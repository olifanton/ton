<?php declare(strict_types=1);

namespace Olifanton\Ton\Tests\Stubs;

use Olifanton\Ton\Transports\Toncenter\ToncenterResponseStack;

class PredefinedStack extends ToncenterResponseStack
{
    public function __construct(array $stack)
    {
        parent::__construct();

        foreach ($stack as $entry) {
            $this->push($entry);
        }

        $this->rewind();
    }
}
