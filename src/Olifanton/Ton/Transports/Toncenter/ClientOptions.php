<?php declare(strict_types=1);

namespace Olifanton\Ton\Transports\Toncenter;

class ClientOptions
{
    /**
     * @param string $baseUri Base Toncenter instance URI
     * @param string|null $apiKey Toncenter API Key
     * @param float|null $requestDelay Request delay in seconds. DO NOT USE IN PRODUCTION ENVIRONMENT!
     */
    public function __construct(
        public readonly string $baseUri = "https://toncenter.com/api/v2",
        public readonly ?string $apiKey = null,
        public readonly ?float $requestDelay = 0.0,
    ) {}
}
