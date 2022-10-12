<?php declare(strict_types=1);

namespace Olifanton\Ton\IntegrationTests\ToncenterHttpClient;

class LookupBlockIntegrationTest extends ToncenterHttpClientIntegrationTestCase
{
    /**
     * @throws \Throwable
     */
    public function testSuccess(): void
    {
        $client = $this->getInstance();
        $result = $client->lookupBlock(
            -1,
            "-9223372036854775808",
            4016065,
        );
        $this->assertEquals(
            -1,
            $result->workchain,
        );
    }
}
