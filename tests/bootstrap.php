<?php declare(strict_types=1);

require dirname(__DIR__) . "/vendor/autoload.php";

$dotenv = Dotenv\Dotenv::createMutable(dirname(__DIR__));
$dotenv->load();

define("STUB_DATA_DIR", __DIR__ . "/stub-data");

