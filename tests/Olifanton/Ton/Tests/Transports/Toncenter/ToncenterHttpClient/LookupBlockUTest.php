<?php declare(strict_types=1);

namespace Olifanton\Ton\Tests\Transports\Toncenter\ToncenterHttpClient;

class LookupBlockUTest extends ToncenterHttpClientUTestCase
{
    /**
     * @throws \Throwable
     */
    public function testInvalidArguments(): void
    {
        $instance = $this->getInstance();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Seqno, LT or unixtime should be defined");
        $instance->lookupBlock(
            -1,
            "-9223372036854775808",
        );
    }

    /**
     * @throws \Throwable
     */
    public function testSuccess(): void
    {
        $this->prepareSendMock("lookupBlock/result");

        $instance = $this->getInstance();
        $result = $instance->lookupBlock(-1, "-9223372036854775808", 24226820);

        $this->assertEquals(
            -1,
            $result->workchain,
        );
        $this->assertEquals(
            "-9223372036854775808",
            $result->shard,
        );
        $this->assertEquals(
            24226820,
            $result->seqno,
        );
        $this->assertEquals(
            "HVrtit5Ocq7bbz0VSOsNpcjrOzGzvAkfNON5GIiLh58=",
            $result->rootHash,
        );
        $this->assertEquals(
            "XUlrMHGvJfjg7a4vPSAPEtzykCWXCOhVMAUEbnkdXTE=",
            $result->fileHash,
        );
    }
}
