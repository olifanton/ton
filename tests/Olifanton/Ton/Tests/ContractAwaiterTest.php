<?php declare(strict_types=1);

namespace Olifanton\Ton\Tests;

use Hamcrest\Core\IsEqual;
use Mockery\MockInterface;
use Olifanton\Interop\Address;
use Olifanton\Ton\AddressState;
use Olifanton\Ton\ContractAwaiter;
use Olifanton\Ton\Exceptions\AwaiterMaxTimeException;
use Olifanton\Ton\Transport;
use PHPUnit\Framework\TestCase;

class ContractAwaiterTest extends TestCase
{
    private Transport|MockInterface $transport;

    protected function setUp(): void
    {
        /** @phpstan-ignore-next-line */
        $this->transport = \Mockery::mock(Transport::class);
    }

    protected function tearDown(): void
    {
        \Mockery::close();
    }

    private function getInstance(): ContractAwaiter
    {
        return new ContractAwaiter($this->transport);
    }

    /**
     * @throws \Throwable
     */
    public function testWaitForActive(): void
    {
        $addr = new Address("Uf8AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAG3R");

        /** @phpstan-ignore-next-line */
        $this
            ->transport
            ->shouldReceive("getState")
            ->with(IsEqual::equalTo($addr))
            ->once()
            ->andReturn(AddressState::UNKNOWN);
        /** @phpstan-ignore-next-line */
        $this
            ->transport
            ->shouldReceive("getState")
            ->with(IsEqual::equalTo($addr))
            ->andReturn(AddressState::ACTIVE);

        $this->getInstance()->waitForActive($addr, 1, 3);
        $this->addToAssertionCount(1);
    }

    /**
     * @throws \Throwable
     */
    public function testWaitForActiveMaxWaitingTimeReached(): void
    {
        $addr = new Address("Uf8AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAG3R");

        /** @phpstan-ignore-next-line */
        $this
            ->transport
            ->shouldReceive("getState")
            ->with(IsEqual::equalTo($addr))
            ->once()
            ->andReturn(AddressState::UNKNOWN);
        /** @phpstan-ignore-next-line */
        $this
            ->transport
            ->shouldReceive("getState")
            ->with(IsEqual::equalTo($addr))
            ->once()
            ->andReturn(AddressState::UNKNOWN);

        $this->expectException(AwaiterMaxTimeException::class);
        $this->expectExceptionMessage("Max wait time reached for address: Uf8AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAG3R");
        $this->getInstance()->waitForActive($addr, 1, 2);
    }
}
