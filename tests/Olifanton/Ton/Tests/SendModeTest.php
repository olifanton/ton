<?php declare(strict_types=1);

namespace Olifanton\Ton\Tests;

use Olifanton\Ton\SendMode;
use PHPUnit\Framework\TestCase;

class SendModeTest extends TestCase
{
    public function testCombine(): void
    {
        $this->assertEquals(
            3,
            SendMode::IGNORE_ERRORS->combine(SendMode::PAY_GAS_SEPARATELY),
        );
    }
}
