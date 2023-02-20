<?php declare(strict_types=1);

namespace Olifanton\Ton\Tests;

use Olifanton\Ton\ContractFactory;
use Olifanton\Ton\Contracts\Wallets\Simple\SimpleWalletR1;
use Olifanton\Ton\Contracts\Wallets\Simple\SimpleWalletR2;
use Olifanton\Ton\Contracts\Wallets\Simple\SimpleWalletR3;
use Olifanton\Ton\Contracts\Wallets\V2\WalletV2R1;
use Olifanton\Ton\Contracts\Wallets\V2\WalletV2R2;
use Olifanton\Ton\Contracts\Wallets\V3\WalletV3R1;
use Olifanton\Ton\Contracts\Wallets\V3\WalletV3R2;
use Olifanton\Ton\Contracts\Wallets\V4\WalletV4R1;
use Olifanton\Ton\Contracts\Wallets\V4\WalletV4R2;
use Olifanton\Ton\Tests\Stubs\Foo;
use Olifanton\Ton\Tests\Stubs\StubWords;
use Olifanton\Ton\Transports\NullTransport\NullTransport;
use PHPUnit\Framework\TestCase;

class ContractFactoryTest extends TestCase
{
    /**
     * @throws \Olifanton\Mnemonic\Exceptions\TonMnemonicException
     */
    public function testAllWallets(): void
    {
        $cases = [
            SimpleWalletR1::class,
            SimpleWalletR2::class,
            SimpleWalletR3::class,

            WalletV2R1::class,
            WalletV2R2::class,

            WalletV3R1::class,
            WalletV3R2::class,

            WalletV4R1::class,
            WalletV4R2::class,
        ];

        $instance = new ContractFactory(new NullTransport());

        foreach ($cases as $walletClass) {
            $wallet = $instance->get($walletClass, StubWords::getKP()->publicKey);
            $this->assertInstanceOf($walletClass, $wallet);
        }
    }

    /**
     * @throws \Throwable
     */
    public function testInvalidClass(): void
    {
        $instance = new ContractFactory(new NullTransport());

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Invalid contract class: " . Foo::class);

        $instance->get(Foo::class, StubWords::getKP()->publicKey); // @phpstan-ignore-line
    }
}
