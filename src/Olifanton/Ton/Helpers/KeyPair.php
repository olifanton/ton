<?php declare(strict_types=1);

namespace Olifanton\Ton\Helpers;

use Olifanton\Interop\Bytes;
use Olifanton\Interop\Crypto;
use Olifanton\TypedArrays\Uint8Array;

final class KeyPair
{
    /**
     * @return \Olifanton\Interop\KeyPair
     * @throws \Olifanton\Interop\Exceptions\CryptoException
     */
    public static function random(): \Olifanton\Interop\KeyPair
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        /** @noinspection PhpComposerExtensionStubsInspection */
        return Crypto::keyPairFromSeed(
            new Uint8Array(Bytes::bytesToArray(random_bytes(SODIUM_CRYPTO_SIGN_SEEDBYTES))),
        );
    }
}
