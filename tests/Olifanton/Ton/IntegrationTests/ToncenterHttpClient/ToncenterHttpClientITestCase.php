<?php declare(strict_types=1);

namespace Olifanton\Ton\IntegrationTests\ToncenterHttpClient;

use Http\Client\Common\HttpMethodsClient;
use Http\Discovery\HttpClientDiscovery;
use Http\Discovery\Psr17FactoryDiscovery;
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
            new HttpMethodsClient(
                HttpClientDiscovery::find(),
                Psr17FactoryDiscovery::findRequestFactory(),
                Psr17FactoryDiscovery::findStreamFactory(),
            ),
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
