<?php declare(strict_types=1);

namespace Olifanton\Ton\Connect;

interface ConnectionAwaiter
{
    public function run(SessionCollection $sessions, PreconnectStorage $storage): ?ConnectionResult;

    public function runInBackground(): void;
}
