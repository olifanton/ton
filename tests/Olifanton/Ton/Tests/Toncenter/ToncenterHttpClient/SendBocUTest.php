<?php declare(strict_types=1);

namespace Olifanton\Ton\Tests\Toncenter\ToncenterHttpClient;

use Olifanton\Interop\Boc\Cell;
use Olifanton\Interop\Bytes;

class SendBocUTest extends ToncenterHttpClientUTestCase
{
    /**
     * @throws \Throwable
     */
    public function testStringBoc(): void
    {
        $response = $this->createResponseDataStub("sendBoc/stub");
        $this
            ->httpClientMock
            ->shouldReceive("send")
            ->andReturn($response);

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
        $response = $this->createResponseDataStub("sendBoc/stub");
        $this
            ->httpClientMock
            ->shouldReceive("send")
            ->andReturn($response);

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
        $response = $this->createResponseDataStub("sendBoc/stub");
        $this
            ->httpClientMock
            ->shouldReceive("send")
            ->andReturn($response);

        $result = $this
            ->getInstance()
            ->sendBoc(new Cell());

        $this->assertTrue($result->ok);
    }
}
