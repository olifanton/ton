<?php declare(strict_types=1);

namespace Olifanton\Ton\IntegrationTests\ToncenterHttpClient;

class GetBlockHeaderITest extends ToncenterHttpClientIntegrationTestCase
{
    /**
     * @throws \Throwable
     */
    public function testSuccess(): void
    {
        $client = $this->getInstance();
        $response = $client->getBlockHeader(
            -1,
            "-9223372036854775808",
            4016065,
        );

        $this->assertEquals(-1, $response->id->workchain);
    }
}
