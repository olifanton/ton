<?php declare(strict_types=1);

namespace Olifanton\Ton\Tests\Contracts\Wallets\V4;

use Olifanton\Mnemonic\TonMnemonic;
use Olifanton\Ton\Contracts\Wallets\V4\WalletV4R2;
use Olifanton\Ton\Tests\Stubs\StubWords;
use PHPUnit\Framework\TestCase;

class WalletV4R2Test extends TestCase
{
    public function testGetName(): void
    {
        $this->assertEquals("v4r2", WalletV4R2::getName());
    }

    /**
     * @throws \Olifanton\Boc\Exceptions\BitStringException
     * @throws \Olifanton\Boc\Exceptions\CellException
     * @throws \Olifanton\Mnemonic\Exceptions\TonMnemonicException
     */
    public function testAddress(): void
    {
        $keyPair = TonMnemonic::mnemonicToKeyPair(StubWords::WORDS);
        $wallet = new WalletV4R2($keyPair->publicKey, 0);
        $this->assertEquals(
            "UQDH6ELHpOUPfJfDg6ZxO89z7ZyRSI60MkP8CVWdQXMYYV-O",
            $wallet->getAddress()->toString(true, true),
        );
    }
}
