<?php declare(strict_types=1);

namespace Olifanton\Ton\Tests\Toncenter\ToncenterHttpClient;

use Olifanton\Ton\Models\AddressState;

class GetAddressStateUTest extends ToncenterHttpClientUTestCase
{
    /**
     * @throws \Throwable
     */
    public function testActive(): void
    {
        $response = $this->createResponseDataStub("getAddressState/active");
        $this
            ->httpClientMock
            ->shouldReceive("send")
            ->andReturn($response);

        $instance = $this->getInstance();
        $result = $instance->getAddressState($this->createAddressStub());

        $this->assertEquals(AddressState::ACTIVE, $result);
    }

    /**
     * @throws \Throwable
     */
    public function testUninitialized(): void
    {
        $response = $this->createResponseDataStub("getAddressState/uninitialized");
        $this
            ->httpClientMock
            ->shouldReceive("send")
            ->andReturn($response);

        $instance = $this->getInstance();
        $result = $instance->getAddressState($this->createAddressStub());

        $this->assertEquals(AddressState::UNINITIALIZED, $result);
    }

    /**
     * @throws \Throwable
     */
    public function testUnknown(): void
    {
        $response = $this->createResponseDataStub("getAddressState/unknown");
        $this
            ->httpClientMock
            ->shouldReceive("send")
            ->andReturn($response);

        $instance = $this->getInstance();
        $result = $instance->getAddressState($this->createAddressStub());

        $this->assertEquals(AddressState::UNKNOWN, $result);
    }
}
