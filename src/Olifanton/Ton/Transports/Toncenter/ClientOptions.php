<?php declare(strict_types=1);

namespace Olifanton\Ton\Transports\Toncenter;

class ClientOptions
{
    public function __construct(
        public readonly string $baseUri = "https://toncenter.com/api/v2",
        public readonly ?string $apiKey = null,
    ) {}
}
