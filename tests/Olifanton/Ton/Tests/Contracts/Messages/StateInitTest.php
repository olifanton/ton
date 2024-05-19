<?php declare(strict_types=1);

namespace Olifanton\Ton\Tests\Contracts\Messages;

use Olifanton\Ton\Contracts\Messages\StateInit;
use PHPUnit\Framework\TestCase;

class StateInitTest extends TestCase
{
    /**
     * @throws \Throwable
     */
    public function testFromBase64(): void
    {
        $s = trim(file_get_contents(STUB_DATA_DIR . "/connect/state-init.txt"));
        $instance = StateInit::fromBase64($s);
        $this->assertNotNull($instance->code);
        $this->assertNotNull($instance->data);
    }
}
