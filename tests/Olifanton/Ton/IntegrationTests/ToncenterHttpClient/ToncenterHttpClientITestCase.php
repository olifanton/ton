<?php declare(strict_types=1);

namespace Olifanton\Ton\IntegrationTests\ToncenterHttpClient;

use GuzzleHttp\Client;
use Olifanton\Ton\ClientOptions;
use Olifanton\Ton\Toncenter\ToncenterHttpV2Client;
use PHPUnit\Framework\TestCase;

abstract class ToncenterHttpClientITestCase extends TestCase
{
    protected function tearDown(): void
    {
        \Mockery::close();
    }

    protected function getInstance(): ToncenterHttpV2Client
    {
        return new ToncenterHttpV2Client(
            new Client(),
            $this->getOptions(),
        );
    }

    protected function getOptions(): ClientOptions
    {
        return new ClientOptions(
            baseUri: "https://testnet.toncenter.com/api/v2",
            apiKey: $_ENV["TONCENTER_API_KEY"],
        );
    }
}
