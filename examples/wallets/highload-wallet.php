<?php /** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

use Olifanton\Interop\Address;
use Olifanton\Interop\Units;
use Olifanton\Ton\ContractAwaiter;
use Olifanton\Ton\Contracts\Wallets\Highload\HighloadV2Options;
use Olifanton\Ton\Contracts\Wallets\Highload\HighloadWalletV2;
use Olifanton\Ton\Contracts\Wallets\Transfer;
use Olifanton\Ton\Contracts\Wallets\V3\WalletV3Options;
use Olifanton\Ton\Contracts\Wallets\V3\WalletV3R1;
use Olifanton\Ton\Deployer;
use Olifanton\Ton\DeployOptions;

require dirname(__DIR__) . "/common.php";

global $kp, $transport, $logger;

$awaiter = new ContractAwaiter($transport);
$awaiter->setLogger($logger);

// HL wallet instance
$hlWalletKp = \Olifanton\Ton\Helpers\KeyPair::random();
$hlWallet = new HighloadWalletV2(
    new HighloadV2Options(
        $hlWalletKp->publicKey,
    ),
);

// Deploy new HL wallet
$deployWallet = new WalletV3R1(
    new WalletV3Options(
        $kp->publicKey,
    )
);
$deployer = new Deployer($transport);
$deployer->setLogger($logger);
$deployer->deploy(
    new DeployOptions(
        $deployWallet,
        $kp->secretKey,
        Units::toNano(0.5),
    ),
    $hlWallet,
);

// Wait contract deploy
$awaiter->waitForActive($hlWallet->getAddress());

// Transfers list
$extMsg = $hlWallet->createTransferMessage(
    [
        new Transfer(
            dest: new Address("UQAoqXsjSOtWhZo9t0Fiss9BIiV34qHo5eU6mx0SL0zQ5do-"),
            amount: Units::toNano("0.011"),
        ),
        new Transfer(
            dest: new Address("EQCrrSblmeNMAw27AXbchzG6MUja9iac7PHjyK3Xn8EMeqbG"),
            amount: Units::toNano("0.012"),
        ),
    ],
);

$transport->sendMessage($extMsg, $hlWalletKp->secretKey);
