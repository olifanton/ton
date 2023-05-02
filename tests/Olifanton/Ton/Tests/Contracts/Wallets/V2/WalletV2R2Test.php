<?php declare(strict_types=1);

namespace Olifanton\Ton\Tests\Contracts\Wallets\V2;

use Olifanton\Mnemonic\Exceptions\TonMnemonicException;
use Olifanton\Mnemonic\TonMnemonic;
use Olifanton\Ton\Contracts\Exceptions\ContractException;
use Olifanton\Ton\Contracts\Wallets\Exceptions\WalletException;
use Olifanton\Ton\Contracts\Wallets\V2\WalletV2R2;
use Olifanton\Ton\Contracts\Wallets\WalletOptions;
use Olifanton\Ton\Tests\Stubs\StubWords;
use PHPUnit\Framework\TestCase;

class WalletV2R2Test extends TestCase
{
    public function testGetName(): void
    {
        $this->assertEquals("v2r2", WalletV2R2::getName());
    }

    /**
     * @throws ContractException|WalletException|TonMnemonicException
     */
    public function testAddress(): void
    {
        $keyPair = TonMnemonic::mnemonicToKeyPair(StubWords::WORDS);
        $wallet = new WalletV2R2(new WalletOptions(publicKey: $keyPair->publicKey));
        $this->assertEquals(
            "UQDzflWrxac6IorbltYkMQpYpegiHfJEoblHU0C8_7eBEP8I",
            $wallet->getAddress()->toString(true, true),
        );
    }
}
