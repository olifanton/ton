<?php /** @noinspection PhpDefineCanBeReplacedWithConstInspection, PhpUnhandledExceptionInspection, DuplicatedCode*/

declare(strict_types=1);

use Olifanton\Ton\Connect\Connector;
use Olifanton\Ton\Connect\Replies\Device;
use Olifanton\Ton\Connect\Replies\TonAddr;
use Olifanton\Ton\Connect\Request\SendTransactionRequest;
use Olifanton\Ton\Connect\Storages\JsonFilePreconnectStorage;
use Olifanton\Ton\Marshalling\Json\Hydrator;

require dirname(__DIR__) . "/common.php";

global $logger, $httpClient;

if (!file_exists(__DIR__ . "/connected.json") || !file_exists(__DIR__ . "/preconnect.json")) {
    fwrite(STDERR, "Run connect.php for connection");
    exit(1);
}

// Use `PdoPreconnectStorage` or own implementation in production:
/*
$pdo = new \PDO(
    "mysql:dbname=db;host=127.0.0.1",
    "user",
    "pwd",
);
$pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
$storage = new \Olifanton\Ton\Connect\Storages\PdoPreconnectStorage($pdo);
 */

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

if (!$session) {
    throw new \RuntimeException("Data integrity error");
}

$session->setLogger($logger);

if (!Device::hasFeatureArray("SendTransaction", $userSavedData["features"])) {
    fwrite(STDERR, "SendTransaction not supported");
    exit(1);
}

$connector->sendTransaction(
    $session,
    SendTransactionRequest::withTransaction(
        [
            new \Olifanton\Ton\Connect\Request\Message(
                address: "UQDTxDNbjzOjIsaDrHfbIp_n6m2MOhqyypmPv_C368XwyBcT",
                amount: \Olifanton\Interop\Units::toNano("0.01"),
                payload: \Olifanton\Interop\Boc\SnakeString::fromString("Yay! 🚀")->cell(true),
            ),
        ],
        from: $tonAddr->getAddress(),
        network: $tonAddr->getNetwork(),
        validUntil: time() + 360 // 6 minutes
    ),
);
