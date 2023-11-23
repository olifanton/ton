<?php declare(strict_types=1);

namespace Olifanton\Ton\Transports\Toncenter;

use Brick\Math\BigNumber;
use Olifanton\Interop\Address;
use Olifanton\Interop\Boc\Cell;
use Olifanton\Interop\Boc\Exceptions\CellException;
use Olifanton\Ton\AddressState;
use Olifanton\Ton\Contract;
use Olifanton\Ton\Contracts\Exceptions\ContractException;
use Olifanton\Ton\Contracts\Messages\Exceptions\MessageException;
use Olifanton\Ton\Contracts\Messages\Exceptions\ResponseStackParsingException;
use Olifanton\Ton\Contracts\Messages\ExternalMessage;
use Olifanton\Ton\Contracts\Messages\ResponseStack;
use Olifanton\Ton\Exceptions\TransportException;
use Olifanton\Ton\Transport;
use Olifanton\Ton\Transports\Toncenter\Exceptions as TncEx;
use Olifanton\TypedArrays\Uint8Array;

class ToncenterTransport implements Transport
{
    public function __construct(
        private readonly ToncenterV2Client $client,
    ) {}

    /**
     * @inheritDoc
     */
    public function runGetMethod(Contract|Address $contract, string $method, array $stack = []): ResponseStack
    {
        try {
            $address = $contract instanceof Contract ? $contract->getAddress() : $contract;
        } catch (ContractException $e) {
            throw new TransportException(
                "Contract address error: " . $e->getMessage(),
                0,
                $e,
            );
        }

        try {
            $sStack = ToncenterStackSerializer::serialize($stack);
        } catch (\Throwable $e) {
            throw new TransportException(
                "Stack serialization error: " . $e->getMessage(),
                $e->getCode(),
                $e,
            );
        }

        try {
            $response = $this
                ->client
                ->runGetMethod(
                    $address,
                    $method,
                    $sStack,
                );

            if (!in_array($response->exitCode, [0, 1], true)) {
                throw new TransportException(
                    "Non-zero exit code, code: " . $response->exitCode,
                    $response->exitCode,
                );
            }

            return ToncenterResponseStack::parse($response->stack);
        } catch (TncEx\ClientException | TncEx\TimeoutException | TncEx\ValidationException $e) {
            throw new TransportException(
                sprintf(
                    "Get method error: %s; address: %s, method: %s",
                    $e->getMessage(),
                    $address->toString(true),
                    $method,
                ),
                0,
                $e,
            );
        } catch (ResponseStackParsingException $e) {
            throw new TransportException(
                sprintf(
                    "Stack parsing error: %s; address: %s, method: %s",
                    $e->getMessage(),
                    $address->toString(true),
                    $method,
                ),
                0,
                $e,
            );
        }
    }

    /**
     * @inheritDoc
     */
    public function send(Uint8Array | string | Cell $boc): void
    {
        try {
            $this
                ->client
                ->sendBoc(
                    $boc,
                );
        } catch (TncEx\ClientException | TncEx\TimeoutException | TncEx\ValidationException $e) {
            throw new TransportException(
                sprintf(
                    "Sending error: %s",
                    $e->getMessage(),
                ),
                0,
                $e,
            );
        }
    }

    /**
     * @inheritDoc
     */
    public function sendMessage(ExternalMessage $message, Uint8Array $secretKey): void
    {
        try {
            $this->send($message->sign($secretKey)->toBoc(false));
        } catch (CellException | MessageException $e) {
            throw new TransportException(
                sprintf(
                    "Message sending error: %s",
                    $e->getMessage(),
                ),
                0,
                $e,
            );
        }
    }

    /**
     * @inheritDoc
     */
    public function estimateFee(Address $address,
                                Cell | Uint8Array | string $body,
                                Cell | Uint8Array | string | null $initCode = null,
                                Cell | Uint8Array | string | null $initData = null): BigNumber
    {
        try {
            return $this
                ->client
                ->estimateFee(
                    $address->toString(true, true, false),
                    $body,
                    $initCode,
                    $initData,
                )
                ->sourceFees
                ->sum();
        } catch (TncEx\ClientException | TncEx\TimeoutException | TncEx\ValidationException$e) {
            throw new TransportException(
                $e->getMessage(),
                0,
                $e,
            );
        }
    }

    /**
     * @inheritDoc
     */
    public function getConfigParam(int $configParamId): Cell
    {
        try {
            $answer = $this->client->getConfigParam($configParamId);

            if ($answer->type !== "tvm.cell") {
                throw new TransportException();
            }

            return Cell::oneFromBoc($answer->bytes, isBase64: true);
        } catch (TncEx\ClientException | TncEx\TimeoutException | TncEx\ValidationException | CellException $e) {
            throw new TransportException(
                $e->getMessage(),
                0,
                $e,
            );
        }
    }

    /**
     * @throws TransportException
     */
    public function getState(Address $address): AddressState
    {
        try {
            return $this->client->getAddressState($address);
        } catch (TncEx\ClientException | TncEx\TimeoutException | TncEx\ValidationException $e) {
            throw new TransportException(
                $e->getMessage(),
                0,
                $e,
            );
        }
    }
}
