<?php declare(strict_types=1);

namespace Olifanton\Ton\Tests\Contracts\Messages;

use Olifanton\Interop\Address;
use Olifanton\Interop\Boc\Cell;
use Olifanton\Interop\Bytes;
use Olifanton\Interop\Units;
use Olifanton\Ton\Contracts\Messages\InternalMessage;
use Olifanton\Ton\Contracts\Messages\InternalMessageOptions;
use Olifanton\Ton\Contracts\Messages\MessageData;
use PHPUnit\Framework\TestCase;

class InternalMessageTest extends TestCase
{
    /**
     * @throws \Throwable
     */
    public function testCreateInternalMessage(): void
    {
        $body = new Cell();
        $body->bits->writeString("foo");

        $state = new Cell();
        $state->bits->writeString("bar");

        $instance = new InternalMessage(
            new InternalMessageOptions(
                false,
                dest: new Address("UQBcCbR40Hzw9Gp6nVlcP8aILuHQuiW6jhSnaLG4TGG2Nsle"),
                value: Units::toNano(0),
            ),
            new MessageData(
                $body,
                $state,
            )
        );

        $this->assertEquals(
            "cee972e7666975ee4c702507d4e36a4f65555a81c8672c17d2f5052fdac1e2f4",
            Bytes::bytesToHexString($instance->cell()->hash()),
        );
    }
}
