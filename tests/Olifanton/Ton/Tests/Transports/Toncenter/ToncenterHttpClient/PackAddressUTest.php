<?php declare(strict_types=1);

namespace Olifanton\Ton\Tests\Transports\Toncenter\ToncenterHttpClient;

class PackAddressUTest extends ToncenterHttpClientUTestCase
{
    /**
     * @throws \Throwable
     */
    public function testPack(): void
    {
        $this->prepareSendMock("packAddress/valid");

        $instance = $this->getInstance();
        $result = $instance->packAddress("0:83DFD552E63729B472FCBCC8C45EBCC6691702558B68EC7527E1BA403A0F31A8");
        $this->assertEquals(
            "EQCD39VS5jcptHL8vMjEXrzGaRcCVYto7HUn4bpAOg8xqB2N",
            $result,
        );
    }
}
