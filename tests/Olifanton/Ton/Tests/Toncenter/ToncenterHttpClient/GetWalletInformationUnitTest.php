<?php declare(strict_types=1);

namespace Olifanton\Ton\Tests\Toncenter\ToncenterHttpClient;

use Olifanton\Ton\Models\AddressState;

class GetWalletInformationUnitTest extends ToncenterHttpClientUnitTestCase
{
    /**
     * @throws \Throwable
     */
    public function testUninitialized(): void
    {
        $response = $this->createResponseDataStub("getWalletInformation/uninitialized");
        $this
            ->httpClientMock
            ->shouldReceive("send")
            ->andReturn($response);

        $instance = $this->getInstance();
        $result = $instance->getWalletInformation($this->createAddressStub());

        $this->assertFalse($result->wallet);
        $this->assertEquals("0", $result->balance->toBase(10));
        $this->assertEquals(AddressState::UNINITIALIZED, $result->accountState);
        $this->assertEquals("0", $result->lastTransactionId->lt);
        $this->assertEquals("AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA=", $result->lastTransactionId->hash);
    }

    /**
     * @throws \Throwable
     */
    public function testV3(): void
    {
        $response = $this->createResponseDataStub("getWalletInformation/v3");
        $this
            ->httpClientMock
            ->shouldReceive("send")
            ->andReturn($response);

        $instance = $this->getInstance();
        $result = $instance->getWalletInformation($this->createAddressStub());

        $this->assertTrue($result->wallet);
        $this->assertEquals("181587202878", $result->balance->toBase(10));
        $this->assertEquals(AddressState::ACTIVE, $result->accountState);
        $this->assertEquals("wallet v3 r2", $result->walletType);
        $this->assertEquals(16, $result->seqno);
        $this->assertEquals("1853463000003", $result->lastTransactionId->lt);
        $this->assertEquals("yLMjCZ5lK1a9Elo6iXHg/zo4mF9QS9wuK8X61X379dA=", $result->lastTransactionId->hash);
        $this->assertEquals(698983191, $result->walletId);
    }
}
