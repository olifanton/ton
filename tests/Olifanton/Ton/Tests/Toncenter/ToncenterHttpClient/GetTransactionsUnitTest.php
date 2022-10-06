<?php declare(strict_types=1);

namespace Olifanton\Ton\Tests\Toncenter\ToncenterHttpClient;

class GetTransactionsUnitTest extends ToncenterHttpClientUnitTestCase
{
    /**
     * @throws \Throwable
     */
    public function testActive0(): void
    {
        $response = $this->createResponseDataStub("getTransactions/active0");
        $this
            ->httpClientMock
            ->shouldReceive("send")
            ->andReturn($response);

        $instance = $this->getInstance();
        $result = $instance->getTransactions($this->createAddressStub());

        $this->assertCount(10, $result->items);
    }

    /**
     * @throws \Throwable
     */
    public function testActive1(): void
    {
        $response = $this->createResponseDataStub("getTransactions/active1");
        $this
            ->httpClientMock
            ->shouldReceive("send")
            ->andReturn($response);

        $instance = $this->getInstance();
        $result = $instance->getTransactions($this->createAddressStub());

        $this->assertCount(10, $result->items);
    }

    /**
     * @throws \Throwable
     */
    public function testActive2(): void
    {
        $response = $this->createResponseDataStub("getTransactions/active2");
        $this
            ->httpClientMock
            ->shouldReceive("send")
            ->andReturn($response);

        $instance = $this->getInstance();
        $result = $instance->getTransactions($this->createAddressStub());

        $this->assertCount(16, $result->items);
    }
}
