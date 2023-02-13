<?php declare(strict_types=1);

namespace Olifanton\Ton\Tests\Toncenter\ToncenterHttpClient;

class RunGetMethodUTest extends ToncenterHttpClientUTestCase
{
    /**
     * @throws \Throwable
     */
    public function testSuccess(): void
    {
        $response = $this->createResponseDataStub("runGetMethod/result");
        $this
            ->httpClientMock
            ->shouldReceive("send")
            ->andReturn($response);

        $instance = $this->getInstance();
        $result = $instance->runGetMethod("kf_NSzfDJI1A3rOM0GQm7xsoUXHTgmdhN5-OrGD8uwL2JMvQ", "seqno");

        $this->assertEquals("num", $result->stack[0][0]);
        $this->assertEquals("0x14c97", $result->stack[0][1]);
    }
}
