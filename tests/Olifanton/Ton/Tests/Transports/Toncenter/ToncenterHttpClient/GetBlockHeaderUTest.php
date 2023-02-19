<?php declare(strict_types=1);

namespace Olifanton\Ton\Tests\Transports\Toncenter\ToncenterHttpClient;

class GetBlockHeaderUTest extends ToncenterHttpClientUTestCase
{
    /**
     * @throws \Throwable
     */
    public function testSuccess(): void
    {
        $this->prepareSendMock("getBlockHeader/result");

        $instance = $this->getInstance();
        $result = $instance->getBlockHeader(
            -1,
            "-9223372036854775808",
            24404479
        );

        $this->assertEquals(-1, $result->id->workchain);

        $this->assertEquals(-239, $result->globalId);
        $this->assertEquals(0, $result->version);
        $this->assertEquals(1, $result->flags);
        $this->assertEquals(false, $result->afterMerge);
        $this->assertEquals(false, $result->afterSplit);
        $this->assertEquals(false, $result->beforeSplit);
        $this->assertEquals(true, $result->wantMerge);
        $this->assertEquals(false, $result->wantSplit);
        $this->assertEquals(-1609713869, $result->validatorListHashShort);
        $this->assertEquals(364659, $result->catchainSeqno);
        $this->assertEquals(24404475, $result->minRefMcSeqno);
        $this->assertEquals(false, $result->isKeyBlock);
        $this->assertEquals(24401637, $result->prevKeyBlockSeqno);
        $this->assertEquals("32151778000000", $result->startLt);
        $this->assertEquals("32151778000004", $result->endLt);
        $this->assertEquals(1666195275, $result->genUtime);
        $this->assertEquals(1, $result->vertSeqno);

        $this->assertCount(1, $result->prevBlocks);

        $this->assertEquals(-1, $result->prevBlocks[0]->workchain);
    }
}
