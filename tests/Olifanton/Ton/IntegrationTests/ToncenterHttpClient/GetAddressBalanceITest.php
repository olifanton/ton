<?php declare(strict_types=1);

namespace Olifanton\Ton\IntegrationTests\ToncenterHttpClient;

use Brick\Math\BigInteger;
use Olifanton\Interop\Address;

class GetAddressBalanceITest extends ToncenterHttpClientITestCase
{
    /**
     * @throws \Throwable
     */
    public function testSuccess(): void
    {
        $client = $this->getInstance();
        $resp = $client->getAddressBalance(new Address("Ef8AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAADAU"));
        $this->assertInstanceOf(BigInteger::class, $resp);
    }
}
