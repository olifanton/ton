<?php /** @noinspection PhpDefineCanBeReplacedWithConstInspection, PhpUnhandledExceptionInspection*/

declare(strict_types=1);

use Olifanton\Ton\Connect\ConnectItem;
use Olifanton\Ton\Connect\Connector;
use Olifanton\Ton\Connect\DefaultConnectionAwaiter;
use Olifanton\Ton\Connect\Request\ConnectRequest;
use Olifanton\Ton\Connect\TimeoutCancellation;
use Olifanton\Ton\Connect\WalletApplicationsManager;

require dirname(__DIR__) . "/common.php";

global $logger, $httpClient;

define("PRECONNECT_STORAGE_FILE", __DIR__ . "/preconnect.json");
define("CONNECTED_INFO_FILE", __DIR__ . "/connected.json");

if (file_exists(PRECONNECT_STORAGE_FILE) && file_exists(CONNECTED_INFO_FILE)) {
    fwrite(
        STDERR,
        "Your wallet already connected, run `send-transaction.php` example or delete json files in directory " . __DIR__ . PHP_EOL,
    );
    exit(1);
}

// Generating a unique connection identifier. A unique identifier must be generated for each user.
// This identifier will be used to determine the wallet connection,
// which the application will receive asynchronously.
// You can store this identifier in your database

$preconnectedId = "test_id_123456";
// In prodcution mode use unique identifier, like this: $preconnectedId = base64_encode(random_bytes(16));

$proofData = json_encode([
    "p_id" => $preconnectedId,

    // You can pass any additional data that you want to pass in load to the proof object
    // "user_id" => 1,
]);

// Get a predefined list of client applications that support SSE bridge.
$walletsApps = WalletApplicationsManager::getDefaultApps();

// Creating a wallet connection request object
$connectionRequest = new ConnectRequest(
    "https://raw.githubusercontent.com/olifanton/olifanton.github.io/main/tonconnect-manifest.json",
    ConnectItem::tonProof($proofData),
);

// The storage is required to store bridge connection session data. Storage must be persistent.
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

$storage = new \Olifanton\Ton\Connect\Storages\JsonFilePreconnectStorage(PRECONNECT_STORAGE_FILE);
$connector = new Connector(
    $storage,
    $httpClient,
);

// Getting new sessions for $preconnectedId or fetching from storage
$sessions = $connector->ensureSessions($preconnectedId, $walletsApps);

// Getting a list of wallet connection links. You should pass these links to your frontend to display to the user
$links = $connector->generateUniversalLinks($sessions, $connectionRequest, "none");

foreach ($links as $appName => $link) {
    echo $appName, ": ", $link, PHP_EOL;
}

$awaiter = new DefaultConnectionAwaiter(
    $preconnectedId,
    new TimeoutCancellation(10 * 60), // Wait 10 minutes for connection
    __DIR__ . "/background.php",
);
$awaiter->setLogger($logger);

// Starts waiting for connection result in blocking mode.
// The script will be paused and blocked until the client application
// connection is made or the timeout time is reached. You can use this method in development mode.
$result = $awaiter->run($sessions, $storage);

// In production mode, you must run result waits in the background:
// $awaiter->runInBackground();
// Then the calling code (self script) will stop executing, but the background script will run. Check out the `background.php` example.
// Depending on your needs, you can implement your own SSE bridges polling in the background using queues or PHP with non-blocking libraries such as ReactPHP.

if ($result) {
    $logger->info("Success!");
    $logger->info(
        "Customer wallet address: " . $result->tonAddr->getAddress()->toString(true, isBounceable: false),
    );
    $logger->info("Customer wallet app: " . $result->walletApplication->name);

    // Save connection data in your database.
    // You MUST store $preconnectedId in conjunction with your user ID in order to be able to perform fund transfer requests from your backend.

    file_put_contents(CONNECTED_INFO_FILE, json_encode([
        "preconnected_id" => $result->preconnectedId,
        "ton_addr" => $result->tonAddr,
        "wallet" => $result->walletApplication->appName,
        "features" => $result->connectEvent->getDevice()->features,
    ]));

    echo "Run `send-transaction.php` example for make transaction request", PHP_EOL;
} else {
    $logger->warning("Aborted by timeout");
}
