<?php declare(strict_types=1);

namespace Olifanton\Ton\Tests\Toncenter\ToncenterHttpClient;

use Olifanton\Interop\Address;
use Olifanton\Interop\Bytes;

class TryLocateSourceTxUTest extends ToncenterHttpClientUTestCase
{
    /**
     * @throws \Throwable
     */
    public function testSuccess(): void
    {
        $this->prepareSendMock("tryLocateSourceTx/result");

        $instance = $this->getInstance();

        $result = $instance->tryLocateSourceTx(
            new Address("EQD57OL7n9KjwN5vxrW5KOJ-WIQTEw85mSMXmkdcSS_eLzi7"),
            new Address("EQAjZeb707YAERSIoMqA3849YKIAMvri0NAKVD0EQ46Gzti1"),
            "5936687000002",
        );

        $this->assertEquals(1668253134, $result->utime);

        $this->assertStringStartsWith(
            "te6ccmECCgEAAkcAA7V",
            Bytes::bytesToBase64($result->data->toBoc(false, true, true)),
        );

        $this->assertEquals(
            "5936687000001",
            $result->transactionId->lt,
        );
        $this->assertEquals(
            "Ru78iQQYSNzRxNy2jP4+xAybukKzFBhjgFMUvnBCxYE=",
            $result->transactionId->hash,
        );
        $this->assertEquals("5510009", $result->fee->toBase(10));

        $this->assertEquals("EQD57OL7n9KjwN5vxrW5KOJ-WIQTEw85mSMXmkdcSS_eLzi7", $result->inMsg->destination);

        $this->assertCount(1, $result->outMsgs);
        $this->assertEquals("1000000000", $result->outMsgs[0]->value->toBase(10));
    }
}
