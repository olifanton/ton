<?php declare(strict_types=1);

namespace Olifanton\Ton\IntegrationTests\ToncenterHttpClient;

use Olifanton\Interop\Address;
use Olifanton\Ton\AddressState;

class GetWalletInformationITest extends ToncenterHttpClientITestCase
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
