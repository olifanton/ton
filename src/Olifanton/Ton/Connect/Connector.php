<?php declare(strict_types=1);

namespace Olifanton\Ton\Connect;

use Http\Client\Common\HttpMethodsClientInterface;
use Olifanton\Ton\Connect\Exceptions\ConnectorException;
use Olifanton\Ton\Connect\Exceptions\SessionException;
use Olifanton\Ton\Connect\Models\BridgeType;
use Olifanton\Ton\Connect\Models\WalletApplication;
use Olifanton\Ton\Connect\Request\ConnectRequest;
use Olifanton\Ton\Connect\Request\SendTransactionRequest;
use Olifanton\Ton\Connect\Sse\Client;
use Olifanton\Ton\Connect\Sse\StreamClient;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

class Connector implements LoggerAwareInterface, SseClientAwareInterface
{
    use LoggerAwareTrait;
    use SseClientAwareTrait;

    public function __construct(
        private readonly PreconnectStorage $preconnectStorage,
        private readonly HttpMethodsClientInterface $httpClient,
    ) {}

    /**
     * @param string $return "none", "back" or specific URL
     * @return array<string, string> Key -- wallet `appName`
     * @throws SessionException
     */
    public function generateUniversalLinks(
        SessionCollection $sessions,
        ConnectRequest $rq,
        string $return = "none",
    ): array
    {
        $result = [];

        foreach ($sessions->getSessions() as $session) {
            $app = $sessions->getWalletApp($session);
            $result[$app->appName] = $session->createUniversalLink(
                $app,
                $rq,
                $return,
            );
        }

        return $result;
    }

    /**
     * @param WalletApplication[]|null $applications
     * @throws ConnectorException
     */
    public function ensureSessions(string $preconnectedId, ?array $applications): SessionCollection
    {
        try {
            if (!$applications) {
                $applications = WalletApplicationsManager::getDefaultApps();
            }

            $sseApplications = [];

            foreach ($applications as $application) {
                if ($application->hasBridge(BridgeType::SSE)) {
                    $sseApplications[] = $application;
                }
            }

            if (empty($sseApplications)) {
                throw new \InvalidArgumentException("No wallet applications found that support SSE bridge");
            }

            $sessions = [];
            /** @var array<string, WalletApplication> $appMap */
            $appMap = [];

            foreach ($sseApplications as $application) {
                $storageKey = $this->getStorageKey($preconnectedId, $application);

                if ($session = $this->preconnectStorage->get($storageKey)) {
                    $sessions[$storageKey] = $session;
                    $appMap[$storageKey] = $application;
                    continue;
                }

                $sessions[$storageKey] = $this->generateSession($application);
                $appMap[$storageKey] = $application;
                $this->preconnectStorage->set(
                    $storageKey,
                    $sessions[$storageKey],
                );
            }

            return new SessionCollection(
                $sessions,
                $appMap,
            );
        } catch (\Throwable $e) {
            throw new ConnectorException(
                $e->getMessage(),
                $e->getCode(),
                $e,
            );
        }
    }

    /**
     * @throws ConnectorException
     * @throws \Throwable
     */
    public function sendTransaction(Session $session, SendTransactionRequest $rq, int $ttl = 300): void
    {
        if (!$session->getPreconnectedId() || !$session->getClientId() || !$session->getId()) {
            throw new ConnectorException("Invalid session state");
        }

        try {
            $session->sendMessage(
                $this->httpClient,
                $rq,
                "sendTransaction",
                $ttl,
            );
        } catch (SessionException $e) {
            $this
                ->logger
                ?->error("Transaction error: " . $e->getMessage(), [
                    "exception" => $e,
                    "preconnected_id" => $session->getPreconnectedId(),
                    "request" => $rq->jsonSerialize(),
                ]);
            throw new ConnectorException($e->getMessage(), $e->getCode(), $e);
        }

        $this->preconnectStorage->set(
            $this->preconnectStorage->getConnectedKey($session->getPreconnectedId()),
            $session,
        );
    }

    protected function getStorageKey(string $preconnectedId, WalletApplication $application): string
    {
        return sprintf(
            "olfnt_pconn_%s_%s",
            $preconnectedId,
            $application->appName,
        );
    }

    protected function ensureSseClient(): Client
    {
        if ($this->sseClient) {
            return $this->sseClient;
        }

        $this->sseClient = new StreamClient();

        if ($this->logger) {
            $this->sseClient->setLogger($this->logger);
        }

        return $this->sseClient;
    }

    /**
     * @throws \SodiumException
     */
    protected final function generateSession(WalletApplication $application): Session
    {
        $kp = SessionIdGenerator::getKeyPair();

        return new Session(
            sodium_crypto_box_publickey($kp),
            sodium_crypto_box_secretkey($kp),
            $application->getBridge(BridgeType::SSE)->url,
        );
    }
}
