<?php declare(strict_types=1);

namespace Olifanton\Ton\Tests\Contracts\Wallets\V3;

use Olifanton\Mnemonic\Exceptions\TonMnemonicException;
use Olifanton\Mnemonic\TonMnemonic;
use Olifanton\Ton\Contracts\Exceptions\ContractException;
use Olifanton\Ton\Contracts\Wallets\Exceptions\WalletException;
use Olifanton\Ton\Contracts\Wallets\V3\WalletV3Options;
use Olifanton\Ton\Contracts\Wallets\V3\WalletV3R1;
use Olifanton\Ton\Tests\Stubs\StubWords;
use PHPUnit\Framework\TestCase;

class WalletV3R1Test extends TestCase
{
    public function testGetName(): void
    {
        $this->assertEquals("v3r1", WalletV3R1::getName());
    }

    /**
     * @throws ContractException|WalletException|TonMnemonicException
     */
    public function testAddress(): void
    {
        $keyPair = TonMnemonic::mnemonicToKeyPair(StubWords::WORDS);
        $wallet = new WalletV3R1(new WalletV3Options(publicKey: $keyPair->publicKey));
        $this->assertEquals(
            "UQBWgLsQ3n-NIM_E0geaCqypB4rNlCHCCJRiv1Sv78vJR8UH",
            $wallet->getAddress()->toString(true, true),
        );
    }
}
