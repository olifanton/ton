<?php declare(strict_types=1);

namespace Olifanton\Ton\IntegrationTests\ToncenterHttpClient;

class GetBlockTransactionsITest extends ToncenterHttpClientITestCase
{
    /**
     * @throws \Throwable
     */
    public function testSuccess(): void
    {
        $client = $this->getInstance();
        $resp = $client->getBlockTransactions(
            -1,
            "-9223372036854775808",
            4222449,
        );

        $this->assertEquals(-1, $resp->id->workchain);
        $this->assertEquals(4222449, $resp->id->seqno);

        $this->assertCount(3, $resp->transactions);
    }
}
