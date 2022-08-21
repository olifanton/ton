<?php declare(strict_types=1);

namespace Olifanton\Ton\Tests\Contracts\Wallets\V3;

use Olifanton\Mnemonic\TonMnemonic;
use Olifanton\Ton\Contracts\Wallets\V3\WalletV3R2;
use Olifanton\Ton\Tests\Stubs\StubWords;
use PHPUnit\Framework\TestCase;

class WalletV2R2Test extends TestCase
{
    public function testGetName(): void
    {
        $this->assertEquals("v3r2", WalletV3R2::getName());
    }

    /**
     * @throws \Olifanton\Boc\Exceptions\BitStringException
     * @throws \Olifanton\Boc\Exceptions\CellException
     * @throws \Olifanton\Mnemonic\Exceptions\TonMnemonicException
     */
    public function testAddress(): void
    {
        $keyPair = TonMnemonic::mnemonicToKeyPair(StubWords::WORDS);
        $wallet = new WalletV3R2($keyPair->publicKey, 0);
        $this->assertEquals(
            "UQClkP6tXXx-ln5ahF24FR_MfPv9cZR9tbyU8deXtgjjOLVm",
            $wallet->getAddress()->toString(true, true),
        );
    }
}
