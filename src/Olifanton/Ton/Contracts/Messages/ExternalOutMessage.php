<?php declare(strict_types=1);

namespace Olifanton\Ton\Contracts\Messages;

use Brick\Math\BigInteger;
use Olifanton\Interop\Boc\Cell;
use Olifanton\Interop\Boc\Exceptions\BitStringException;
use Olifanton\Ton\Contracts\Messages\Exceptions\MessageException;

class ExternalOutMessage extends Message
{
    /**
     * @throws MessageException
     */
    public function __construct(ExternalOutMessageOptions $options, ?MessageData $data = null)
    {
        $src = $options->src;
        $dest = $options->dest;
        $createdLt = BigInteger::of($options->createdLt ?? "0");
        $createdAt = BigInteger::of($options->createdAt ?? "0");

        $body = $data?->body;
        $state = $data?->state;

        $header = new Cell();
        $headerBs = $header->bits;

        try {
            $headerBs
                ->writeBit(1)
                ->writeBit(1)
                ->writeAddress($src)
                ->writeAddress($dest)
                ->writeUint($createdLt, 64)
                ->writeUint($createdAt, 32);
        // @codeCoverageIgnoreStart
        } catch (BitStringException $e) {
            throw new MessageException($e->getMessage(), $e->getCode(), $e);
        }
        // @codeCoverageIgnoreEnd

        parent::__construct($header, $body, $state);
    }
}
