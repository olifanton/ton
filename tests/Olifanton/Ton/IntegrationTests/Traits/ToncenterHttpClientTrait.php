<?php declare(strict_types=1);

namespace Olifanton\Ton\IntegrationTests\Traits;

use Http\Client\Common\HttpMethodsClient;
use Http\Discovery\HttpClientDiscovery;
use Http\Discovery\Psr17FactoryDiscovery;
use Olifanton\Ton\Transports\Toncenter\ClientOptions;
use Olifanton\Ton\Transports\Toncenter\ToncenterHttpV2Client;

trait ToncenterHttpClientTrait
{
    protected function createToncenterHttpV2Client(?ClientOptions $options = null): ToncenterHttpV2Client
    {
        return new ToncenterHttpV2Client(
            new HttpMethodsClient(
                HttpClientDiscovery::find(),
                Psr17FactoryDiscovery::findRequestFactory(),
                Psr17FactoryDiscovery::findStreamFactory(),
            ),
            $options ?? new ClientOptions(
                baseUri: "https://testnet.toncenter.com/api/v2",
                apiKey: $_ENV["TONCENTER_API_KEY"],
                requestDelay: 1.0,
            ),
        );
    }
}
