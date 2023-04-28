<?php declare(strict_types=1);

namespace Olifanton\Ton\Tests;

use Hamcrest\Core\IsEqual;
use Hamcrest\Type\IsString;
use Mockery\MockInterface;
use Olifanton\Interop\Units;
use Olifanton\Ton\Contracts\Messages\ExternalMessage;
use Olifanton\Ton\Contracts\Wallets\V3\WalletV3Options;
use Olifanton\Ton\Contracts\Wallets\V3\WalletV3R1;
use Olifanton\Ton\Deployer;
use Olifanton\Ton\DeployOptions;
use Olifanton\Ton\Exceptions\DeployerException;
use Olifanton\Ton\Exceptions\TransportException;
use Olifanton\Ton\Helpers\KeyPair;
use Olifanton\Ton\Tests\Stubs\StubSeqnoResponseStack;
use Olifanton\Ton\Tests\Stubs\StubWords;
use Olifanton\Ton\Transport;
use Olifanton\TypedArrays\Uint8Array;
use PHPUnit\Framework\TestCase;

class DeployerTest extends TestCase
{
    private Transport | MockInterface $transportMock;

    protected function setUp(): void
    {
        $this->transportMock = \Mockery::mock(Transport::class); // @phpstan-ignore-line
    }

    protected function tearDown(): void
    {
        \Mockery::close();
    }

    private function getInstance(): Deployer
    {
        return new Deployer(
            $this->transportMock,
        );
    }

    /**
     * @throws \Throwable
     */
    public function testDeploy(): void
    {
        $deployKP = StubWords::getKP();
        $deployWallet = new WalletV3R1(
            new WalletV3Options($deployKP->publicKey),
        );

        $newWalletPk = KeyPair::random();
        $newWallet = new \Olifanton\Ton\Contracts\Wallets\V4\WalletV4R2(
            new \Olifanton\Ton\Contracts\Wallets\V4\WalletV4Options(
                publicKey: $newWalletPk->publicKey,
            ),
        );

        /** @phpstan-ignore-next-line */
        $this
            ->transportMock
            ->shouldReceive("runGetMethod")
            ->with(IsEqual::equalTo($deployWallet), "seqno")
            ->once()
            ->andReturn(StubSeqnoResponseStack::create(10));

        $this
            ->transportMock
            ->shouldReceive("sendMessage")
            ->withArgs(function (ExternalMessage $message, Uint8Array $secretKey) use ($deployKP) {
                $this->assertEquals($secretKey, $deployKP->secretKey);

                return true;
            })
            ->once();

        $this
            ->getInstance()
            ->deploy(
                new DeployOptions(
                    $deployWallet,
                    $deployKP->secretKey,
                    Units::toNano("0.05"),
                ),
                $newWallet,
            );
    }

    /**
     * @throws \Throwable
     */
    public function testDeployCatchCreateExternal(): void
    {
        $deployKP = StubWords::getKP();
        $deployWallet = new WalletV3R1(
            new WalletV3Options($deployKP->publicKey),
        );

        $newWalletPk = KeyPair::random();
        $newWallet = new \Olifanton\Ton\Contracts\Wallets\V4\WalletV4R2(
            new \Olifanton\Ton\Contracts\Wallets\V4\WalletV4Options(
                publicKey: $newWalletPk->publicKey,
            ),
        );

        /** @phpstan-ignore-next-line */
        $this
            ->transportMock
            ->shouldReceive("runGetMethod")
            ->with(IsEqual::equalTo($deployWallet), "seqno")
            ->once()
            ->andThrow(new TransportException("Foo bar"));

        $this->expectException(DeployerException::class);
        $this->expectExceptionMessage("Foo bar");

        $this
            ->getInstance()
            ->deploy(
                new DeployOptions(
                    $deployWallet,
                    $deployKP->secretKey,
                    Units::toNano("0.05"),
                ),
                $newWallet,
            );
    }

