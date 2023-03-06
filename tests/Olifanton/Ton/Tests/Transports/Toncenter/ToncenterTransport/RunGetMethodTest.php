<?php declare(strict_types=1);

namespace Olifanton\Ton\Tests\Transports\Toncenter\ToncenterTransport;

use Hamcrest\Core\IsEqual;
use Mockery\MockInterface;
use Olifanton\Ton\Contracts\ContractOptions;
use Olifanton\Ton\Contracts\Wallets\Simple\SimpleWalletR1;
use Olifanton\Ton\Marshalling\Json\Hydrator;
use Olifanton\Ton\Tests\Stubs\StubWords;
use Olifanton\Ton\Tests\Transports\Toncenter\ToncenterHttpClient\ToncenterHttpClientUTestCase;
use Olifanton\Ton\Transports\Toncenter\Responses\UnrecognizedSmcRunResult;
use Olifanton\Ton\Transports\Toncenter\ToncenterTransport;
use Olifanton\Ton\Transports\Toncenter\ToncenterV2Client;
use PHPUnit\Framework\TestCase;

class RunGetMethodTest extends TestCase
{
    private ToncenterV2Client & MockInterface $clientMock;

    protected function setUp(): void
    {
        $this->clientMock = \Mockery::mock(ToncenterV2Client::class); // @phpstan-ignore-line
    }

    protected function tearDown(): void
    {
        \Mockery::close();
    }

    private function getInstance(): ToncenterTransport
    {
        return new ToncenterTransport(
            $this->clientMock,
        );
    }

    /**
     * @throws \Throwable
     */
    public function testRunSuccess(): void
    {
        $instance = $this->getInstance();
        $wallet = new SimpleWalletR1(new ContractOptions(publicKey: StubWords::getKP()->publicKey));

        /** @phpstan-ignore-next-line */
        $this
            ->clientMock
            ->shouldReceive("runGetMethod")
            ->once()
            ->with(IsEqual::equalTo($wallet->getAddress()), "foo", [])
            ->andReturn(Hydrator::extract(
                UnrecognizedSmcRunResult::class,
                ToncenterHttpClientUTestCase::getDataStub('runGetMethod/result')['result'],
            ));

        $this->assertEquals(
            [
                ['num', '0x14c97'],
            ],
            $instance->runGetMethod($wallet, "foo")
        );
    }
}
