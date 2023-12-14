<?php declare(strict_types=1);

namespace Olifanton\Ton\Dns;

use Olifanton\Interop\Address;
use Olifanton\Interop\Boc\Builder;
use Olifanton\Interop\Boc\Cell;
use Olifanton\Interop\Boc\Exceptions\BitStringException;
use Olifanton\Interop\Boc\Exceptions\CellException;
use Olifanton\Interop\Boc\Exceptions\SliceException;
use Olifanton\Interop\Boc\Helpers\TypedArrayHelper;
use Olifanton\Interop\Bytes;
use Olifanton\Ton\Contracts\Messages\Exceptions\ResponseStackParsingException;
use Olifanton\Ton\Contracts\Messages\ResponseStack;
use Olifanton\Ton\Dns\Exceptions\DnsException;
use Olifanton\Ton\Dns\Exceptions\DnsInitializationException;
use Olifanton\Ton\Dns\Exceptions\DomainDataParsingException;
use Olifanton\Ton\Dns\Helpers\DomainHelper;
use Olifanton\Ton\Exceptions\TransportException;
use Olifanton\Ton\Helpers\AddressHelper;
use Olifanton\Ton\Transport;
use Olifanton\TypedArrays\Uint8Array;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\SimpleCache\CacheException;
use Psr\SimpleCache\CacheInterface;
use function Olifanton\Ton\Marshalling\Tvm\num;

