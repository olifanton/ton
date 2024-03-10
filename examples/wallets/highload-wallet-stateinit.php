<?php /** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

use Olifanton\Interop\Address;
use Olifanton\Interop\Bytes;
use Olifanton\Interop\KeyPair;
use Olifanton\Interop\Units;
use Olifanton\Ton\AddressState;
use Olifanton\Ton\Contracts\Wallets\Highload\HighloadV2Options;
use Olifanton\Ton\Contracts\Wallets\Highload\HighloadWalletV2;
use Olifanton\Ton\Contracts\Wallets\Transfer;
use Olifanton\Ton\Contracts\Wallets\TransferOptions;
use Olifanton\Ton\SendMode;

require dirname(__DIR__) . "/common.php";

global $transport, $logger;

// HL wallet instance
$hlWalletKp = KeyPair::fromSecretKey(Bytes::base64ToBytes("   KEY HERE   ")); // <- Paste here your secret key for HL wallet in Base64 format
$hlWallet = new HighloadWalletV2(
    new HighloadV2Options(
        $hlWalletKp->publicKey,
    ),
);

$logger->info("HL wallet address: " . $hlWallet->getAddress()->toString(true));

// Get state of HL wallet contract
$state = $transport->getState($hlWallet->getAddress());

// Transfers list
$extMsg = $hlWallet->createTransferMessage(
    [
        new Transfer(
            dest: new Address("UQAoqXsjSOtWhZo9t0Fiss9BIiV34qHo5eU6mx0SL0zQ5do-"),
            amount: Units::toNano("0.011"),
            sendMode: SendMode::IGNORE_ERRORS->combine(SendMode::PAY_GAS_SEPARATELY),
        ),
        new Transfer(
            dest: new Address("EQCrrSblmeNMAw27AXbchzG6MUja9iac7PHjyK3Xn8EMeqbG"),
            amount: Units::toNano("0.012"),
            sendMode: SendMode::IGNORE_ERRORS->combine(SendMode::PAY_GAS_SEPARATELY),
        ),
    ],
    new TransferOptions(
        seqno: $state === AddressState::UNINITIALIZED ? 0 : 1, // Set `0` for autodeploy
    )
);

$transport->sendMessage($extMsg, $hlWalletKp->secretKey);
