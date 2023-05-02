<?php declare(strict_types=1);

namespace Olifanton\Ton\Tests\Contracts\Wallets\Simple;

use Olifanton\Mnemonic\Exceptions\TonMnemonicException;
use Olifanton\Mnemonic\TonMnemonic;
use Olifanton\Ton\Contracts\Exceptions\ContractException;
use Olifanton\Ton\Contracts\Wallets\Exceptions\WalletException;
use Olifanton\Ton\Contracts\Wallets\Simple\SimpleWalletR3;
use Olifanton\Ton\Contracts\Wallets\WalletOptions;
use Olifanton\Ton\Tests\Stubs\StubWords;
use PHPUnit\Framework\TestCase;

class SimpleWalletR3Test extends TestCase
{
    public function testGetName(): void
    {
        $this->assertEquals("simpleR3", SimpleWalletR3::getName());
    }

    /**
     * @throws ContractException|WalletException|TonMnemonicException
     */
    public function testAddress(): void
    {
        $keyPair = TonMnemonic::mnemonicToKeyPair(StubWords::WORDS);
        $wallet = new SimpleWalletR3(new WalletOptions(publicKey: $keyPair->publicKey));
        $this->assertEquals(
            "UQB2yHpZRbGebMkqULlerVI6XkmHFVYmxrZzhdh1tkbPvUpH",
            $wallet->getAddress()->toString(true, true),
        );
    }
}
