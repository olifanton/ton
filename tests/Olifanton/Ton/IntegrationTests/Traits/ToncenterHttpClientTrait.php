<?php declare(strict_types=1);

namespace Olifanton\Ton\IntegrationTests\Traits;

use Http\Client\Common\HttpMethodsClient;
use Http\Discovery\Psr17FactoryDiscovery;
use Http\Discovery\Psr18ClientDiscovery;
use Olifanton\Ton\Transports\Toncenter\ClientOptions;
use Olifanton\Ton\Transports\Toncenter\ToncenterHttpV2Client;

trait ToncenterHttpClientTrait
{
    protected function createToncenterHttpV2Client(?ClientOptions $options = null): ToncenterHttpV2Client
    {
        return new ToncenterHttpV2Client(
            new HttpMethodsClient(
                Psr18ClientDiscovery::find(),
                Psr17FactoryDiscovery::findRequestFactory(),
                Psr17FactoryDiscovery::findStreamFactory(),
            ),
            $options ?? new ClientOptions(
                baseUri: ClientOptions::TEST_BASE_URL,
                apiKey: $_ENV["TONCENTER_API_KEY"],
                requestDelay: 1.0,
            ),
        );
    }
}
