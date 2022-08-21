<?php declare(strict_types=1);

namespace Olifanton\Ton\Tests\Contracts\Wallets\Simple;

use Olifanton\Mnemonic\TonMnemonic;
use Olifanton\Ton\Contracts\Wallets\Simple\SimpleWalletR3;
use Olifanton\Ton\Tests\Stubs\StubWords;
use PHPUnit\Framework\TestCase;

class SimpleWalletR3Test extends TestCase
{
    public function testGetName(): void
    {
        $this->assertEquals("simpleR3", SimpleWalletR3::getName());
    }

    /**
     * @throws \Olifanton\Boc\Exceptions\BitStringException
     * @throws \Olifanton\Boc\Exceptions\CellException
     * @throws \Olifanton\Mnemonic\Exceptions\TonMnemonicException
     */
    public function testAddress(): void
    {
        $keyPair = TonMnemonic::mnemonicToKeyPair(StubWords::WORDS);
        $wallet = new SimpleWalletR3($keyPair->publicKey, 0);
        $this->assertEquals(
            "UQB2yHpZRbGebMkqULlerVI6XkmHFVYmxrZzhdh1tkbPvUpH",
            $wallet->getAddress()->toString(true, true),
        );
    }
}
