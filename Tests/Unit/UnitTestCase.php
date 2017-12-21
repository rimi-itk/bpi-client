<?php

namespace Bpi\Sdk\Tests\Unit;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

class UnitTestCase extends TestCase
{
    protected $client;

    protected function getClient($fixturePath)
    {
        $status = -1;
        $headers = [];
        $body = '';

        $content = file_get_contents($fixturePath);
        $lines = explode(PHP_EOL, $content);

        foreach ($lines as $index => $line) {
            if (preg_match('@HTTP/[0-9.]* (?<code>[0-9]+) .+@', $line, $matches)) {
                $status = (int)$matches['code'];
            } elseif (preg_match('/(?<name>[^:]+):\s*(?<value>.+)/', $line, $matches)) {
                $headers[$matches['name']] = $matches['value'];
            } elseif (!trim($line)) {
                // Skip blank lines.
            } else {
                $body = implode(PHP_EOL, array_slice($lines, $index));
                break;
            }
        }

        $mock = new MockHandler([
            new Response($status, $headers, $body),
        ]);
        $handler = HandlerStack::create($mock);

        return new Client(['handler' => $handler]);
    }
}
