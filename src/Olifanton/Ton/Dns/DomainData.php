<?php declare(strict_types=1);

namespace Olifanton\Ton\Dns;

use Olifanton\Interop\Address;
use Olifanton\Interop\Boc\BitString;
use Olifanton\Interop\Boc\Cell;
use Olifanton\Interop\Boc\DictSerializers;
use Olifanton\Interop\Boc\Exceptions\CellException;
use Olifanton\Interop\Boc\Exceptions\HashmapException;
use Olifanton\Interop\Boc\Exceptions\SliceException;
use Olifanton\Interop\Boc\Hashmap;
use Olifanton\Interop\Bytes;
use Olifanton\Interop\Crypto;
use Olifanton\Ton\Dns\Exceptions\DomainDataParsingException;

class DomainData
{
    private readonly Hashmap $dict;

    /**
     * @throws DomainDataParsingException
     */
    public function __construct(Cell $dnsNftContent)
    {
        try {
            $this->dict = Hashmap::parse(
                256,
                $dnsNftContent,
                new DictSerializers(
                    keySerializer: static fn(string $k, int $keySize): array => (new BitString($keySize))
                        ->writeBytes(Crypto::sha256(Bytes::stringToBytes($k)))
                        ->toBitsA(),
                )
            );
        } catch (HashmapException $e) {
            throw new DomainDataParsingException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * @throws CellException|SliceException
     */
    public function getWallet(): ?Address
    {
        /** @var Cell|null $cell */
        $cell = $this->dict->get("wallet");

        if ($cell && count($cell->refs)) {
            $slice = $cell->refs[0]->beginParse();

            if ($slice->loadUint(16)->toInt() === 0x9fd3) {
                return $slice->loadAddress();
            }
        }

        return null;
    }
}
