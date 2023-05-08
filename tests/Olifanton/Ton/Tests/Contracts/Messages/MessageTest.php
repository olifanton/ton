<?php declare(strict_types=1);

namespace Olifanton\Ton\Tests\Contracts\Messages;

use Olifanton\Interop\Boc\Cell;
use Olifanton\Interop\Bytes;
use Olifanton\Ton\Tests\Stubs\StubMessage;
use PHPUnit\Framework\TestCase;

class MessageTest extends TestCase
{
    /**
     * @throws \Throwable
     */
    public function testSimple(): void
    {
        $stubRef = new Cell();
        $stubRef
            ->bits
            ->writeBit(1);

        $header = new Cell();
        $header
            ->bits
            ->writeString("foo");
        $header->writeCell($stubRef);

        $instance = new StubMessage($header);
        $resultCellHashStr = Bytes::bytesToHexString($instance->cell()->hash());

        $this->assertEquals(
            "c052776153bcf986abd0f36c293dfd1376514565d060f607817fa69c2d5904db",
            $resultCellHashStr,
        );
    }

    /**
     * @throws \Throwable
     */
    public function testSimpleSigned(): void
    {
        $header = new Cell();
        $header
            ->bits
            ->writeString("foo");

        $body = new Cell();
        $body
            ->bits
            ->writeString("bar");

        $secretKey = Bytes::base64ToBytes("WETxFdMU/4MzMe4Cu/6jWLWgwVIcZecPjCnL3p84tcPvEX8wDU7KD4j/0X0ANA3uDIZLDYMAGXIDFDwDavO+KQ==");

        $instance = new StubMessage($header, $body);
        $resultCellHashStr = Bytes::bytesToHexString(
            $instance
                ->sign($secretKey)
                ->hash()
        );

        $this->assertEquals(
            "a1b659abdeda6e02f34ce0a29b2356472f2b71a18f076c77bec1a38e39484bd0",
            $resultCellHashStr,
        );
    }

    /**
     * @throws \Throwable
     */
    public function testSimpleSignedRefBody(): void
    {
        $header = new Cell();
        $header
            ->bits
            ->writeString("foobarbaz");

        $body = new Cell();
        $body
            ->bits
            ->writeString("Lorem ipsum dolor sit amet, consectetur adipisicing elit");

        $secretKey = Bytes::base64ToBytes("WETxFdMU/4MzMe4Cu/6jWLWgwVIcZecPjCnL3p84tcPvEX8wDU7KD4j/0X0ANA3uDIZLDYMAGXIDFDwDavO+KQ==");

        $instance = new StubMessage($header, $body);
        $resultCellHashStr = Bytes::bytesToHexString(
            $instance
                ->sign($secretKey)
                ->hash()
        );

        $this->assertEquals(
            "98fa0b897ffc6a27a75ee4cb7c79d6556c53ae60b4e7fa68caf035babdc7751b",
            $resultCellHashStr,
        );
    }

    /**
     * @throws \Throwable
     */
    public function testWBodyWState(): void
    {
        $header = new Cell();
        $header
            ->bits
            ->writeString("foo");

        $body = new Cell();
        $body
            ->bits
            ->writeString("bar");

        $state = new Cell();
        $state
            ->bits
            ->writeString("baz");

        $instance = new StubMessage($header, $body, $state);

        $resultCellHashStr = Bytes::bytesToHexString(
            $instance
                ->cell()
                ->hash()
        );

        $this->assertEquals(
            "0226ec96669f20eca5d02456deff0a4c1a14eceb8fad4bb3f99784885463e6e3",
            $resultCellHashStr,
        );
    }
}
