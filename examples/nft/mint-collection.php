<?php /** @noinspection PhpIncludeInspection,PhpUnhandledExceptionInspection,PhpComposerExtensionStubsInspection */

declare(strict_types=1);

use Olifanton\Ton\ContractAwaiter;

require dirname(__DIR__) . "/common.php";

global $kp, $transport, $logger;

$awaiter = new ContractAwaiter($transport);
$awaiter->setLogger($logger);

// @TODO
