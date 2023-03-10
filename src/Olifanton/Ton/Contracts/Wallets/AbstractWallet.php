<?php declare(strict_types=1);

namespace Olifanton\Ton\Contracts\Wallets;

use Olifanton\Interop\Boc\Cell;
use Olifanton\Interop\Boc\Exceptions\BitStringException;
use Olifanton\Interop\Boc\Exceptions\CellException;
use Olifanton\Ton\Contracts\AbstractContract;
use Olifanton\Ton\Contracts\Exceptions\ContractException;
use Olifanton\Ton\Contracts\Messages\Exceptions\MessageException;
use Olifanton\Ton\Contracts\Messages\ExternalMessage;
use Olifanton\Ton\Contracts\Messages\ExternalMessageOptions;
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

            if ($stack->count() === 0) {
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

    public function createDeployMessage(ExternalMessageOptions $options): ExternalMessage
    {
        try {
            $stateInit = $this->getStateInit();
            $body = $this->createSigningMessage();

            return new ExternalMessage(
                $options,
                new MessageData(
                    $body,
                    $stateInit->cell(),
                )
            );
        } catch (ContractException | BitStringException | MessageException $e) {
            throw new WalletException(
                "Message creation error: " . $e->getMessage(),
                $e->getCode(),
                $e,
            );
        }
    }

    /**
     * @throws BitStringException
     */
    public function createSigningMessage(int $seqno = 0): Cell
    {
        $message = new Cell();
        $message
            ->bits
            ->writeUint($seqno, 32);

        return $message;
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
     * @throws ContractException
     */
    protected function deserializeCode(string $serializedBoc): Cell
    {
        try {
            return Cell::oneFromBoc($serializedBoc);
        // @codeCoverageIgnoreStart
        } catch (CellException $e) {
            throw new WalletException("Wallet code creation error: " . $e->getMessage(), $e->getCode(), $e);
        }
        // @codeCoverageIgnoreEnd
    }
}
