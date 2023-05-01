<?php declare(strict_types=1);

namespace Olifanton\Ton;

use Http\Client\Common\HttpMethodsClientInterface;
use Olifanton\Ton\Contracts\Jetton\JettonData;
use Olifanton\Ton\Contracts\Jetton\JettonMetadata;
use Olifanton\Ton\Contracts\Jetton\JettonMinter;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

class JettonOffchainMetadataFetcher implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(
        private readonly Transport $transport,
        private readonly HttpMethodsClientInterface $httpClient,
    ) {}

    /**
     * @throws Contracts\Exceptions\ContractException
     * @throws Exceptions\TransportException
     * @throws \Http\Client\Exception
     * @throws \JsonException
     */
    public function getMetadata(JettonMinter $smc): JettonMetadata
    {
        return $this->getMetadataFromData($smc->getJettonData($this->transport));
    }

    /**
     * @throws \Http\Client\Exception
     * @throws \JsonException
     */
    public function getMetadataFromData(JettonData $data): JettonMetadata
    {
        $url = $data->jettonContentUrl;

        if (!$url) {
            throw new \InvalidArgumentException("Jetton content url is required");
        }

        return $this->getMetadataFromUri($url);
    }

    /**
     * @throws \Http\Client\Exception
     * @throws \JsonException
     */
    private function getMetadataFromUri(string $uri): JettonMetadata
    {
        $schema = strtolower(parse_url($uri, PHP_URL_SCHEME));

        $this->logger?->debug("Start fetching jetton metadata from url: " . $uri);

        if (!in_array($schema, ["http", "https"])) {
            throw new \RuntimeException(sprintf("%s protocol currently not supported", $schema));
        }

        $response = $this->httpClient->get($uri);

        if ($response->getStatusCode() > 299) {
            throw new \RuntimeException("Bad status: " . $response->getStatusCode());
        }

        $content = $response->getBody()->getContents();
        $this
            ->logger
            ?->debug("Metadata fetched, content type: " . $response->getHeaderLine("Content-Type"));

        return JettonMetadata::fromJson($content);
    }
}
