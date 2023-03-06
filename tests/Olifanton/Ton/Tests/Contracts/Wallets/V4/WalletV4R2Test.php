<?php declare(strict_types=1);

namespace Olifanton\Ton\Tests\Contracts\Wallets\V4;

use Olifanton\Mnemonic\Exceptions\TonMnemonicException;
use Olifanton\Mnemonic\TonMnemonic;
use Olifanton\Ton\Contracts\Exceptions\ContractException;
use Olifanton\Ton\Contracts\Wallets\Exceptions\WalletException;
use Olifanton\Ton\Contracts\Wallets\V4\WalletV4Options;
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
     * @throws ContractException|WalletException|TonMnemonicException
     */
    public function testAddress(): void
    {
        $keyPair = TonMnemonic::mnemonicToKeyPair(StubWords::WORDS);
        $wallet = new WalletV4R2(new WalletV4Options(publicKey: $keyPair->publicKey));
        $this->assertEquals(
            "UQDH6ELHpOUPfJfDg6ZxO89z7ZyRSI60MkP8CVWdQXMYYV-O",
            $wallet->getAddress()->toString(true, true),
        );
    }
}
