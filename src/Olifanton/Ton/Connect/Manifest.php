<?php declare(strict_types=1);

namespace Olifanton\Ton\Connect;

class Manifest implements \JsonSerializable
{
    public function __construct(
        public readonly string $url,
        public readonly string $name,
        public readonly string $iconUrl,
        public readonly ?string $termsOfUseUrl = null,
        public readonly ?string $privacyPolicyUrl = null,
    ) {}

    public function jsonSerialize(): array
    {
        $result = [
            "url" => $this->url,
            "name" => $this->name,
            "iconUrl" => $this->iconUrl,
        ];

        if ($this->termsOfUseUrl) {
            $result["termsOfUseUrl"] = $this->termsOfUseUrl;
        }

        if ($this->privacyPolicyUrl) {
            $result["privacyPolicyUrl"] = $this->privacyPolicyUrl;
        }

        return $result;
    }
}
