### Toncenter transport initialization

An HTTP client implementation is required to use the SDK via Toncenter API. For this example, [Guzzle](https://docs.guzzlephp.org/en/stable/), a PHP HTTP client will be used. If you are using another HTTP client supplied by your framework, refer to your framework's documentation and the [`httplug` documentation](https://docs.php-http.org/en/latest/index.html) for additional information..

1. Install http components using [Composer](https://getcomposer.org/doc/00-intro.md):
```bash
composer require guzzlehttp/guzzle http-interop/http-factory-guzzle php-http/guzzle7-adapter
```

1. Setup Toncenter transport:
```php
<?php

use Http\Client\Common\HttpMethodsClient;
use Http\Discovery\HttpClientDiscovery;
use Http\Discovery\Psr17FactoryDiscovery;
use Olifanton\Ton\Transports\Toncenter\ToncenterHttpV2Client;
use Olifanton\Ton\Transports\Toncenter\ClientOptions;
use Olifanton\Ton\Transports\Toncenter\ToncenterTransport;

$isMainnet = false;
$toncenterApiKey = "..."; // Request API key from https://t.me/tontestnetapibot or https://t.me/tonapibot

// HTTP client initialization
$httpClient = new HttpMethodsClient(
    HttpClientDiscovery::find(),
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
See [`examples/common.php`](./examples/common.php) for a complex Toncenter example.

### SDK components

#### Primitives

To read descriptions of primitives (Address, Cell, Slice, Builder, Hashmap), refer to documentation in the [`olifanton/interop`](https://github.com/olifanton/interop) repository.

### Framework integration

_@WIP_

---

<p align="right">(<a href="README.md">back to README</a>)</p>