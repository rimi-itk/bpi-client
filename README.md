Installation
============

Install dependencies (skipping dev dependencies):

```
composer install --no-dev
```

Usage
------------

```
<?php
include_once __DIR__ . '/vendor/autoload.php';
include_once __DIR__ . '/Bpi/Sdk/Bpi.php';

$bpi = new Bpi(…);
$nodes = $bpi->searchNodes([]);
…
```

Running tests
-------------

Install dev dependencies:

```
composer install
```

Run all unit tests:

```
./vendor/bin/phpunit
```

Run web service tests:

```
BPI_WS_ENDPOINT=http://develop.bpi-web-service.vm BPI_WS_AGENCY_ID=999999 BPI_WS_API_KEY=80f5b4ce83ffb13324dc553665a5b852 BPI_WS_SECRET_KEY=608d9b0fed745573b4d4f868093073830638cbdc ./vendor/bin/phpunit --stop-on-failure Tests/WebService/
```
