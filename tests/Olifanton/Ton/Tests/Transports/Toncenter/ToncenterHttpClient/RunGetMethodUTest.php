<?php declare(strict_types=1);

namespace Olifanton\Ton\Tests\Transports\Toncenter\ToncenterHttpClient;

class RunGetMethodUTest extends ToncenterHttpClientUTestCase
{
    /**
     * @throws \Throwable
     */
    public function testSuccess(): void
    {
        $this->prepareSendMock("runGetMethod/result");

        $instance = $this->getInstance();
        $result = $instance->runGetMethod("kf_NSzfDJI1A3rOM0GQm7xsoUXHTgmdhN5-OrGD8uwL2JMvQ", "seqno");

        $this->assertEquals("0", $result->gasUsed->toBase(10));
        $this->assertEquals("num", $result->stack[0][0]);
        $this->assertEquals("0x14c97", $result->stack[0][1]);
        $this->assertEquals("smc.runResult", $result->type);
        $this->assertEquals(-13, $result->exitCode);
    }
}
