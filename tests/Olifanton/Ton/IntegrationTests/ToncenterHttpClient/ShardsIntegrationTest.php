<?php declare(strict_types=1);

namespace Olifanton\Ton\IntegrationTests\ToncenterHttpClient;

class ShardsIntegrationTest extends ToncenterHttpClientIntegrationTestCase
{
    /**
     * @throws \Throwable
     */
    public function testSuccess(): void
    {
        $client = $this->getInstance();
        $resp = $client->shards(4016065);

        $this->assertEquals(0, $resp->shards[0]->workchain);
    }
}
