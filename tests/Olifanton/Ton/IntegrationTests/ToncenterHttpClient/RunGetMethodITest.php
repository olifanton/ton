<?php declare(strict_types=1);

namespace Olifanton\Ton\IntegrationTests\ToncenterHttpClient;

use Olifanton\Interop\Address;

class RunGetMethodITest extends ToncenterHttpClientITestCase
{
    /**
     * @throws \Throwable
     */
    public function testGetterSeqno(): void
    {
        $addr = new Address("kf_NSzfDJI1A3rOM0GQm7xsoUXHTgmdhN5-OrGD8uwL2JMvQ");
        $result = $this
            ->getInstance()
            ->runGetMethod(
                $addr,
                "seqno",
            );

        $this->assertEquals("num", $result->stack[0][0]);
        $this->assertEquals("0x14c97", $result->stack[0][1]);
    }
}
