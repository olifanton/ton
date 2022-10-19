<?php declare(strict_types=1);

namespace Olifanton\Ton\Tests\Toncenter\ToncenterHttpClient;

use Olifanton\Ton\Toncenter\Exceptions\ClientException;
use Olifanton\Utils\Bytes;

class GetTransactionsUTest extends ToncenterHttpClientUTestCase
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

        $transaction0 = $result->items[0];
        $this->assertEquals(1657894320, $transaction0->utime);
        $this->assertTrue(
            str_starts_with(
                Bytes::bytesToBase64($transaction0->data->toBoc(false)),
                "te6cckECBwEAAZUAA7V/HGl4szRHYcfeea0OhCxEv3n15hvilFbW00vbntTfQoAAABr4sA88PUnse6EybkEU9RW",
            )
        );
        $this->assertEquals(
            "1853463000003",
            $transaction0->transactionId->lt,
        );
        $this->assertEquals(
            "yLMjCZ5lK1a9Elo6iXHg/zo4mF9QS9wuK8X61X379dA=",
            $transaction0->transactionId->hash,
        );
        $this->assertEquals("103088", $transaction0->fee->toBase(10));
        $this->assertEquals("3088", $transaction0->storageFee->toBase(10));
        $this->assertEquals("100000", $transaction0->otherFee->toBase(10));
        $this->assertEquals(
            "raw.message",
            $transaction0->inMsg->type,
        );
        $this->assertEquals(
            "EQAIgtPPCYPv_AcQlR8bqsaMgVmATMqvb3GQagdsmoB3026l",
            $transaction0->inMsg->source,
        );
        $this->assertEquals(
            "EQDxxpeLM0R2HH3nmtDoQsRL959eYb4pRW1tNL257U30KBOX",
            $transaction0->inMsg->destination,
        );
        $this->assertEquals(
            "10000000000",
            $transaction0->inMsg->value->toBase(10),
        );
        $this->assertEquals(
            "666672",
            $transaction0->inMsg->fwdFee->toBase(10),
        );
        $this->assertEquals(
            "0",
            $transaction0->inMsg->ihrFee->toBase(10),
        );
        $this->assertEquals(
            "1853463000002",
            $transaction0->inMsg->createdLt,
        );
        $this->assertEquals(
            "lqKW0iTyhcZ77pPDD4owkVfw2qNdxbh+QQt4YwoJz8c=",
            $transaction0->inMsg->bodyHash,
        );
        $this->assertEquals(
            "msg.dataRaw",
            $transaction0->inMsg->msgData->type,
        );
        $this->assertEquals(
            "te6cckEBAQEAAgAAAEysuc0=",
            $transaction0->inMsg->msgData->body,
        );
        $this->assertEquals(
            "",
            $transaction0->inMsg->msgData->initState,
        );
        $this->assertEquals(
            "",
            $transaction0->inMsg->message,
        );
        $this->assertCount(0, $transaction0->outMsgs);
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

        $transaction7 = $result->items[7];
        $this->assertCount(1, $transaction7->outMsgs);
        $outMsg0 = $transaction7->outMsgs[0];
        $this->assertEquals(
            "raw.message",
            $outMsg0->type,
        );
        $this->assertEquals(
            "EQAIgtPPCYPv_AcQlR8bqsaMgVmATMqvb3GQagdsmoB3026l",
            $outMsg0->source,
        );
        $this->assertEquals(
            "EQDxxpeLM0R2HH3nmtDoQsRL959eYb4pRW1tNL257U30KBOX",
            $outMsg0->destination,
        );
        $this->assertEquals(
            "7900000000",
            $outMsg0->value->toBase(10),
        );
        $this->assertEquals(
            "666672",
            $outMsg0->fwdFee->toBase(10),
        );
        $this->assertEquals(
            "0",
            $outMsg0->ihrFee->toBase(10),
        );
        $this->assertEquals(
            "1825485000002",
            $outMsg0->createdLt,
        );
        $this->assertEquals(
            "lqKW0iTyhcZ77pPDD4owkVfw2qNdxbh+QQt4YwoJz8c=",
            $outMsg0->bodyHash,
        );
        $this->assertEquals(
            "msg.dataRaw",
            $outMsg0->msgData->type,
        );
        $this->assertEquals(
            "te6cckEBAQEAAgAAAEysuc0=",
            $outMsg0->msgData->body,
        );
        $this->assertEquals(
            "",
            $outMsg0->msgData->initState,
        );
        $this->assertEquals(
            "",
            $outMsg0->message,
        );
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
