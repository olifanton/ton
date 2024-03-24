<?php /** @noinspection PhpDefineCanBeReplacedWithConstInspection, PhpUnhandledExceptionInspection, DuplicatedCode*/

declare(strict_types=1);

use Olifanton\Interop\Address;
use Olifanton\Ton\Connect\Connector;
use Olifanton\Ton\Connect\Replies\TonAddr;
use Olifanton\Ton\Connect\Request\SendTransactionRequest;
use Olifanton\Ton\Connect\Storages\JsonFilePreconnectStorage;
use Olifanton\Ton\Contracts\Jetton\JettonMinter;
use Olifanton\Ton\Contracts\Jetton\JettonWallet;
use Olifanton\Ton\Contracts\Jetton\JettonWalletOptions;
use Olifanton\Ton\Contracts\Jetton\TransferJettonOptions;
use Olifanton\Ton\Marshalling\Json\Hydrator;

/**
 *
 * Attention! Mainnet example!
 *
 * This is an example of Jetton's (Glint Coin) transfer request through TonConnect.
 *
 */

define("MAIN_NET", true);

require dirname(__DIR__) . "/common.php";

global $logger, $httpClient, $transport;

if (!file_exists(__DIR__ . "/connected.json") || !file_exists(__DIR__ . "/preconnect.json")) {
    fwrite(STDERR, "Run `connect.php` for connection");
    exit(1);
}

$storage = new JsonFilePreconnectStorage(__DIR__ . "/preconnect.json");
/** @var array{
 *     preconnected_id: string,
 *     ton_addr: array,
 *     wallet: string,
 *     features: array,
 * } $userSavedData
 */
$userSavedData = json_decode(
    file_get_contents(__DIR__ . "/connected.json"),
    true,
    flags: JSON_THROW_ON_ERROR,
);

$connector = new Connector($storage, $httpClient);
$connector->setLogger($logger);
$session = $storage->get(
    $storage->getConnectedKey($userSavedData["preconnected_id"]),
);
$tonAddr = Hydrator::extract(TonAddr::class, $userSavedData["ton_addr"]);

$glintRoot = JettonMinter::fromAddress(
    $transport,
    new Address("EQCBdxpECfEPH2wUxi1a6QiOkSf-5qDjUWqLCUuKtD-GLINT"),
);
$glintWalletAddress = $glintRoot->getJettonWalletAddress($transport, $tonAddr->getAddress());
$glintWallet = new JettonWallet(new JettonWalletOptions(
    address: $glintWalletAddress,
));

$connector->sendTransaction(
    $session,
    SendTransactionRequest::withTransaction(
        [
            new \Olifanton\Ton\Connect\Request\Message(
                address: $glintWalletAddress,
                amount: \Olifanton\Interop\Units::toNano("0.1"),
                payload: $glintWallet->createTransferBody(new TransferJettonOptions(
                    jettonAmount: \Olifanton\Interop\Units::toNano("0.01"),
                    toAddress: new Address("UQDTxDNbjzOjIsaDrHfbIp_n6m2MOhqyypmPv_C368XwyBcT"),
                    responseAddress: $tonAddr->getAddress(),
                )),
            ),
        ],
        from: $tonAddr->getAddress(),
        network: $tonAddr->getNetwork(),
        validUntil: time() + 360 // 6 minutes
    ),
);
