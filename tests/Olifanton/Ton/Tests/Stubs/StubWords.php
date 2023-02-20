<?php declare(strict_types=1);

namespace Olifanton\Ton\Tests\Stubs;

use Olifanton\Interop\KeyPair;
use Olifanton\Mnemonic\TonMnemonic;

final class StubWords
{
    public const WORDS = [
        'bring',  'like',    'escape',
        'health', 'chimney', 'pear',
        'whale',  'peasant', 'drum',
        'beach',  'mass',    'garden',
        'riot',   'alien',   'possible',
        'bus',    'shove',   'unable',
        'jar',    'anxiety', 'click',
        'salon',  'canoe',   'lion',
    ];

    /**
     * @throws \Olifanton\Mnemonic\Exceptions\TonMnemonicException
     */
    public static function getKP(): KeyPair
    {
        return TonMnemonic::mnemonicToKeyPair(self::WORDS);
    }
}
