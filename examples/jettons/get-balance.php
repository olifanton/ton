<?php /** @noinspection PhpIncludeInspection,PhpUnhandledExceptionInspection,PhpComposerExtensionStubsInspection,PhpDefineCanBeReplacedWithConstInspection */

declare(strict_types=1);

use Olifanton\Interop\Address;
use Olifanton\Interop\Units;
use Olifanton\Ton\Contracts\Jetton\JettonMinter;
use Olifanton\Ton\Contracts\Jetton\JettonWallet;
use Olifanton\Ton\Contracts\Jetton\JettonWalletOptions;

define("MAIN_NET", true);

require dirname(__DIR__) . "/common.php";

global $transport;

$targetWalletAddrStr = "UQD4uGNdB4a3f52mYOZf0x1nCmdd1DAvrLppL0a1cetTYCQx";

define("NOT_JETTON_MINTER_ADDR", "EQAvlWFDxGF2lXm67y4yzC17wYKD9A0guwPkMs1gOsM__NOT");

$notRoot = JettonMinter::fromAddress(
    $transport,
    new Address(NOT_JETTON_MINTER_ADDR),
);
$notWalletAddress = $notRoot->getJettonWalletAddress($transport, new Address($targetWalletAddrStr));
$notWallet = new JettonWallet(
    new JettonWalletOptions(
        address: $notWalletAddress,
    ),
);
$data = $notWallet->getWalletData($transport);

var_dump(Units::fromNano($data->balance) . ' NOT');
