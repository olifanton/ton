<?php declare(strict_types=1);

namespace Olifanton\Ton\Tests\Contracts\Wallets\Simple;

use Olifanton\Mnemonic\Exceptions\TonMnemonicException;
use Olifanton\Mnemonic\TonMnemonic;
use Olifanton\Ton\Contracts\ContractOptions;
use Olifanton\Ton\Contracts\Exceptions\ContractException;
use Olifanton\Ton\Contracts\Wallets\Exceptions\WalletException;
use Olifanton\Ton\Contracts\Wallets\Simple\SimpleWalletR1;
use Olifanton\Ton\Tests\Stubs\StubWords;
use PHPUnit\Framework\TestCase;

class SimpleWalletR1Test extends TestCase
{
    public function testGetName(): void
    {
        $this->assertEquals("simpleR1", SimpleWalletR1::getName());
    }

    /**
     * @throws ContractException|WalletException|TonMnemonicException
     */
    public function testAddress(): void
    {
        $keyPair = TonMnemonic::mnemonicToKeyPair(StubWords::WORDS);
        $wallet = new SimpleWalletR1(new ContractOptions(publicKey: $keyPair->publicKey));
        $this->assertEquals(
            "UQBEPqGRDlRwvt9dg-Q1q_dlHXndljNcZgUqp0pKTJffv117",
            $wallet->getAddress()->toString(true, true),
        );
    }
}
