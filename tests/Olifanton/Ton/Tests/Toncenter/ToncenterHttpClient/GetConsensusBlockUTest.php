<?php declare(strict_types=1);

namespace Olifanton\Ton\Tests\Toncenter\ToncenterHttpClient;

class GetConsensusBlockUTest extends ToncenterHttpClientUTestCase
{
    /**
     * @throws \Throwable
     */
    public function testSuccess(): void
    {
        $response = $this->createResponseDataStub("getConsensusBlock/result");
        $this
            ->httpClientMock
            ->shouldReceive("send")
            ->andReturn($response);

        $instance = $this->getInstance();
        $result = $instance->getConsensusBlock();

        $this->assertEquals(24227230, $result->consensusBlock);
        $this->assertEquals(1665589396.873616, $result->timestamp);
    }
}
