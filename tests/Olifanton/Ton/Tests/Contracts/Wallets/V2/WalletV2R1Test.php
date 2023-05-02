<?php declare(strict_types=1);

namespace Olifanton\Ton\Tests\Contracts\Wallets\V2;

use Olifanton\Mnemonic\Exceptions\TonMnemonicException;
use Olifanton\Mnemonic\TonMnemonic;
use Olifanton\Ton\Contracts\Exceptions\ContractException;
use Olifanton\Ton\Contracts\Wallets\Exceptions\WalletException;
use Olifanton\Ton\Contracts\Wallets\V2\WalletV2R1;
use Olifanton\Ton\Contracts\Wallets\WalletOptions;
use Olifanton\Ton\Tests\Stubs\StubWords;
use PHPUnit\Framework\TestCase;

class WalletV2R1Test extends TestCase
{
    public function testGetName(): void
    {
        $this->assertEquals("v2r1", WalletV2R1::getName());
    }

    /**
     * @throws ContractException|WalletException|TonMnemonicException
     */
    public function testAddress(): void
    {
        $keyPair = TonMnemonic::mnemonicToKeyPair(StubWords::WORDS);
        $wallet = new WalletV2R1(new WalletOptions(publicKey: $keyPair->publicKey));
        $this->assertEquals(
            "UQALL_DxP4Rwyb7FcUlLdrbLHreISrxCh5iIOvP53ouNvHBI",
            $wallet->getAddress()->toString(true, true),
        );
    }
}
