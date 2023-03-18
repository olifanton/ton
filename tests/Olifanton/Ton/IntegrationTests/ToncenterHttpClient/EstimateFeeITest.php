<?php declare(strict_types=1);

namespace Olifanton\Ton\IntegrationTests\ToncenterHttpClient;

use Olifanton\Interop\Address;
use Olifanton\Interop\Bytes;
use Olifanton\Interop\Crypto;
use Olifanton\Interop\Units;
use Olifanton\Ton\Contracts\Wallets\TransferMessageOptions;
use Olifanton\Ton\Contracts\Wallets\V3\WalletV3Options;
use Olifanton\Ton\Contracts\Wallets\V3\WalletV3R1;
use Olifanton\Ton\SendMode;
use Olifanton\TypedArrays\Uint8Array;

class EstimateFeeITest extends ToncenterHttpClientITestCase
{
    /**
     * @throws \Throwable
     */
    public function testSuccess(): void
    {
        /** @noinspection PhpComposerExtensionStubsInspection */
        $kp = Crypto::keyPairFromSeed(new Uint8Array(Bytes::bytesToArray(random_bytes(SODIUM_CRYPTO_SIGN_SEEDBYTES))));
        $wallet = new WalletV3R1(
            new WalletV3Options(
                $kp->publicKey,
            )
        );
        $transfer = $wallet->createTransferMessage(
            new TransferMessageOptions(
                dest: new Address("EQBYivdc0GAk-nnczaMnYNuSjpeXu2nJS3DZ4KqLjosX5sVC"),
                amount: Units::toNano("0.01"),
                seqno: 1,
                payload: "Hello world!",
                sendMode: SendMode::IGNORE_ERRORS->combine(SendMode::PAY_GAS_SEPARATELY)
            )
        );

        $client = $this->getInstance();
        $queryFees = $client
            ->estimateFee(
                $wallet->getAddress()->toString(true, true, true),
                Bytes::bytesToBase64($transfer->sign($kp->secretKey)->toBoc(has_idx: false)),
            );

        $this->assertGreaterThan(0.001, $queryFees->sourceFees->sum()->toFloat());
    }
}
