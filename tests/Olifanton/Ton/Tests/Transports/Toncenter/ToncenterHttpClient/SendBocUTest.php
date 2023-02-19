<?php declare(strict_types=1);

namespace Olifanton\Ton\Tests\Transports\Toncenter\ToncenterHttpClient;

use Olifanton\Interop\Boc\Cell;
use Olifanton\Interop\Bytes;

class SendBocUTest extends ToncenterHttpClientUTestCase
{
    /**
     * @throws \Throwable
     */
    public function testStringBoc(): void
    {
        $this->prepareSendMock("sendBoc/stub");

        $result = $this
            ->getInstance()
            ->sendBoc(Bytes::bytesToBase64((new Cell())->toBoc()));

        $this->assertTrue($result->ok);
    }

    /**
     * @throws \Throwable
     */
    public function testUint8ArrayBoc(): void
    {
        $this->prepareSendMock("sendBoc/stub");

        $result = $this
            ->getInstance()
            ->sendBoc((new Cell())->toBoc());

        $this->assertTrue($result->ok);
    }

    /**
     * @throws \Throwable
     */
    public function testCellBoc(): void
    {
        $this->prepareSendMock("sendBoc/stub");

        $result = $this
            ->getInstance()
            ->sendBoc(new Cell());

        $this->assertTrue($result->ok);
    }
}
