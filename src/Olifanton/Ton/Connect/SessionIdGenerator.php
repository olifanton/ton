<?php declare(strict_types=1);

namespace Olifanton\Ton\Connect;

final class SessionIdGenerator
{
    private static ?string $mockedValue = null;

    public function mock(string $keypair): void
    {
        self::$mockedValue = $keypair;
    }

    public static function clearMock(): void
    {
        self::$mockedValue = null;
    }

    /**
     * @throws \SodiumException
     */
    public static function getKeyPair(): string
    {
        if (self::$mockedValue !== null) {
            return self::$mockedValue;
        }

        return sodium_crypto_box_keypair();
    }
}
