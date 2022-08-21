<?php declare(strict_types=1);

namespace Olifanton\Ton\Tests\Contracts\Wallets\V2;

use Olifanton\Mnemonic\TonMnemonic;
use Olifanton\Ton\Contracts\Wallets\V2\WalletV2R1;
use Olifanton\Ton\Tests\Stubs\StubWords;
use PHPUnit\Framework\TestCase;

class WalletV2R1Test extends TestCase
{
    public function testGetName(): void
    {
        $this->assertEquals("v2r1", WalletV2R1::getName());
    }

    /**
     * @throws \Olifanton\Boc\Exceptions\BitStringException
     * @throws \Olifanton\Boc\Exceptions\CellException
     * @throws \Olifanton\Mnemonic\Exceptions\TonMnemonicException
     */
    public function testAddress(): void
    {
        $keyPair = TonMnemonic::mnemonicToKeyPair(StubWords::WORDS);
        $wallet = new WalletV2R1($keyPair->publicKey, 0);
        $this->assertEquals(
            "UQALL_DxP4Rwyb7FcUlLdrbLHreISrxCh5iIOvP53ouNvHBI",
            $wallet->getAddress()->toString(true, true),
        );
    }
}
