<?php

declare(strict_types=1);

namespace Olifanton\Ton\Tests\Transports\Toncenter\ToncenterHttpClient;

use Olifanton\Interop\Address;

class TryLocateTxUTest extends ToncenterHttpClientUTestCase
{
    /**
     * @throws \Throwable
     */
    public function testSuccess(): void
    {
        $this->prepareSendMock("tryLocateTx/result");

        $instance = $this->getInstance();
        $result = $instance->tryLocateTx(
            new Address("EQBecELVphHE_kqAk646KA4X5m_LnEZnOlucXhlI79kXBmJ8"),
            new Address("EQAHFygPL9OViFBG-4ZdM7YZw9UWWXOZuOqoqiOvH6cJTW1q"),
            "32338513000002",
        );

        $this->assertEquals(1666790184, $result->utime);
        $this->assertEquals(
            "x{70717280F2FD395885046FB865D33B619C3D516597399B8EAA8AA23AF1FA7094D00001D69659476431BF6C786493E96CCE36A2638B3C7612C3FEE5BB8E313815A5AAD792C34E6877200001D696350A0C163593328000146030D4A}
 x{A_}
  x{4800BCE085AB4C2389FC9501275C74501C2FCCDF97388CCE74B738BC3291DFB22E0D0001C5CA03CBF4E5621411BEE1974CED8670F545965CE66E3AAA2A88EBC7E9C25354C748609ED0061CE3E400003AD2CB28EC84C6B26650C_}
   x{0000000036663934663437662D656232652D346165352D396339662D643038353738356534653665}
 x{7207E49E71F2FE6B55CCA6A2BA883B72C99A63D7FACBA85E3118A9CECD923163C493557611FFB9A4B38A3050B1A57E31D2D1AD832F2908D646E8302A2A0B70A527}
 x{0C41494C748609ED186030D411_}
  x{27CC3D090000000000000000000300000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000}
  x{C00000000000000000000000012D452DA449E50B8CF7DD27861F146122AFE1B546BB8B70FC8216F0C614139F8E04_}\n",
            $result->data->print(),
        );
        $this->assertEquals("32338513000003", $result->transactionId->lt);
        $this->assertEquals("tSfqJLi6h5FDgdmht+NwhdDX81JcdFAtWlft0E9XjjE=", $result->transactionId->hash);
        $this->assertEquals("100005", $result->fee->toBase(10));
        $this->assertEquals("5", $result->storageFee->toBase(10));
        $this->assertEquals("100000", $result->otherFee->toBase(10));
        $this->assertEquals(
            "EQBecELVphHE_kqAk646KA4X5m_LnEZnOlucXhlI79kXBmJ8",
            $result->inMsg->source,
        );
        $this->assertEquals(
            "EQAHFygPL9OViFBG-4ZdM7YZw9UWWXOZuOqoqiOvH6cJTW1q",
            $result->inMsg->destination,
        );
        $this->assertEquals(
            "213978195892",
            $result->inMsg->value->toBase(10),
        );
        $this->assertEquals(
            "946674",
            $result->inMsg->fwdFee->toBase(10),
        );
        $this->assertEquals(
            "0",
            $result->inMsg->ihrFee->toBase(10),
        );
        $this->assertEquals(
            "32338513000002",
            $result->inMsg->createdLt,
        );
        $this->assertEquals(
            "z2vbGg+miU05xCo7Pl0cMHQhqyDNfm2UP4PWKPfWVjE=",
            $result->inMsg->bodyHash,
        );
        $this->assertEquals(
            "msg.dataText",
            $result->inMsg->msgData->type,
        );
        $this->assertEquals(
            "NmY5NGY0N2YtZWIyZS00YWU1LTljOWYtZDA4NTc4NWU0ZTZl",
            $result->inMsg->msgData->text,
        );
        $this->assertEquals(
            "6f94f47f-eb2e-4ae5-9c9f-d085785e4e6e",
            $result->inMsg->message,
        );
        $this->assertEquals([], $result->outMsgs);
    }
}
