<?php declare(strict_types=1);

namespace Olifanton\Ton\IntegrationTests\ToncenterHttpClient;

use Olifanton\Interop\Address;
use Olifanton\Ton\AddressState;

class GetAddressStateITest extends ToncenterHttpClientITestCase
{
    /**
     * @throws \Throwable
     */
    public function testSuccess(): void
    {
        $client = $this->getInstance();
        $resp = $client->getAddressState(new Address("Ef8AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAADAU"));
        $this->assertEquals(AddressState::ACTIVE, $resp);
    }
}
