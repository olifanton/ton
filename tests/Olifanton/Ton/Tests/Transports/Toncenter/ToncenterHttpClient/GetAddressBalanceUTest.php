<?php declare(strict_types=1);

namespace Olifanton\Ton\Tests\Transports\Toncenter\ToncenterHttpClient;

class GetAddressBalanceUTest extends ToncenterHttpClientUTestCase
{
    /**
     * @throws \Throwable
     */
    public function testActive(): void
    {
        $this->prepareSendMock("getAddressBalance/active");

        $instance = $this->getInstance();
        $result = $instance->getAddressBalance($this->createAddressStub());

        $this->assertEquals("222852837968943", $result->toBase(10));
    }

    /**
     * @throws \Throwable
     */
    public function testUninitialized(): void
    {
        $this->prepareSendMock("getAddressBalance/uninitialized");

        $instance = $this->getInstance();
        $result = $instance->getAddressBalance($this->createAddressStub());

        $this->assertEquals("0", $result->toBase(10));
    }
}
