<?php declare(strict_types=1);

namespace Olifanton\Ton\Contracts\Wallets;

use Olifanton\Interop\Boc\Cell;
use Olifanton\Interop\Boc\Exceptions\BitStringException;
use Olifanton\Ton\Contracts\AbstractContract;
use Olifanton\Ton\Contracts\Exceptions\ContractException;
use Olifanton\Ton\Contracts\Messages\Exceptions\MessageException;
use Olifanton\Ton\Contracts\Messages\ExternalMessage;
use Olifanton\Ton\Contracts\Messages\ExternalMessageOptions;
use Olifanton\Ton\Contracts\Messages\InternalMessage;
use Olifanton\Ton\Contracts\Messages\InternalMessageOptions;
use Olifanton\Ton\Contracts\Messages\MessageData;
use Olifanton\Ton\Contracts\Wallets\Exceptions\WalletException;
use Olifanton\Ton\Exceptions\TransportException;
use Olifanton\Ton\Transport;

abstract class AbstractWallet extends AbstractContract implements Wallet
{
    /**
     * @throws WalletException
     */
    public function seqno(Transport $transport): ?int
    {
        $seqno = null;

        try {
            $stack = $transport->runGetMethod(
                $this,
                "seqno",
            );

            if ($stack->count() > 0) {
                $seqno = (int)$stack
                    ->currentBigInteger()
                    ?->toBase(10);
            }
        } catch (TransportException $e) {
            if ($e->getCode() === 13 || $e->getCode() === -13 || $e->getCode() === -14) {
                // Out of gas error?
                return null;
            }

            throw new WalletException(
                "Seqno fetching error: " . $e->getMessage(),
                $e->getCode(),
                $e,
            );
        }

        return $seqno;
    }

    public function createTransferMessage(TransferMessageOptions $options): ExternalMessage
    {
        $signingMessage = $this->createSigningMessage($options->seqno);

        try {
            $signingMessage->bits->writeUint8($options->sendMode);
            $body = is_string($options->payload)
                ? $this->createTxtPayload($options->payload)
                : $options->payload;

            $internalMessage = new InternalMessage(
                new InternalMessageOptions(
                    bounce: false,
                    dest: $options->dest,
                    value: $options->amount,
                    src: $this->getAddress(),
                ),
                new MessageData(
                    $body,
                )
            );

            $signingMessage->refs[] = $internalMessage->cell();

            return new ExternalMessage(
                new ExternalMessageOptions(
                    null,
                    $this->getAddress(),
                ),
                new MessageData(
                    $signingMessage,
                    $options->stateInit
                        ? $options->stateInit->cell()
                        : ($options->seqno === 0 ? $this->getStateInit()->cell() : null),
                )
            );
        } catch (BitStringException | MessageException | ContractException $e) {
            throw new WalletException(
                $e->getMessage(),
                $e->getCode(),
                $e,
            );
        }
    }

    protected function createData(): Cell
    {
        try {
            $cell = new Cell();
            $cell->bits->writeUint(0, 32); // seqno
            $cell->bits->writeBytes($this->getPublicKey());

            return $cell;
        // @codeCoverageIgnoreStart
        } catch (BitStringException $e) {
            throw new WalletException("Wallet data creation error: " . $e->getMessage(), $e->getCode(), $e);
        }
        // @codeCoverageIgnoreEnd
    }

    /**
     * @throws BitStringException
     */
    protected function createSigningMessage(int $seqno): Cell
    {
        $cell = new Cell();
        $cell
            ->bits
            ->writeUint($seqno, 21);

        return $cell;
    }

    /**
     * @throws BitStringException
     */
    private function createTxtPayload(string $textMessage): Cell
    {
        $payload = new Cell();

        if (strlen($textMessage) > 0) {
            $payload
                ->bits
                ->writeUint(0, 32)
                ->writeString($textMessage);
        }

        return $payload;
    }
}
