<?php declare(strict_types=1);

namespace Olifanton\Ton\IntegrationTests\ToncenterHttpClient;

class GetConsensusBlockIntegrationTest extends ToncenterHttpClientIntegrationTestCase
{
    /**
     * @throws \Throwable
     */
    public function testSuccess(): void
    {
        $client = $this->getInstance();
        $resp = $client->getConsensusBlock();
        $this->assertGreaterThan(0, $resp->consensusBlock);
    }
}
