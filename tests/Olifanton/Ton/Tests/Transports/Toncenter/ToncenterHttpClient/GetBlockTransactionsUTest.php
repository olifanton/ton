<?php declare(strict_types=1);

namespace Olifanton\Ton\Tests\Transports\Toncenter\ToncenterHttpClient;

class GetBlockTransactionsUTest extends ToncenterHttpClientUTestCase
{
    /**
     * @throws \Throwable
     */
    public function testSuccess(): void
    {
        $this->prepareSendMock("getBlockTransactions/result");

        $instance = $this->getInstance();
        $result = $instance->getBlockTransactions(
            -1,
            "-9223372036854775808",
            24226822,
        );

        $this->assertEquals(-1, $result->id->workchain);
        $this->assertEquals("-9223372036854775808", $result->id->shard);
        $this->assertEquals(24226822, $result->id->seqno);
        $this->assertEquals("+t92toJ65fYekK43K3JEqxOzNPCcxPY5nko4YRT8Ycc=", $result->id->rootHash);
        $this->assertEquals("sDvaN/GEV2Pu71UgpD8/egAN5jdeh7/3KnvjzuU8D4g=", $result->id->fileHash);

        $this->assertEquals(40, $result->reqCount);
        $this->assertFalse($result->incomplete);
        $this->assertCount(5, $result->transactions);

        $this->assertEquals(135, $result->transactions[0]->mode);
        $this->assertEquals("-1:3333333333333333333333333333333333333333333333333333333333333333", $result->transactions[0]->account);
        $this->assertEquals("31959863000001", $result->transactions[0]->lt);
        $this->assertEquals("SGWRRXo06yA7thyKYEgcac2FUZivWDFsxW4123aihOM=", $result->transactions[0]->hash);
    }
}
