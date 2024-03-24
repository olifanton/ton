<?php /** @noinspection PhpDefineCanBeReplacedWithConstInspection, PhpUnhandledExceptionInspection*/

declare(strict_types=1);

/**
 *
 * This is an example of a script that runs in the background and waits for a customer wallet application to connect.
 *
 * You MUST implement the initialization of your application (framework) here and handle the connection result.
 *
 */

use Http\Client\Common\HttpMethodsClient;
use Http\Discovery\Psr17FactoryDiscovery;
use Http\Discovery\Psr18ClientDiscovery;
use Olifanton\Ton\Connect\Connector;
use Olifanton\Ton\Connect\DefaultConnectionAwaiter;
use Olifanton\Ton\Connect\Storages\JsonFilePreconnectStorage;

require dirname(__DIR__, 2) . "/vendor/autoload.php";

$preconnectedId = $argv[1];
$cancellation = $argv[2];

$awaiter = new DefaultConnectionAwaiter(
    $preconnectedId,
    DefaultConnectionAwaiter::safeUnserialize($cancellation),
    "none",
);
// $awaiter->setLogger($logger);

$storage = new JsonFilePreconnectStorage(__DIR__ . "/preconnect.json");
$connector = new Connector(
    $storage,
    new HttpMethodsClient(
        Psr18ClientDiscovery::find(),
        Psr17FactoryDiscovery::findRequestFactory(),
        Psr17FactoryDiscovery::findStreamFactory(),
    ),
);
$sessions = $connector->ensureSessions($preconnectedId, null);

/** @var \Olifanton\Ton\Connect\ConnectionResult|null $result */
$result = $awaiter->run($sessions, $storage);

// Save connection data in your database.
// You MUST store $preconnectedId in conjunction with your user ID in order to be able to perform fund transfer requests from your backend.
// Also, serialized session should also remain saved in PreconnectStorage (this is implemented by default in DefaultConnectionAwaiter).

file_put_contents(__DIR__ . "/connected.json", json_encode([
    "preconnected_id" => $result->preconnectedId,
    "ton_addr" => $result->tonAddr,
    "wallet" => $result->walletApplication->appName,
    "features" => $result->connectEvent->getDevice()->features,
])); // <- This is just example. You should implement user data saving based on your application requirements, but you will definitely need to save the ton_addr object for sending requests.
