<?php declare(strict_types=1);

namespace Olifanton\Ton\IntegrationTests\ToncenterHttpClient;

use Olifanton\Utils\Address;

class GetWalletInformationIntegrationTest extends ToncenterHttpClientIntegrationTestCase
{
    /**
     * @throws \Throwable
     */
    public function testSuccess(): void
    {
        $client = $this->getInstance();
        $resp = $client->getWalletInformation(new Address("Ef8AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAADAU"));
        $this->assertEquals("active", $resp->accountState);
        $this->assertNotNull($resp->lastTransactionId);
        $this->assertFalse($resp->wallet);
    }
}
