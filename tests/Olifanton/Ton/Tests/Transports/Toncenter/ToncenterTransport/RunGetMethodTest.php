<?php declare(strict_types=1);

namespace Olifanton\Ton\Tests\Transports\Toncenter\ToncenterTransport;

use Hamcrest\Core\IsEqual;
use Mockery\MockInterface;
use Olifanton\Ton\Contracts\ContractOptions;
use Olifanton\Ton\Contracts\Exceptions\ContractException;
use Olifanton\Ton\Contracts\Wallets\Simple\SimpleWalletR1;
use Olifanton\Ton\Contracts\Wallets\Wallet;
use Olifanton\Ton\Exceptions\TransportException;
use Olifanton\Ton\Marshalling\Json\Hydrator;
use Olifanton\Ton\Tests\Stubs\StubWords;
use Olifanton\Ton\Tests\Transports\Toncenter\ToncenterHttpClient\ToncenterHttpClientUTestCase;
use Olifanton\Ton\Transports\Toncenter\Exceptions\ClientException;
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
            "85143",
            $instance
                ->runGetMethod($wallet, "foo")
                ->currentBigInteger()
                ->toBase(10),
        );
    }

    /**
     * @throws \Throwable
     */
    public function testRunAddressError(): void
    {
        $instance = $this->getInstance();
        $wallet = \Mockery::mock(Wallet::class);
        /** @phpstan-ignore-next-line */
        $wallet
            ->shouldReceive("getAddress")
            ->andThrow(new ContractException("bar"));
        /** @var Wallet $wallet */

        $this->expectException(TransportException::class);
        $this->expectExceptionMessage("Contract address error: bar");

        $instance->runGetMethod($wallet, 'foo');
    }

    /**
     * @throws \Throwable
     */
    public function testRunNonZeroExitError(): void
    {
        $instance = $this->getInstance();
        $wallet = new SimpleWalletR1(new ContractOptions(publicKey: StubWords::getKP()->publicKey));

        $dataStub = ToncenterHttpClientUTestCase::getDataStub('runGetMethod/result')['result'];
        $dataStub['exit_code'] = 99;

        /** @phpstan-ignore-next-line */
        $this
            ->clientMock
            ->shouldReceive("runGetMethod")
            ->once()
            ->with(IsEqual::equalTo($wallet->getAddress()), "foo", [])
            ->andReturn(Hydrator::extract(
                UnrecognizedSmcRunResult::class,
                $dataStub,
            ));

        $this->expectException(TransportException::class);
        $this->expectExceptionMessage("Non-zero exit code, code: 99");

        $instance->runGetMethod($wallet, "foo");
    }

    /**
     * @throws \Throwable
     */
    public function testRunClientError(): void
    {
        $instance = $this->getInstance();
        $wallet = new SimpleWalletR1(new ContractOptions(publicKey: StubWords::getKP()->publicKey));

        /** @phpstan-ignore-next-line */
        $this
            ->clientMock
            ->shouldReceive("runGetMethod")
            ->once()
            ->with(IsEqual::equalTo($wallet->getAddress()), "foo", [])
            ->andThrow(new ClientException("bar"));

        $this->expectException(TransportException::class);
        $this->expectExceptionMessage(
            "Get method error: bar; address: UQBEPqGRDlRwvt9dg+Q1q/dlHXndljNcZgUqp0pKTJffv117, method: foo"
        );

        $instance->runGetMethod($wallet, "foo");
    }

    public function testRunStackParsingError(): void
    {
        $instance = $this->getInstance();
        $wallet = new SimpleWalletR1(new ContractOptions(publicKey: StubWords::getKP()->publicKey));

        $dataStub = ToncenterHttpClientUTestCase::getDataStub('runGetMethod/result')['result'];
        $dataStub['stack'] = [
            ['foo', 'bar']
        ];

        /** @phpstan-ignore-next-line */
        $this
            ->clientMock
            ->shouldReceive("runGetMethod")
            ->once()
            ->with(IsEqual::equalTo($wallet->getAddress()), "foo", [])
            ->andReturn(Hydrator::extract(
                UnrecognizedSmcRunResult::class,
                $dataStub,
            ));

        $this->expectException(TransportException::class);
        $this->expectExceptionMessage(
            "Stack parsing error: Unknown type: foo; address: UQBEPqGRDlRwvt9dg+Q1q/dlHXndljNcZgUqp0pKTJffv117, method: foo"
        );

        $instance->runGetMethod($wallet, "foo");
    }
}
