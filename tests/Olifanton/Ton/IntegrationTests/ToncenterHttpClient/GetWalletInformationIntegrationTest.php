<?php declare(strict_types=1);

namespace Olifanton\Ton\IntegrationTests\ToncenterHttpClient;

use Olifanton\Ton\Models\AddressState;
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
        $this->assertEquals(AddressState::ACTIVE, $resp->accountState);
        $this->assertNotNull($resp->lastTransactionId);
        $this->assertFalse($resp->wallet);
    }
}
