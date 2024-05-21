<?php declare(strict_types=1);

namespace Olifanton\Ton\Tests\Contracts\Wallets\V5;

use Olifanton\Interop\Address;
use Olifanton\Interop\Units;
use Olifanton\Mnemonic\TonMnemonic;
use Olifanton\Ton\Contracts\Wallets\Transfer;
use Olifanton\Ton\Contracts\Wallets\V5\WalletV5Options;
use Olifanton\Ton\Contracts\Wallets\V5\WalletV5R1;
use Olifanton\Ton\Contracts\Wallets\V5\WalletV5TransferOptions;
use Olifanton\Ton\Helpers\KeyPair;
use Olifanton\Ton\SendMode;
use Olifanton\Ton\Transports\NullTransport\NullTransport;
use PHPUnit\Framework\TestCase;

class WalletV5R1Test extends TestCase
{
    /**
     * @throws \Throwable
     */
    public function testGetAddress(): void
    {
        $expected = "UQBcVzogh28qC8fAHz5gWjQ-BodVaNNqcMH-Zoi7lJGFPDOV";
        $words = "point august abandon hamster pilot cousin rug bargain century rate rule armed host rate balcony ordinary advance forward unknown border tip art luxury return";
        $kp = TonMnemonic::mnemonicToKeyPair(explode(" ", $words));

        $instance = new WalletV5R1(
            new WalletV5Options(
                publicKey: $kp->publicKey,
            ),
        );

        $this->assertEquals(
            $expected,
            $instance->getAddress()->toString(true, true, false),
        );
    }

    /**
     * @throws \Throwable
     */
    public function testCreateTransferMessage(): void
    {
        $kp = KeyPair::random();
        $instance = new WalletV5R1(
            new WalletV5Options(
                publicKey: $kp->publicKey,
            ),
        );

        $transfers = [];

        for ($i = 0; $i < 255; $i++) {
            $transfers[] = new Transfer(
                dest: new Address("UQAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAJKZ"),
                amount: Units::toNano(0.01),
                sendMode: SendMode::IGNORE_ERRORS->combine(SendMode::PAY_GAS_SEPARATELY),
            );
        }

        $extMessage = $instance
            ->createTransferMessage(
                $transfers,
                new WalletV5TransferOptions(
                    seqno: 0,
                ),
            );

        (new NullTransport())->sendMessage($extMessage, $kp->secretKey);
        $this->addToAssertionCount(1);
    }
}
