<?php declare(strict_types=1);

namespace Olifanton\Ton\Tests\Transports\Toncenter\ToncenterHttpClient;

use Olifanton\Interop\Bytes;
use Olifanton\Ton\AddressState;

class GetAddressInformationUTest extends ToncenterHttpClientUTestCase
{
    /**
     * @throws \Throwable
     */
    public function testUninitialized(): void
    {
        $this->prepareSendMock("getAddressInformation/uninitialized");

        $instance = $this->getInstance();
        $result = $instance->getAddressInformation($this->createAddressStub());

        $this->assertEquals("0", $result->balance->toBase(10));
        $this->assertNull($result->code);
        $this->assertNull($result->data);
        $this->assertEquals("0", $result->lastTransactionId->lt);
        $this->assertEquals("AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA=", $result->lastTransactionId->hash);
        $this->assertEquals(-1, $result->blockId->workchain);
        $this->assertEquals("-9223372036854775808", $result->blockId->shard);
        $this->assertEquals(23630593, $result->blockId->seqno);
        $this->assertEquals("isttJl5gI4VAtL39BTR9I8afTuo7oRKdWLKu8CfeXNg=", $result->blockId->rootHash);
        $this->assertEquals("78rXvC+19uv4PGMYXEB2bjGxVtz/+XE9KU5QxHG5GIw=", $result->blockId->fileHash);
        $this->assertEquals("", $result->frozenHash);
        $this->assertEquals(1663506642, $result->syncUtime);
        $this->assertEquals(AddressState::UNINITIALIZED, $result->state);
    }

    /**
     * @throws \Throwable
     */
    public function testActive(): void
    {
        $this->prepareSendMock("getAddressInformation/active");

        $instance = $this->getInstance();
        $result = $instance->getAddressInformation($this->createAddressStub());

        $this->assertEquals("4981639170606546912", $result->balance->toBase(10));
        $this->assertEquals(
            "te6cckEBAQEATgAAmP8AIN0gggFMl7qXMO1E0NcLH+Ck8mCBAgDXGCDXCx/tRNDTH9P/0VESuvKhIvkBVBBE+RDyovgAAdMfMdMH1NEB+wCkyMsfy//J7VRwECKG",
            Bytes::bytesToBase64($result->code->toBoc(false))
        );
        $this->assertEquals(
            "te6cckEBAQEAJgAASAAAABw2dcDNNLAgeSopYhPchzWDSGbJpJx5gVTk2NmopHSBQyIsV48=",
            Bytes::bytesToBase64($result->data->toBoc(false))
        );
        $this->assertEquals("3607013000001", $result->lastTransactionId->lt);
        $this->assertEquals("RDwPOlV8Ka+EPgB1igMI/PPcRepLqCgpj0YH3SySQf0=", $result->lastTransactionId->hash);
        $this->assertEquals(-1, $result->blockId->workchain);
        $this->assertEquals("-9223372036854775808", $result->blockId->shard);
        $this->assertEquals(3296617, $result->blockId->seqno);
        $this->assertEquals("OLEF5b+cnGHBp4GPy7bfZeqwfYOit1RvEGSpuWmB1go=", $result->blockId->rootHash);
        $this->assertEquals("jhl23eQQHLWrVe2K6DxSJtehzwtoSsmhIcrT1nNWR8c=", $result->blockId->fileHash);
        $this->assertEquals("", $result->frozenHash);
        $this->assertEquals(1663488131, $result->syncUtime);
        $this->assertEquals(AddressState::ACTIVE, $result->state);
    }
}
