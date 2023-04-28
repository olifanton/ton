<?php declare(strict_types=1);

namespace Olifanton\Ton\IntegrationTests\ToncenterHttpClient;

use Olifanton\Ton\IntegrationTests\Traits\ToncenterHttpClientTrait;
use Olifanton\Ton\Transports\Toncenter\ClientOptions;
use Olifanton\Ton\Transports\Toncenter\ToncenterHttpV2Client;
use PHPUnit\Framework\TestCase;

abstract class ToncenterHttpClientITestCase extends TestCase
{
    use ToncenterHttpClientTrait;

    protected function tearDown(): void
    {
        \Mockery::close();
    }

    protected function getInstance(): ToncenterHttpV2Client
    {
        return $this->createToncenterHttpV2Client($this->getOptions());
    }

    protected function getOptions(): ClientOptions
    {
        return new ClientOptions(
            baseUri: "https://testnet.toncenter.com/api/v2",
            apiKey: $_ENV["TONCENTER_API_KEY"],
        );
    }
}
