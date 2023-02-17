<?php declare(strict_types=1);

namespace Olifanton\Ton\Tests\Toncenter\ToncenterHttpClient;

class ShardsUTest extends ToncenterHttpClientUTestCase
{
    /**
     * @throws \Throwable
     */
    public function testSuccess(): void
    {
        $this->prepareSendMock("shards/result");

        $instance = $this->getInstance();
        $result = $instance->shards(29774672);

        $this->assertCount(1, $result->shards);
    }
}
