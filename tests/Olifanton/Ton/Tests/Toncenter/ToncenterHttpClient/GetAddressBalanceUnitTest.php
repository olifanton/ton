<?php declare(strict_types=1);

namespace Olifanton\Ton\Tests\Toncenter\ToncenterHttpClient;

class GetAddressBalanceUnitTest extends ToncenterHttpClientUnitTestCase
{
    /**
     * @throws \Throwable
     */
    public function testActive(): void
    {
        $response = $this->createResponseDataStub("getAddressBalance/active");
        $this
            ->httpClientMock
            ->shouldReceive("send")
            ->andReturn($response);

        $instance = $this->getInstance();
        $result = $instance->getAddressBalance($this->createAddressStub());

        $this->assertEquals("222852837968943", $result->toBase(10));
    }

    /**
     * @throws \Throwable
     */
    public function testUninitialized(): void
    {
        $response = $this->createResponseDataStub("getAddressBalance/uninitialized");
        $this
            ->httpClientMock
            ->shouldReceive("send")
            ->andReturn($response);

        $instance = $this->getInstance();
        $result = $instance->getAddressBalance($this->createAddressStub());

        $this->assertEquals("0", $result->toBase(10));
    }
}