class DnsClient implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    protected CacheInterface|null $cache = null;

    protected int $cacheTtl = 3600;

    protected int $rootResolverCacheTtl = 864000;

    private bool $isInitialized = false;

    private ?Address $rootDnsAddress = null;

    public function __construct(
        private readonly Transport $transport,
    ) {}

    /**
     * @throws DnsException
     */
    public function resolve(string $domain): ?DomainData
    {
        if (empty($domain)) {
            throw new DnsException("Empty domain");
        }

        $rawDomain = DomainHelper::domainToBytes($domain);
        $this->initialize();

        return $this->resolveInner($rawDomain, $this->rootDnsAddress);
    }

    public function setCache(CacheInterface $cache): void
    {
        $this->cache = $cache;
    }

    public function setCacheTtl(int $seconds): void
    {
        $this->cacheTtl = $seconds;
    }

    public function setRootResolverCacheTtl(int $rootResolverCacheTtl): void
    {
        $this->rootResolverCacheTtl = $rootResolverCacheTtl;
    }

    /**
     * @throws DnsException|DomainDataParsingException
     */
    protected final function resolveInner(Uint8Array $rawDomain,
                                          Address    $resolverAddress): ?DomainData
    {
        try {
            $domainCell = (new Builder())->writeBytes($rawDomain)->cell();
        } catch (BitStringException $e) {
            throw new DnsException("Domain cell serialization error: " . $e->getMessage(), 0, $e);
        }

        try {
            $domainCellBoc = Bytes::bytesToBase64($domainCell->toBoc(false));
        } catch (CellException $e) {
            throw new DnsException($e->getMessage(), $e->getCode(), $e);
        }

        $responseStack = $this->getCachedSmcGetterResponse($domainCellBoc);

        if (!$responseStack) {
            try {
                $responseStack = $this
                    ->transport
                    ->runGetMethod(
                        $resolverAddress,
                        "dnsresolve",
                        [
                            [
                                "tvm.Slice",
                                $domainCellBoc,
                            ],
                            num(0),
                        ]
                    );
                $this->cacheSmcGetterResponse($domainCellBoc, $responseStack);
            // @codeCoverageIgnoreStart
            } catch (TransportException $e) {
                throw new DnsException($e->getMessage(), $e->getCode(), $e);
            }
            // @codeCoverageIgnoreEnd
        }

        if ($responseStack->count() !== 2) {
            throw new DnsException("Invalid `dnsresolve` response");
        }

        $resultLength = $responseStack->currentBigInteger()->toInt();
        $domainLength = $rawDomain->length * 8;
        $responseStack->next();
        $cell = $responseStack->currentCell();

        if ($resultLength === 0) {
            return null;
        }

        if ($resultLength % 8 !== 0) {
            throw new DnsException("Domain split not at a component boundary");
        }

        if ($resultLength > $domainLength) {
            throw new DnsException("Invalid response, length not matched");
        }

        if ($cell) {
            if ($resultLength === $domainLength) {
                $this
                    ->logger
                    ?->debug("DNS query completed");

                return new DomainData($cell);
            }

            $nextResolver = $this->parseNextResolverAddress($cell);
            $this
                ->logger
                ?->debug("Next resolver address: " . $nextResolver->toString(true, true, true));

            return $this->resolveInner(
                TypedArrayHelper::sliceUint8Array($rawDomain, $resultLength / 8),
                $nextResolver,
            );
        }

        return null;
    }

    /** @noinspection PhpRedundantCatchClauseInspection */
    protected final function getCachedSmcGetterResponse(string $domainCellBoc): ?ResponseStack
    {
        try {
            if ($cached = $this->readCachedValue("resolved_" . $domainCellBoc)) {
                return unserialize($cached);
            }
        // @codeCoverageIgnoreStart
        } catch (\JsonException|ResponseStackParsingException $e) {
            $this
                ->logger
                ?->warning(
                    "Cached stack parsing error: " . $e->getMessage(),
                    [
                        "exception" => $e,
                    ],
                );
        }
        // @codeCoverageIgnoreEnd

        return null;
    }

    /** @noinspection PhpRedundantCatchClauseInspection */
    protected final function cacheSmcGetterResponse(string $domainCellBoc, ResponseStack $responseStack): void
    {
        try {
            $this->cacheValue(
                "resolved_" . $domainCellBoc,
                serialize($responseStack),
                $this->cacheTtl,
            );
        // @codeCoverageIgnoreStart
        } catch (\JsonException $e) {
            $this
                ->logger
                ?->warning(
                    "Stack serialization error: " . $e->getMessage(),
                    [
                        "exception" => $e,
                    ],
                );
        }
        // @codeCoverageIgnoreEnd
    }

    protected function cacheValue(string $key, string $value, int $ttl): void
    {
        try {
            $this
                ->cache
                ?->set(
                    "olfnt_ton_dns_" . $key,
                    $value,
                    $ttl,
                );
        // @codeCoverageIgnoreStart
        } catch (CacheException $e) {
            $this
                ->logger
                ?->warning(
                    "Cache writing error: " . $e->getMessage(),
                    [
                        "exception" => $e,
                    ]
                );
        }
        // @codeCoverageIgnoreEnd
    }

    protected function readCachedValue(string $key): ?string
    {
        try {
            return $this->cache?->get("olfnt_ton_dns_" . $key);
        // @codeCoverageIgnoreStart
        } catch (CacheException $e) {
            $this
                ->logger
                ?->warning(
                    "Cache reading error: " . $e->getMessage(),
                    [
                        "exception" => $e,
                    ]
                );
        }
        // @codeCoverageIgnoreEnd

        return null;
    }

    /**
     * @throws DnsException
     */
    private function parseNextResolverAddress(Cell $cell): Address
    {
        return $this->parseSmcAddress($cell, 0xba, 0x93);
    }

    /**
     * @throws DnsException
     */
    private function parseSmcAddress(Cell $cell, int $prefix0, int $prefix1): Address
    {
        try {
            $slice = $cell->beginParse();

            if ($slice->loadUint(8)->toInt() !== $prefix0 || $slice->loadUint(8)->toInt() !== $prefix1) {
                throw new DnsException("Invalid DNS record value prefix");
            }

            return AddressHelper::parseAddressSlice($slice);
        // @codeCoverageIgnoreStart
        } catch (SliceException|CellException $e) {
            throw new DnsException($e->getMessage(), $e->getCode(), $e);
        }
        // @codeCoverageIgnoreEnd
    }

    /**
     * @throws DnsInitializationException
     */
    private function initialize(): void
    {
        if ($this->isInitialized) {
            return;
        }

        if ($root = $this->readCachedValue("root")) {
            $this->isInitialized = true;
            $this->rootDnsAddress = new Address($root);
            return;
        }

        $this->logger?->debug("Start root DNS resolver receiving...");
        $this->isInitialized = true;

        try {
            $configCell = $this->transport->getConfigParam(4);
            $this->logger?->debug("Config cell with paramId 4 fetched");
        } catch (TransportException $e) {
            throw new DnsInitializationException($e->getMessage(), $e->getCode(), $e);
        }

        try {
            $this->rootDnsAddress = new Address(
                "-1:" . Bytes::bytesToHexString($configCell->beginParse()->loadBits(256))
            );
            $this
                ->logger
                ?->debug(sprintf(
                    "Root DNS resolver address received: %s",
                    $this->rootDnsAddress->toString(true, true, false),
                ));

            $this->cacheValue("root", $this->rootDnsAddress->toString(), $this->rootResolverCacheTtl);
        // @codeCoverageIgnoreStart
        } catch (CellException|SliceException $e) {
            throw new DnsInitializationException($e->getMessage(), $e->getCode(), $e);
        }
        // @codeCoverageIgnoreEnd
    }
}