    /**
     * @throws \Throwable
     */
    public function testDeployCatchSending(): void
    {
        $deployKP = StubWords::getKP();
        $deployWallet = new WalletV3R1(
            new WalletV3Options($deployKP->publicKey),
        );

        $newWalletPk = KeyPair::random();
        $newWallet = new \Olifanton\Ton\Contracts\Wallets\V4\WalletV4R2(
            new \Olifanton\Ton\Contracts\Wallets\V4\WalletV4Options(
                publicKey: $newWalletPk->publicKey,
            ),
        );

        /** @phpstan-ignore-next-line */
        $this
            ->transportMock
            ->shouldReceive("runGetMethod")
            ->with(IsEqual::equalTo($deployWallet), "seqno")
            ->once()
            ->andReturn(StubSeqnoResponseStack::create(10));

        /** @phpstan-ignore-next-line */
        $this
            ->transportMock
            ->shouldReceive("sendMessage")
            ->withArgs(function (ExternalMessage $message, Uint8Array $secretKey) use ($deployKP) {
                $this->assertEquals($secretKey, $deployKP->secretKey);

                return true;
            })
            ->once()
            ->andThrow(new TransportException("Foo bar"));

        $this->expectException(DeployerException::class);
        $this->expectExceptionMessage("Foo bar");

        $this
            ->getInstance()
            ->deploy(
                new DeployOptions(
                    $deployWallet,
                    $deployKP->secretKey,
                    Units::toNano("0.05"),
                ),
                $newWallet,
            );
    }

    /**
     * @throws \Throwable
     */
    public function testEstimateFee(): void
    {
        $deployKP = StubWords::getKP();
        $deployWallet = new WalletV3R1(
            new WalletV3Options($deployKP->publicKey),
        );

        $newWalletPk = KeyPair::random();
        $newWallet = new \Olifanton\Ton\Contracts\Wallets\V4\WalletV4R2(
            new \Olifanton\Ton\Contracts\Wallets\V4\WalletV4Options(
                publicKey: $newWalletPk->publicKey,
            ),
        );

        /** @phpstan-ignore-next-line */
        $this
            ->transportMock
            ->shouldReceive("estimateFee")
            ->with(
                IsEqual::equalTo($newWallet->getAddress()),
                IsString::stringValue(),
            )
            ->once()
            ->andReturn(Units::toNano("0.001"));

        $fee = $this
            ->getInstance()
            ->estimateFee(
                new DeployOptions(
                    $deployWallet,
                    $deployKP->secretKey,
                    Units::toNano("0.05"),
                ),
                $newWallet,
            );

        $this->assertEquals(0.001, Units::fromNano($fee)->toFloat());
    }

    /**
     * @throws \Throwable
     */
    public function testEstimateFeeCatchTransportException(): void
    {
        $deployKP = StubWords::getKP();
        $deployWallet = new WalletV3R1(
            new WalletV3Options($deployKP->publicKey),
        );

        $newWalletPk = KeyPair::random();
        $newWallet = new \Olifanton\Ton\Contracts\Wallets\V4\WalletV4R2(
            new \Olifanton\Ton\Contracts\Wallets\V4\WalletV4Options(
                publicKey: $newWalletPk->publicKey,
            ),
        );

        /** @phpstan-ignore-next-line */
        $this
            ->transportMock
            ->shouldReceive("estimateFee")
            ->with(
                IsEqual::equalTo($newWallet->getAddress()),
                IsString::stringValue(),
            )
            ->once()
            ->andThrow(new TransportException("Foo bar"));

        $this->expectException(DeployerException::class);
        $this->expectExceptionMessage("Foo bar");

        $this
            ->getInstance()
            ->estimateFee(
                new DeployOptions(
                    $deployWallet,
                    $deployKP->secretKey,
                    Units::toNano("0.05"),
                ),
                $newWallet,
            );
    }
}
