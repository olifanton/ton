<?php declare(strict_types=1);

namespace Olifanton\Ton\Connect;

use Olifanton\Ton\Connect\Exceptions\StorageException;
use Olifanton\Ton\Connect\Replies\TonAddr;
use Olifanton\Ton\Connect\Replies\TonProof;
use Olifanton\Ton\Connect\Sse\Event;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

class DefaultConnectionAwaiter implements ConnectionAwaiter, LoggerAwareInterface, SseClientAwareInterface
{
    use LoggerAwareTrait;
    use SseClientAwareTrait;

    public function __construct(
        private readonly string $preconnectedId,
        private readonly TimeoutCancellation $cancellation,
        private readonly string $backgroundAwaiterScript,
        private readonly string $phpCliPath = "/usr/bin/php",
    ) {}

    /**
     * @throws StorageException
     */
    public function run(SessionCollection $sessions, PreconnectStorage $storage): ?ConnectionResult
    {
        /** @var \Generator<Event|null> $iterators */
        $iterators = [];
        $sessionsA = $sessions->getSessions();
        $result = null;
        $connectedSession = null;

        foreach ($sessionsA as $session) {
            if ($this->logger) {
                $session->setLogger($this->logger);
            }

            $iterators[] = $session->pollEvents($this->createSseClient(), $this->cancellation);
        }

        while (!$this->cancellation->isCanceled()) {
            foreach ($iterators as $sessionEventPoll) {
                $sessionEvent = $sessionEventPoll->current();

                if ($sessionEvent) {
                    try {
                        switch ($sessionEvent::class) {
                            case Events\Connect::class:
                                /** @var ?TonProof $proof */
                                $proof = $sessionEvent->getItem(TonProof::class);
                                /** @var ?TonAddr $address */
                                $address = $sessionEvent->getItem(TonAddr::class);

                                if ($proof && $address) {
                                    if ($proof->check($address)) {
                                        $connectedSession = $sessionEvent->session;
                                        $result = new ConnectionResult(
                                            $this->preconnectedId,
                                            $address,
                                            $proof,
                                            $connectedSession,
                                            $sessions->getWalletApp($connectedSession),
                                            $sessionEvent,
                                        );
                                        $this->cancellation->forceCancel();
                                        $this
                                            ->logger
                                            ?->info(sprintf(
                                                "Wallet connected: %s",
                                                $address
                                                    ->getAddress()
                                                    ->toString(true, isBounceable: false),
                                            ));
                                    }
                                }
                                break;

                            // @TODO: Handle other events
                        }
                    } catch (\Throwable $e) {
                        $this
                            ->logger
                            ?->error("Polling error in DefaultConnectionAwaiter: " . $e->getMessage(), [
                                "exception" => $e,
                            ]);
                    }
                }

                if ($sessionEventPoll->valid()) {
                    $sessionEventPoll->next();
                }
            }
        }

        if ($connectedSession) {
            foreach (array_keys($sessionsA) as $key) {
                try {
                    $storage->remove($key);
                } catch (\Throwable $e) {
                    $this
                        ->logger
                        ?->error("Storage error: " . $e->getMessage(), [
                            "exception" => $e,
                        ]);
                }
            }

            $connectedSession->setPreconnectedId($this->preconnectedId);
            $storage->set(
                $storage->getConnectedKey($this->preconnectedId),
                $connectedSession,
            );
        }

        return $result;
    }

    public function runInBackground(): void
    {
        exec(sprintf(
            "%s %s %s %s > /dev/null &",
            $this->phpCliPath,
            $this->backgroundAwaiterScript,
            escapeshellarg($this->preconnectedId),
            self::safeSerialize($this->cancellation),
        ));
    }

    public static function safeSerialize(mixed $object): string
    {
        return escapeshellarg(
            str_replace("\0", "x__NNBB__x", serialize($object)),
        );
    }

    public static function safeUnserialize(string $serializedObject): mixed
    {
        return unserialize(str_replace("x__NNBB__x", "\0", $serializedObject));
    }
}
