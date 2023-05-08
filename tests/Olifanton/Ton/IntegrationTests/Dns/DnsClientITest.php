<?php declare(strict_types=1);

namespace Olifanton\Ton\IntegrationTests\Dns;

use Olifanton\Ton\Dns\DnsClient;
use Olifanton\Ton\IntegrationTests\Traits\ToncenterHttpClientTrait;
use Olifanton\Ton\Transports\Toncenter\ToncenterTransport;
use PHPUnit\Framework\TestCase;

class DnsClientITest extends TestCase
{
    use ToncenterHttpClientTrait;

    private function getInstance(): DnsClient
    {
        return new DnsClient(
            new ToncenterTransport($this->createToncenterHttpV2Client()),
        );
    }

    /**
     * @throws \Throwable
     */
    public function testResolveAddr(): void
    {
        $result = $this->getInstance()->resolve("foobarbazover9000yolo.ton");
        $this
            ->assertEquals(
                "0:2d95c14fb61944de50731c8f3ded3caeda50a88b8ddf7cdb57ddf8dc3da962cf",
                $result->getWallet()->toString(false),
            );
    }
}
