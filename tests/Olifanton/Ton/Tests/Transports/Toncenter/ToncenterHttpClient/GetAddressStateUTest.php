<?php declare(strict_types=1);

namespace Olifanton\Ton\Tests\Transports\Toncenter\ToncenterHttpClient;

use Olifanton\Ton\AddressState;

class GetAddressStateUTest extends ToncenterHttpClientUTestCase
{
    /**
     * @throws \Throwable
     */
    public function testActive(): void
    {
        $this->prepareSendMock("getAddressState/active");

        $instance = $this->getInstance();
        $result = $instance->getAddressState($this->createAddressStub());

        $this->assertEquals(AddressState::ACTIVE, $result);
    }

    /**
     * @throws \Throwable
     */
    public function testUninitialized(): void
    {
        $this->prepareSendMock("getAddressState/uninitialized");

        $instance = $this->getInstance();
        $result = $instance->getAddressState($this->createAddressStub());

        $this->assertEquals(AddressState::UNINITIALIZED, $result);
    }

    /**
     * @throws \Throwable
     */
    public function testUnknown(): void
    {
        $this->prepareSendMock("getAddressState/unknown");

        $instance = $this->getInstance();
        $result = $instance->getAddressState($this->createAddressStub());

        $this->assertEquals(AddressState::UNKNOWN, $result);
    }
}
