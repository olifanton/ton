<?php declare(strict_types=1);

namespace Olifanton\Ton\Tests\Contracts;

use Olifanton\Ton\Contract;
use Olifanton\Ton\Contracts\Jetton\JettonMinter;
use Olifanton\Ton\Contracts\Jetton\JettonWallet;
use Olifanton\Ton\Contracts\Nft\NftCollection;
use Olifanton\Ton\Contracts\Nft\NftItem;
use Olifanton\Ton\Contracts\Wallets\Highload\HighloadWalletV2;
use Olifanton\Ton\Contracts\Wallets\Simple\SimpleWalletR1;
use Olifanton\Ton\Contracts\Wallets\Simple\SimpleWalletR2;
use Olifanton\Ton\Contracts\Wallets\Simple\SimpleWalletR3;
use Olifanton\Ton\Contracts\Wallets\V2\WalletV2R1;
use Olifanton\Ton\Contracts\Wallets\V2\WalletV2R2;
use Olifanton\Ton\Contracts\Wallets\V3\WalletV3R1;
use Olifanton\Ton\Contracts\Wallets\V3\WalletV3R2;
use Olifanton\Ton\Contracts\Wallets\V4\WalletV4R1;
use Olifanton\Ton\Contracts\Wallets\V4\WalletV4R2;
use PHPUnit\Framework\TestCase;

class ContractNameTest extends TestCase
{
    public function testGetName(): void
    {
        /** @var class-string<Contract>[] $cases */
        $cases = [
            SimpleWalletR1::class,
            WalletV3R1::class,
            HighloadWalletV2::class,
            SimpleWalletR3::class,
            WalletV4R1::class,
            WalletV3R2::class,
            SimpleWalletR2::class,
            WalletV2R2::class,
            WalletV2R1::class,
            WalletV4R2::class,
            NftItem::class,
            NftCollection::class,
            JettonWallet::class,
            JettonMinter::class,
        ];

        foreach ($cases as $smcClass) {
            /** @var class-string<Contract> $smcClass */
            $this->assertIsString(call_user_func([$smcClass, "getName"]), $smcClass);
        }
    }
}
