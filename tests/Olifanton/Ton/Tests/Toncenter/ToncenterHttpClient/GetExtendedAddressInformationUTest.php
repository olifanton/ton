<?php declare(strict_types=1);

namespace Olifanton\Ton\Tests\Toncenter\ToncenterHttpClient;

use Olifanton\Interop\Bytes;

class GetExtendedAddressInformationUTest extends ToncenterHttpClientUTestCase
{
    /**
     * @throws \Throwable
     */
    public function testUninitialized(): void
    {
        $this->prepareSendMock("getExtendedAddressInformation/uninitialized");

        $instance = $this->getInstance();
        $result = $instance->getExtendedAddressInformation($this->createAddressStub());

        $this->assertEquals(
            "UQClkP6tXXx-ln5ahF24FR_MfPv9cZR9tbyU8deXtgjjOLVm",
            $result->address,
        );
        $this->assertEquals("-1", $result->balance->toBase(10));
        $this->assertEquals("0", $result->lastTransactionId->lt);
        $this->assertEquals("AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA=", $result->lastTransactionId->hash);
        $this->assertEquals(-1, $result->blockId->workchain);
        $this->assertEquals("-9223372036854775808", $result->blockId->shard);
        $this->assertEquals(23929063, $result->blockId->seqno);
        $this->assertEquals("9rGlxl+gKiVX4K/xT+E9IhUb/oQTqCeDGNambVYVuuc=", $result->blockId->rootHash);
        $this->assertEquals("8paLgoRePvUcrZKbtkOTC1DbRoSzh8dV6u5m0IVAscE=", $result->blockId->fileHash);
        $this->assertEquals(1664553825, $result->syncUtime);
        $this->assertEquals(0, $result->revision);
        $this->assertNull($result->accountState->code);
        $this->assertNull($result->accountState->data);
        $this->assertEquals("", $result->accountState->frozenHash);
        $this->assertEquals("uninited.accountState", $result->accountState->type);
    }

    /**
     * @throws \Throwable
     */
    public function testActiveUnknown(): void
    {
        $this->prepareSendMock("getExtendedAddressInformation/active-unknown");

        $instance = $this->getInstance();
        $result = $instance->getExtendedAddressInformation($this->createAddressStub());

        $this->assertEquals(
            "EQC72vpFsACenrjlTLRBEb2gVjXeM3-PtDlhQOwofIuk3Ymi",
            $result->address,
        );
        $this->assertEquals("29997945725", $result->balance->toBase(10));
        $this->assertEquals("31630489000001", $result->lastTransactionId->lt);
        $this->assertEquals("La8q0GD2faitgS0dXcQ6y5C2/zm3xcZ0XeepdsyZfLw=", $result->lastTransactionId->hash);
        $this->assertEquals(-1, $result->blockId->workchain);
        $this->assertEquals("-9223372036854775808", $result->blockId->shard);
        $this->assertEquals(23928769, $result->blockId->seqno);
        $this->assertEquals("l3ghfP/+2I+CzMiIr86UQmf/eGmchK4m7eXivOCVZM0=", $result->blockId->rootHash);
        $this->assertEquals("QmAVIECroNPcLso0M86keX4yEDvGxPMvNlgxYXbKFYo=", $result->blockId->fileHash);
        $this->assertEquals(1664552810, $result->syncUtime);
        $this->assertEquals(
            "raw.accountState",
            $result->accountState->type,
        );
        $this->assertTrue(
            str_starts_with(Bytes::bytesToBase64($result->accountState->code->toBoc(false)), "te6cckECFAEAAtQAART/APSkE/S88sgLAQ")
        );
        $this->assertEquals(
            "te6cckEBAQEAKwAAUQAAA1QpqaMXvMQB7f/VCp92VGlKdDW5ZDUP8wgCpJhehst3Ao3l3R9AJRpmXg==",
            Bytes::bytesToBase64($result->accountState->data->toBoc(false))
        );
        $this->assertEquals("", $result->accountState->frozenHash);
    }

    /**
     * @throws \Throwable
     */
    public function testActiveV3(): void
    {
        $this->prepareSendMock("getExtendedAddressInformation/active-v3");

        $instance = $this->getInstance();
        $result = $instance->getExtendedAddressInformation($this->createAddressStub());

        $this->assertEquals(
            "EQDxxpeLM0R2HH3nmtDoQsRL959eYb4pRW1tNL257U30KBOX",
            $result->address,
        );
        $this->assertEquals("181587202878", $result->balance->toBase(10));
        $this->assertEquals("1853463000003", $result->lastTransactionId->lt);
        $this->assertEquals("yLMjCZ5lK1a9Elo6iXHg/zo4mF9QS9wuK8X61X379dA=", $result->lastTransactionId->hash);
        $this->assertEquals(-1, $result->blockId->workchain);
        $this->assertEquals("-9223372036854775808", $result->blockId->shard);
        $this->assertEquals(3651654, $result->blockId->seqno);
        $this->assertEquals("Cfi9aWmd6sXIRDFnHRdW45IVO+tobzHDTBDsy/tucxI=", $result->blockId->rootHash);
        $this->assertEquals("LRHmAUMwjTQG+ELXppzdERFlx2a53DmnARsw65LaN94=", $result->blockId->fileHash);
        $this->assertEquals(1664552653, $result->syncUtime);
        $this->assertEquals(
            "wallet.v3.accountState",
            $result->accountState->type,
        );
        $this->assertEquals(
            "698983191",
            $result->accountState->walletId,
        );
        $this->assertEquals(
            16,
            $result->accountState->seqno,
        );
        $this->assertEquals(2, $result->revision);
    }
}
