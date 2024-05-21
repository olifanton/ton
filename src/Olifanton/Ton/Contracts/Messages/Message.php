<?php declare(strict_types=1);

namespace Olifanton\Ton\Contracts\Messages;

use Olifanton\Interop\Boc\Cell;
use Olifanton\Interop\Boc\Exceptions\BitStringException;
use Olifanton\Interop\Boc\Exceptions\CellException;
use Olifanton\Interop\Crypto;
use Olifanton\Interop\Exceptions\CryptoException;
use Olifanton\Ton\Contracts\Messages\Exceptions\MessageException;
use Olifanton\TypedArrays\Uint8Array;

abstract class Message
{
    protected bool $tailSigned = false;

    public function __construct(
        private readonly Cell $header,
        private readonly ?Cell $body = null,
        private readonly ?Cell $state = null,
    ) {}

    public function tailSigned(bool $isTailSigned): self
    {
        $this->tailSigned = $isTailSigned;

        return $this;
    }

    /**
     * @throws MessageException
     */
    public function sign(Uint8Array $key): Cell
    {
        return $this->parse($key);
    }

    /**
     * @throws MessageException
     */
    public function cell(): Cell
    {
        return $this->parse();
    }

    /**
     * @throws MessageException
     */
    protected function parse(?Uint8Array $key = null): Cell
    {
        try {
            $message = new Cell();
            $bs = $message->bits;
            $body = $this->body && $key ? self::signed($this->body, $key, $this->tailSigned) : $this->body;
            $message->writeCell($this->header);

            if ($this->state) {
                $bs->writeBit(1);

                if (self::hasTargetFreeBits($message, $this->state)) {
                    $bs->writeBit(0);
                    $message->writeCell($this->state);
                } else {
                    $bs->writeBit(1);
                    $message->refs[] = $this->state;
                }
            } else {
                $bs->writeBit(0);
            }

            if ($body) {
                if (self::hasTargetFreeBits($message, $body)) {
                    $bs->writeBit(0);
                    $message->writeCell($body);
                } else {
                    $bs->writeBit(1);
                    $message->refs[] = $body;
                }
            } else {
                $bs->writeBit(0);
            }

            return $message;
        // @codeCoverageIgnoreStart
        } catch (CellException | BitStringException $e) {
            throw new MessageException(
                "Message parsing error: " . $e->getMessage(),
                $e->getCode(),
                $e,
            );
        }
        // @codeCoverageIgnoreEnd
    }

    /**
     * @throws MessageException
     */
    protected static function signed(Cell $data, Uint8Array $key, bool $isSignTail): Cell
    {
        try {
            $hash = $data->hash();
            $signature = Crypto::sign($hash, $key);
            $message = new Cell();

            if ($isSignTail) {
                $message->writeCell($data);
                $message
                    ->bits
                    ->writeBytes($signature);
            } else {
                $message
                    ->bits
                    ->writeBytes($signature);
                $message->writeCell($data);
            }

            return $message;
        // @codeCoverageIgnoreStart
        } catch (CellException | CryptoException | BitStringException $e) {
            throw new MessageException(
                "Message signing error: " . $e->getMessage(),
                $e->getCode(),
                $e,
            );
        }
        // @codeCoverageIgnoreEnd
    }

    private static function hasTargetFreeBits(Cell $target, Cell $other): bool
    {
        return $target->bits->getFreeBits() >= ($other->bits->getUsedBits() + 1)
            && (count($target->refs) + count($other->refs)) <= 4;
    }
}
