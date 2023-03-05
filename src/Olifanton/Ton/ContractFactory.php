<?php declare(strict_types=1);

namespace Olifanton\Ton;

use Olifanton\Ton\Contracts\TransportAwareInterface;
use Olifanton\TypedArrays\Uint8Array;

// FIXME: Remove this factory
class ContractFactory
{
    public function __construct(
        private readonly Transport $transport
    )
    {
    }

    /**
     * @template T of Contract
     * @param class-string<T> $contractClass
     * @return T
     */
    public function get(string $contractClass, Uint8Array $publicKey, int $workchain = 0): Contract
    {
        $implements = class_implements($contractClass);

        if (!$implements || !in_array(Contract::class, $implements)) {
            throw new \InvalidArgumentException("Invalid contract class: " . $contractClass);
        }

        /** @var T $contract */
        $contract = call_user_func([$contractClass, "create"], $publicKey, $workchain);

        if ($contract instanceof TransportAwareInterface) {
            $contract->setTransport($this->transport);
        }

        return $contract;
    }
}
