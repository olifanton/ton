<?php declare(strict_types=1);

namespace Olifanton\Ton;

use Olifanton\Boc\Cell;
use Olifanton\Utils\Address;

interface Contract
{
    public static function getName(): string;

    public function getCode(): Cell;

    public function getData(): Cell;

    public function getAddress(): Address;
}
