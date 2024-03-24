<?php declare(strict_types=1);

namespace Olifanton\Ton\Connect;

use Olifanton\Ton\Connect\Exceptions\SessionException;
use Olifanton\Ton\Connect\Models\WalletApplication;

class SessionCollection
{
    /**
     * @param array<string, Session> $sessions
     * @param array<string, WalletApplication> $appMap
     */
    public function __construct(
        private readonly array $sessions,
        private readonly array $appMap,
    ) {}

    /**
     * @return array<string, Session>
     */
    public function getSessions(): array
    {
        return $this->sessions;
    }

    /**
     * @throws SessionException
     */
    public function getWalletApp(Session $session): WalletApplication
    {
        $storageKey = array_search($session, $this->sessions);

        if ($storageKey === false) {
            throw new SessionException("Data integrity error");
        }

        return $this->appMap[$storageKey];
    }
}
