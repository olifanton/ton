<?php declare(strict_types=1);

namespace Olifanton\Ton\Contracts\Messages;

use Olifanton\Interop\Address;
use Olifanton\Interop\Boc\Cell;
use Olifanton\Interop\Boc\Exceptions\BitStringException;
use Olifanton\Interop\Units;
use Olifanton\Ton\Contracts\Messages\Exceptions\MessageException;

class ExternalMessage extends Message
{
    /**
     * @throws MessageException
     */
    public function __construct(
        ExternalMessageOptions $options,
        ?MessageData $data = null,
    )
    {
        $src = is_null($options->src) ? Address::NONE : $options->src;
        $dest = is_null($options->dest) ? Address::NONE : $options->dest;
        $importFee = is_null($options->importFee) ? Units::toNano(0) : $options->importFee;

        $body = $data?->body;
        $state = $data?->state;

        $header = new Cell();
        $headerBs = $header->bits;

        try {
            $headerBs
                ->writeBit(1)
                ->writeBit(0)
                ->writeAddress($src)
                ->writeAddress($dest)
                ->writeCoins($importFee);
        // @codeCoverageIgnoreStart
        } catch (BitStringException $e) {
            throw new MessageException($e->getMessage(), $e->getCode(), $e);
        }
        // @codeCoverageIgnoreEnd

        parent::__construct($header, $body, $state);
    }
}
