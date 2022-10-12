<?php declare(strict_types=1);

namespace Olifanton\Ton\Tests\Toncenter\ToncenterHttpClient;

class GetMasterchainInfoUnitTest extends ToncenterHttpClientUnitTestCase
{
    /**
     * @throws \Throwable
     */
    public function testInfo(): void
    {
        $response = $this->createResponseDataStub("getMasterchainInfo/info");
        $this
            ->httpClientMock
            ->shouldReceive("send")
            ->andReturn($response);

        $instance = $this->getInstance();
        $result = $instance->getMasterchainInfo();

        $this->assertEquals(
            -1,
            $result->last->workchain,
        );
        $this->assertEquals(
            "-9223372036854775808",
            $result->last->shard,
        );
        $this->assertEquals(
            24226822,
            $result->last->seqno,
        );
        $this->assertEquals(
            "+t92toJ65fYekK43K3JEqxOzNPCcxPY5nko4YRT8Ycc=",
            $result->last->rootHash,
        );
        $this->assertEquals(
            "sDvaN/GEV2Pu71UgpD8/egAN5jdeh7/3KnvjzuU8D4g=",
            $result->last->fileHash,
        );

        $this->assertEquals(
            "3igEwCKKuGDJ9tVDeQ27UTGFD/4I91PAp9BcSq073Sc=",
            $result->stateRootHash,
        );

        $this->assertEquals(
            -1,
            $result->init->workchain,
        );
        $this->assertEquals(
            "0",
            $result->init->shard,
        );
        $this->assertEquals(
            0,
            $result->init->seqno,
        );
        $this->assertEquals(
            "F6OpKZKqvqeFp6CQmFomXNMfMj2EnaUSOXN+Mh+wVWk=",
            $result->init->rootHash,
        );
        $this->assertEquals(
            "XplPz01CXAps5qeSWUtxcyBfdAo5zVb1N979KLSKD24=",
            $result->init->fileHash,
        );

        $this->assertEquals(
            "1665588015.5899098:13:0.39802945269686596",
            $result->extra,
        );
    }
}
