<?php declare(strict_types=1);

namespace Olifanton\Ton\Tests\Contracts\Messages;

use Olifanton\Interop\Address;
use Olifanton\Interop\Boc\Cell;
use Olifanton\Interop\Bytes;
use Olifanton\Interop\Units;
use Olifanton\Ton\Contracts\Messages\ExternalMessage;
use Olifanton\Ton\Contracts\Messages\ExternalMessageOptions;
use Olifanton\Ton\Contracts\Messages\MessageData;
use PHPUnit\Framework\TestCase;

class ExternalMessageTest extends TestCase
{
    /**
     * @throws \Throwable
     */
    public function testCreateExternalMessage(): void
    {
        $body = new Cell();
        $body->bits->writeString("foo");

        $state = new Cell();
        $state->bits->writeString("bar");

        $instance = new ExternalMessage(
            new ExternalMessageOptions(
                dest: new Address("UQBcCbR40Hzw9Gp6nVlcP8aILuHQuiW6jhSnaLG4TGG2Nsle"),
                importFee: Units::toNano("0.01"),
            ),
            new MessageData(
                $body,
                $state,
            )
        );

        $this->assertEquals(
            "5ba46912974babfbd25dca4ddacdfcb4b291e686f4349deea8f14857f0051a46",
            Bytes::bytesToHexString($instance->cell()->hash()),
        );
    }
}
