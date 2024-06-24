PHP SDK for "The Open Network" blockchain
---

![Tests](https://github.com/olifanton/ton/actions/workflows/tests.yml/badge.svg)
[![Latest Stable Version](https://poser.pugx.org/olifanton/ton/v/stable)](https://packagist.org/packages/olifanton/ton)
[![Total Downloads](https://poser.pugx.org/olifanton/ton/downloads)](https://packagist.org/packages/olifanton/ton)
![Based on TON](https://img.shields.io/badge/Based%20on-TON-blue)
[![Donation](https://img.shields.io/badge/Donate-Support-%230098ea?style=flat&logo=ton&logoColor=white)](https://github.com/olifanton#donation)

---
[💬 En chat](https://t.me/olifanton_en) | [💬 Ру чат](https://t.me/olifanton_ru)

## Prerequisites

- Minimum PHP 8.1;
- `ext-hash`;
- `ext-sodium` required as default cryptographic implementation;
- any httplug-compatible HTTP client (`php-http/client-common`), see [Documentation](https://docs.php-http.org/en/latest/clients.html);
- `ext-bcmath` not required, but strongly recommended for performance reasons.

## Installation

```bash
composer require olifanton/ton
```

## Examples

See [`examples`](./examples) directory.

### Running examples

1. Clone repository and install with development dependencies;
2. Get own testnet API key for Toncenter from [Telegram bot](https://t.me/tontestnetapibot);
3. Copy `.env.dist` to `.env`;
4. Put API key and seed phrase variables to `.env` file;
5. Run examples in console.

## Documentation

### Toncenter transport initialization

To use the SDK via Toncenter API, an HTTP client implementation is required. For the example, Guzzle will be used. If you are using another HTTP client supplied by your framework, refer to your framework's documentation and the [`httplug` documentation](https://docs.php-http.org/en/latest/index.html) for additional information.

1. Install http components via Composer:
```bash
composer require guzzlehttp/guzzle http-interop/http-factory-guzzle php-http/guzzle7-adapter
```

2. Setup Toncenter transport:
```php
<?php

use Http\Client\Common\HttpMethodsClient;
use Http\Discovery\Psr18ClientDiscovery;
use Http\Discovery\Psr17FactoryDiscovery;
use Olifanton\Ton\Transports\Toncenter\ToncenterHttpV2Client;
use Olifanton\Ton\Transports\Toncenter\ClientOptions;
use Olifanton\Ton\Transports\Toncenter\ToncenterTransport;

$isMainnet = false;
$toncenterApiKey = "..."; // Request API key from https://t.me/tontestnetapibot or https://t.me/tonapibot

// HTTP client initialization
$httpClient = new HttpMethodsClient(
    Psr18ClientDiscovery::find(),
    Psr17FactoryDiscovery::findRequestFactory(),
    Psr17FactoryDiscovery::findStreamFactory(),
);

// Toncenter API client initialization
$toncenter = new ToncenterHttpV2Client(
    $httpClient,
    new ClientOptions(
        $isMainnet ? "https://toncenter.com/api/v2" : "https://testnet.toncenter.com/api/v2",
        $toncenterApiKey,
    ),
);

// Transport initialization
$toncenterTransport = new ToncenterTransport($toncenter);

// ...

// Now you can use Toncenter transport as access point to blockchain
$toncenterTransport->send($someBoc);
```
See [`examples/common.php`](./examples/common.php) for complex Toncenter example.

### SDK components

#### Primitives

To read description of primitives (Address, Cell, Slice, Builder, Hashmap), refer to documentation in the [`olifanton/interop`](https://github.com/olifanton/interop) repository.

### Performance tips

- First of all, use the latest version of PHP, despite the fact that the minimum version is 8.1
- __Install__ the `bcmath` extension for PHP. This dramatically speeds up the work with large integers, which is necessary for interacting with TVM
- __Disable__ `xdebug` (or other debuggers) in your production. BoC serialisation/deserialisation speedup can be up to 5 times with `XDEBUG_MODE=off`

---

## Contributing

Please make sure to read the [Olifanton contribution guide](https://github.com/olifanton/.github/blob/main/profile/CONTRIBUTING.md) before making a pull request.

---

## Tests

```bash
composer run test:unit
```

# License

MIT
