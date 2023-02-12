<?php declare(strict_types=1);

namespace Olifanton\Ton\IntegrationTests\ToncenterHttpClient;

use Olifanton\Interop\Address;

class TryLocateResultTxITest extends ToncenterHttpClientITestCase
{
    /**
     * @throws \Throwable
     */
    public function testSuccess(): void
    {
        $client = $this->getInstance();
        $resp = $client->tryLocateResultTx(
            new Address("EQD57OL7n9KjwN5vxrW5KOJ-WIQTEw85mSMXmkdcSS_eLzi7"),
            new Address("EQAjZeb707YAERSIoMqA3849YKIAMvri0NAKVD0EQ46Gzti1"),
            "5936687000002",
        );
        $this->assertEquals("9+X5gYqVUl2mAyByIu1U0ML+00tNoWy/bwqLmby5M/s=", $resp->transactionId->hash);
    }
}
