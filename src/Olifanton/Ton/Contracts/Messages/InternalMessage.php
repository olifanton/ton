<?php declare(strict_types=1);

namespace Olifanton\Ton\Contracts\Messages;

use Brick\Math\BigInteger;
use Olifanton\Interop\Boc\Cell;
use Olifanton\Interop\Boc\Exceptions\BitStringException;
use Olifanton\Interop\Units;
use Olifanton\Ton\Contracts\Messages\Exceptions\MessageException;

class InternalMessage extends Message
{
    /**
     * @throws MessageException
     */
    public function __construct(InternalMessageOptions $options, ?MessageData $data = null)
    {
        $ihrDisabled = is_null($options->ihrDisabled) ? true : $options->ihrDisabled;
        $bounce = $options->bounce;
        $bounced = is_null($options->bounced) ? false : $options->bounced;
        $src = $options->src;
        $dest = $options->dest;
        $value = $options->value;
        $ihrFee = is_null($options->ihrFee) ? Units::toNano(0) : $options->ihrFee;
        $fwdFee = is_null($options->fwdFee) ? Units::toNano(0) : $options->fwdFee;
        $createdLt = BigInteger::of($options->createdLt ?? "0");
        $createdAt = BigInteger::of($options->createdAt ?? "0");

        $body = $data?->body;
        $state = $data?->state;

        $header = new Cell();
        $headerBs = $header->bits;

        try {
            $headerBs
                ->writeBit(0)
                ->writeInt($ihrDisabled ? -1 : 0, 1)
                ->writeInt($bounce ? -1 : 0, 1)
                ->writeInt($bounced ? -1 : 0, 1)
                ->writeAddress($src)
                ->writeAddress($dest)
                ->writeCoins($value)
                ->writeBit(0)
                ->writeCoins($ihrFee)
                ->writeCoins($fwdFee)
                ->writeUint($createdLt, 64)
                ->writeUint($createdAt, 32);
        } catch (BitStringException $e) {
            throw new MessageException($e->getMessage(), $e->getCode(), $e);
        }

        parent::__construct($header, $body, $state);
    }
}
