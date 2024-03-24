<?php declare(strict_types=1);

namespace Olifanton\Ton\Connect\Replies;

use Olifanton\Interop\Address;
use Olifanton\Interop\Bytes;
use Olifanton\Ton\Marshalling\Attributes\JsonMap;

class TonProof extends Reply
{
    #[JsonMap("timestamp")]
    public readonly int $timestamp;

    /** @var array{lengthBytes: int, value: string} */
    #[JsonMap("domain")]
    public readonly array $domain;

    #[JsonMap("signature")]
    public readonly string $signature;

    #[JsonMap("payload")]
    public readonly string $payload;

    /**
     * @throws \SodiumException
     */
    public function check(TonAddr $tonAddr): bool
    {
        return self::manualCheck([
            "domain" => $this->domain,
            "timestamp" => $this->timestamp,
            "payload" => $this->payload,
            "signature" => $this->signature,
        ], $tonAddr->getAddress(), $tonAddr->publicKey);
    }

    /**
     * @throws \SodiumException
     */
    public static function manualCheck(array $proofData, Address $address, string $publicKey): bool
    {
        $msg = "ton-proof-item-v2/";
        $msg .= pack("V", $address->getWorkchain());
        $msg .= Bytes::arrayToBytes($address->getHashPart());
        $msg .= pack("V", $proofData["domain"]["lengthBytes"]);
        $msg .= $proofData["domain"]["value"];
        $msg .= pack("P", $proofData["timestamp"]);
        $msg .= $proofData["payload"];

        $msgHash = hash("sha256", $msg, true);
        $signatureMessage = "\xFF\xFF" . utf8_encode("ton-connect") . $msgHash;
        $hash = hash("sha256", $signatureMessage, true);

        return sodium_crypto_sign_verify_detached(
            base64_decode($proofData["signature"]),
            $hash,
            sodium_hex2bin($publicKey),
        );
    }

    public function getName(): string
    {
        return "ton_proof";
    }
}
