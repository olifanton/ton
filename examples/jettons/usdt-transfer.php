<?php /** @noinspection PhpIncludeInspection,PhpUnhandledExceptionInspection,PhpComposerExtensionStubsInspection,PhpDefineCanBeReplacedWithConstInspection */

declare(strict_types=1);

use Olifanton\Interop\Address;
use Olifanton\Interop\Boc\SnakeString;
use Olifanton\Interop\Units;
use Olifanton\Ton\AddressState;
use Olifanton\Ton\Contracts\Jetton\JettonMinter;
use Olifanton\Ton\Contracts\Jetton\JettonWallet;
use Olifanton\Ton\Contracts\Jetton\JettonWalletOptions;
use Olifanton\Ton\Contracts\Jetton\TransferJettonOptions;
use Olifanton\Ton\Contracts\Wallets\Transfer;
use Olifanton\Ton\Contracts\Wallets\TransferOptions;
use Olifanton\Ton\Contracts\Wallets\V5\WalletV5Options;
use Olifanton\Ton\Contracts\Wallets\V5\WalletV5Beta;
use Olifanton\Ton\SendMode;

define("MAIN_NET", true);

require dirname(__DIR__) . "/common.php";

global $kp, $transport, $logger;

$recipient = new Address("PASTE YOUR RECIPIENT WALLET ADDRESS HERE"); // Wallet address of USDt recipient, not a jetton wallet address
$textComment = "(૭ ｡•̀ ᵕ •́｡ )૭";
$usdtAmount = "0.1";

define("USDT_JETTON_MINTER_ADDR", "EQCxE6mUtQJKFnGfaROTKOt1lZbDiiX1kCixRv7Nw2Id_sDs"); // https://tonviewer.com/EQCxE6mUtQJKFnGfaROTKOt1lZbDiiX1kCixRv7Nw2Id_sDs
$wallet = new WalletV5Beta( // W5, why not? :]
    new WalletV5Options(
        publicKey: $kp->publicKey,
    ),
);

$walletAddress = $wallet->getAddress();
$logger->debug(sprintf("Your wallet address: %s", $walletAddress->asWallet()));

$usdtRoot = JettonMinter::fromAddress(
    $transport,
    new Address(USDT_JETTON_MINTER_ADDR),
);
$usdtWalletAddress = $usdtRoot->getJettonWalletAddress($transport, $walletAddress);
$usdtWallet = new JettonWallet(new JettonWalletOptions(
    address: $usdtWalletAddress,
));

$logger->debug(sprintf(
    "Your USDt jetton wallet address: %s",
    $usdtWalletAddress->toString(true, true, true),
));

$state = $transport->getState($usdtWalletAddress);

if ($state !== AddressState::ACTIVE) {
    $logger->error("Your USDt wallet is not initialized, you cannot perform a transfer of funds that do not exist");
    exit(1);
}

$extMessage = $wallet->createTransferMessage([
    new Transfer(
        dest: $usdtWalletAddress,
        amount: Units::toNano("0.1"),
        payload: $usdtWallet->createTransferBody(
            new TransferJettonOptions(
                jettonAmount: Units::toNano($usdtAmount, Units::USDt),
                toAddress: $recipient,
                responseAddress: $walletAddress,
                forwardPayload: SnakeString::fromString($textComment)->cell(true),
                forwardAmount: Units::toNano("0.0000001"),
            ),
        ),
        sendMode: SendMode::IGNORE_ERRORS->combine(SendMode::PAY_GAS_SEPARATELY, SendMode::CARRY_ALL_REMAINING_INCOMING_VALUE),
    )
], new TransferOptions(
    seqno: (int)$wallet->seqno($transport),
));
$transport->sendMessage($extMessage, $kp->secretKey);

$logger->info("Done!");
