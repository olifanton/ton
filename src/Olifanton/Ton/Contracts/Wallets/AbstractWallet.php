<?php declare(strict_types=1);

namespace Olifanton\Ton\Contracts\Wallets;

use Olifanton\Interop\Boc\Builder;
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
use Olifanton\Ton\SendMode;
use Olifanton\Ton\Transport;
use Olifanton\TypedArrays\Uint8Array;

abstract class AbstractWallet extends AbstractContract implements Wallet
{
    protected Uint8Array $publicKey;

    protected int $wc;

    public function __construct(WalletOptions $walletOptions)
    {
        $this->publicKey = $walletOptions->publicKey;
        $this->wc = $walletOptions->workchain;

        parent::__construct($walletOptions);
    }

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

    public function createTransferMessage(array $transfers,
                                          ?TransferOptions $options = null): ExternalMessage
    {
        if (empty($transfers)) {
            throw new WalletException("At least one transfer is required");
        }

        if (count($transfers) > 4) {
            throw new WalletException("Sending no more than 4 transfers is possible");
        }

        $options = $options ?? new TransferOptions();
        $seqno = $options->seqno;

        if ($seqno === null) {
            throw new WalletException("Seqno is required");
        }

        $signingMessage = $this->createSigningMessage($seqno);
        $sendMode = $options->sendMode;

        try {
            $signingMessage
                ->bits
                ->writeUint8(
                    $sendMode instanceof SendMode
                        ? $sendMode->value
                        : $sendMode,
                );
        } catch (BitStringException $e) {
            throw new WalletException($e->getMessage(), $e->getCode(), $e);
        }

        foreach ($transfers as $transfer) {
            try {
                $body = is_string($transfer->payload)
                    ? $this->createTxtPayload($transfer->payload)
                    : $transfer->payload;
                $internalMessage = new InternalMessage(
                    new InternalMessageOptions(
                        bounce: $transfer->bounce,
                        dest: $transfer->dest,
                        value: $transfer->amount,
                        src: $this->getAddress(),
                    ),
                    new MessageData(
                        $body,
                    )
                );
                $signingMessage->refs[] = $internalMessage->cell();
            } catch (BitStringException|MessageException|ContractException $e) {
                throw new WalletException(
                    $e->getMessage(),
                    $e->getCode(),
                    $e,
                );
            }
        }

        try {
            return new ExternalMessage(
                new ExternalMessageOptions(
                    null,
                    $this->getAddress(),
                ),
                new MessageData(
                    $signingMessage,
                    $seqno === 0 ? $this->getStateInit()->cell() : null,
                )
            );
        } catch (MessageException|ContractException $e) {
            throw new WalletException(
                $e->getMessage(),
                $e->getCode(),
                $e,
            );
        }
    }

    public function createSigningMessage(int $seqno): Cell
    {
        $cell = new Cell();

        try {
            $cell
                ->bits
                ->writeUint($seqno, 32);
        } catch (BitStringException $e) {
            throw new WalletException($e->getMessage(), $e->getCode(), $e);
        }

        return $cell;
    }

    public function getPublicKey(): Uint8Array
    {
        return $this->publicKey;
    }

    protected function createData(): Cell
    {
        try {
            $cell = new Cell();
            $cell
                ->bits
                ->writeUint(0, 32) // seqno
                ->writeBytes($this->getPublicKey());

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
    protected function createTxtPayload(string $textMessage): Cell
    {
        $payload = new Builder();

        if (strlen($textMessage) > 0) {
            $payload
                ->writeUint(0, 32)
                ->writeString($textMessage);
        }

        return $payload->cell();
    }
}
