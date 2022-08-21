<?php declare(strict_types=1);

namespace Olifanton\Ton\Tests\Contracts\Wallets\V3;

use Olifanton\Mnemonic\TonMnemonic;
use Olifanton\Ton\Contracts\Wallets\V3\WalletV3R1;
use Olifanton\Ton\Tests\Stubs\StubWords;
use PHPUnit\Framework\TestCase;

class WalletV2R1Test extends TestCase
{
    public function testGetName(): void
    {
        $this->assertEquals("v3r1", WalletV3R1::getName());
    }

    /**
     * @throws \Olifanton\Boc\Exceptions\BitStringException
     * @throws \Olifanton\Boc\Exceptions\CellException
     * @throws \Olifanton\Mnemonic\Exceptions\TonMnemonicException
     */
    public function testAddress(): void
    {
        $keyPair = TonMnemonic::mnemonicToKeyPair(StubWords::WORDS);
        $wallet = new WalletV3R1($keyPair->publicKey, 0);
        $this->assertEquals(
            "UQBWgLsQ3n-NIM_E0geaCqypB4rNlCHCCJRiv1Sv78vJR8UH",
            $wallet->getAddress()->toString(true, true),
        );
    }
}
