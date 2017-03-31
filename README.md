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

## Unit tests

Run unit tests:

```
./vendor/bin/phpunit Tests/Unit/
```

## Web service tests

These tests must be run against an actual BPI web service (see https://github.com/inleadmedia/rest-api).

First, load fixtures in the BPI web service:

```
app/console doctrine:mongodb:fixtures:load
```

When running the web service tests, the web service endpoint and user credentials (api key and secret) must be specified using environment variables. Adapt the example to match your actual setup.

Run web service tests:

```
BPI_WS_ENDPOINT=http://master.bpi-web-service.vm BPI_WS_AGENCY_ID=200100 BPI_WS_API_KEY=98c645c7e2882e7431037caa75ca5134 BPI_WS_SECRET_KEY=90eb05e4fbc327d3f455fb7576c493d3872fca7f ./vendor/bin/phpunit --stop-on-failure Tests/WebService/
```
