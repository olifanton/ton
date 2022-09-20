<?php declare(strict_types=1);

namespace Olifanton\Ton\IntegrationTests\ToncenterHttpClient;

use Olifanton\Utils\Address;

class GetAddressInformationIntegrationTest extends ToncenterHttpClientIntegrationTestCase
{
    /**
     * @throws \Olifanton\Ton\Toncenter\Exceptions\ClientException
     * @throws \Olifanton\Ton\Toncenter\Exceptions\TimeoutException
     * @throws \Olifanton\Ton\Toncenter\Exceptions\ValidationException
     */
    public function testSuccess(): void
    {
        $client = $this->getInstance();
        $resp = $client->getAddressInformation(new Address("Ef8AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAADAU"));
        $this->assertEquals("active", $resp->state);
    }
}
