<?php declare(strict_types=1);

namespace Olifanton\Ton\Tests\Toncenter\ToncenterHttpClient;

class GetConfigParamUTest extends ToncenterHttpClientUTestCase
{
    /**
     * @throws \Throwable
     */
    public function testSuccess(): void
    {
        $response = $this->createResponseDataStub("getConfigParam/result");
        $this
            ->httpClientMock
            ->shouldReceive("send")
            ->andReturn($response);

        $instance = $this->getInstance();
        $result = $instance->getConfigParam(1);

        $this->assertEquals("tvm.cell", $result->type);
        $this->assertEquals(
            "te6cckEBAQEAIgAAQO/nHROGCvqmrq6vY2+RaEh/gPEDGwv42TmuSdPqf32glRg2PQ==",
            $result->bytes,
        );
    }
}
