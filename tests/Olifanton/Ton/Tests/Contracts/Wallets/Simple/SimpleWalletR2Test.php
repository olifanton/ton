<?php declare(strict_types=1);

namespace Olifanton\Ton\Tests\Contracts\Wallets\Simple;

use Olifanton\Mnemonic\Exceptions\TonMnemonicException;
use Olifanton\Mnemonic\TonMnemonic;
use Olifanton\Ton\Contracts\Exceptions\ContractException;
use Olifanton\Ton\Contracts\Wallets\Exceptions\WalletException;
use Olifanton\Ton\Contracts\Wallets\Simple\SimpleWalletR2;
use Olifanton\Ton\Tests\Stubs\StubWords;
use PHPUnit\Framework\TestCase;

class SimpleWalletR2Test extends TestCase
{
    public function testGetName(): void
    {
        $this->assertEquals("simpleR2", SimpleWalletR2::getName());
    }

    /**
     * @throws ContractException|WalletException|TonMnemonicException
     */
    public function testAddress(): void
    {
        $keyPair = TonMnemonic::mnemonicToKeyPair(StubWords::WORDS);
        $wallet = new SimpleWalletR2($keyPair->publicKey, 0);
        $this->assertEquals(
            "UQDr32mHaHQWwSGSIC_g31GlFfUIWSYsen5rq3x_cHgK-5Ub",
            $wallet->getAddress()->toString(true, true),
        );
    }
}
