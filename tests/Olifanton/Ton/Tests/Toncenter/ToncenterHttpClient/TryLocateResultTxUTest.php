<?php declare(strict_types=1);

namespace Olifanton\Ton\Tests\Toncenter\ToncenterHttpClient;

use Olifanton\Interop\Address;
use Olifanton\Interop\Bytes;

class TryLocateResultTxUTest extends ToncenterHttpClientUTestCase
{
    /**
     * @throws \Throwable
     */
    public function testSuccess(): void
    {
        $this->prepareSendMock("tryLocateResultTx/result");

        $instance = $this->getInstance();

        $result = $instance->tryLocateResultTx(
            new Address("EQD57OL7n9KjwN5vxrW5KOJ-WIQTEw85mSMXmkdcSS_eLzi7"),
            new Address("EQAjZeb707YAERSIoMqA3849YKIAMvri0NAKVD0EQ46Gzti1"),
            "5936687000002",
        );

        $this->assertEquals(1668253134, $result->utime);

        $this->assertStringStartsWith(
            "te6ccmECGAEAAzEAA7VyNl5vvTtgARFIigyoDfzj1gogAy+uLQ0ApUPQRDjobOAAAFZj4eqcPYlVJXAJlX0RZwGhsl7TW1A7",
            Bytes::bytesToBase64($result->data->toBoc(false, true, true)),
        );

        $this->assertEquals(
            "5936687000003",
            $result->transactionId->lt,
        );
        $this->assertEquals(
            "9+X5gYqVUl2mAyByIu1U0ML+00tNoWy/bwqLmby5M/s=",
            $result->transactionId->hash,
        );
        $this->assertEquals("10992004", $result->fee->toBase(10));

        $this->assertEquals("EQD57OL7n9KjwN5vxrW5KOJ-WIQTEw85mSMXmkdcSS_eLzi7", $result->inMsg->source);

        $this->assertCount(1, $result->outMsgs);
        $this->assertEquals("50000000", $result->outMsgs[0]->value->toBase(10));
    }
}
