<?php declare(strict_types=1);

namespace Olifanton\Ton\IntegrationTests\ToncenterHttpClient;

class GetMasterchainInfoITest extends ToncenterHttpClientITestCase
{
    /**
     * @throws \Throwable
     */
    public function testSuccess(): void
    {
        $client = $this->getInstance();
        $resp = $client->getMasterchainInfo();
        $this->assertEquals(-1, $resp->last->workchain);
    }
}
