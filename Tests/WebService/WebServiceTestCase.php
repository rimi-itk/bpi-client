<?php

namespace Bpi\Sdk\Tests\WebService;

use PHPUnit\Framework\TestCase;

abstract class WebServiceTestCase extends TestCase
{
    /**
     * @var \Bpi
     */
    protected $client;

    protected $agencyId = null;

    public function setUp()
    {
        if (!$this->client) {
            $environment = [
                'BPI_WS_ENDPOINT' => null,
                'BPI_WS_AGENCY_ID' => null,
                'BPI_WS_API_KEY' => null,
                'BPI_WS_SECRET_KEY' => null,
            ];
            foreach ($environment as $name => &$value) {
                $value = getenv($name);
                if (empty($value)) {
                    throw new \Exception('Environment variable ' . $name . ' is not defined');
                }
            }

            $this->agencyId = $environment['BPI_WS_AGENCY_ID'];

            $this->client = new \Bpi($environment['BPI_WS_ENDPOINT'], $environment['BPI_WS_AGENCY_ID'], $environment['BPI_WS_API_KEY'], $environment['BPI_WS_SECRET_KEY']);
        }
    }

    protected function getRandomName()
    {
        return uniqid(__METHOD__);
    }
}
