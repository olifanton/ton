<?php declare(strict_types=1);

namespace Olifanton\Ton\Tests\Contracts\Wallets\V4;

use Olifanton\Mnemonic\TonMnemonic;
use Olifanton\Ton\Contracts\Wallets\V4\WalletV4R1;
use Olifanton\Ton\Tests\Stubs\StubWords;
use PHPUnit\Framework\TestCase;

class WalletV4R1Test extends TestCase
{
    public function testGetName(): void
    {
        $this->assertEquals("v4r1", WalletV4R1::getName());
    }

    /**
     * @throws \Olifanton\Boc\Exceptions\BitStringException
     * @throws \Olifanton\Boc\Exceptions\CellException
     * @throws \Olifanton\Mnemonic\Exceptions\TonMnemonicException
     */
    public function testAddress(): void
    {
        $keyPair = TonMnemonic::mnemonicToKeyPair(StubWords::WORDS);
        $wallet = new WalletV4R1($keyPair->publicKey, 0);
        $this->assertEquals(
            "UQBcCbR40Hzw9Gp6nVlcP8aILuHQuiW6jhSnaLG4TGG2Nsle",
            $wallet->getAddress()->toString(true, true),
        );
    }
}
