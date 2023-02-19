<?php declare(strict_types=1);

namespace Olifanton\Ton\Tests\Transports\Toncenter\ToncenterHttpClient;

use Olifanton\Ton\AddressState;

class GetWalletInformationUTest extends ToncenterHttpClientUTestCase
{
    /**
     * @throws \Throwable
     */
    public function testUninitialized(): void
    {
        $this->prepareSendMock("getWalletInformation/uninitialized");

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
        $this->prepareSendMock("getWalletInformation/v3");

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
