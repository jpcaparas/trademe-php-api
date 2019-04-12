<?php

namespace Tests\Unit;

use GuzzleHttp\Client;
use JPCaparas\TradeMeAPI\Exceptions\RequestException;
use JPCaparas\TradeMeAPI\Request;
use Psr\Http\Message\ResponseInterface;
use Tests\TestCase;

/**
 * @coversDefaultClass \JPCaparas\TradeMeAPI\Request
 */
class RequestTest extends TestCase
{
    public function testWillThrowExceptionOnMissingConfig(): void
    {
        $this->expectException(RequestException::class);

        new Request([]);
    }

    /**
     * @covers ::send
     */
    public function testSend(): void
    {
        $response = $this->prophesize(ResponseInterface::class);
        $response->getBody()->willReturn('banana');

        $httpClient = $this->prophesize(Client::class);
        $httpClient->request(
            'GET',
            'https://trademe.co.nz/Endpoint',
            [
                'json' => ['param1' => 'Param 1'],
                'headers' => ['header1' => 'Header 1'],
            ]
        )->willReturn($response);

        $request = new Request($this->getOptionsStub(), $httpClient->reveal());
        $response = $request->send('GET', 'https://trademe.co.nz/Endpoint', ['param1' => 'Param 1'], ['header1' => 'Header 1']);

        $this->assertEquals('banana', $response, 'The response does not match expectations.');
    }

    private static function getOptionsStub(): array
    {
        return [
            'oauth' => [
                'consumer_key' => 'foo',
                'consumer_secret' => 'bar',
                'token' => 'baz',
                'token_secret' => 'qux',
            ]
        ];
    }

    /**
     * @covers ::api
     */
    public function testApi(): void
    {
        $response = $this->prophesize(ResponseInterface::class);
        $response->getBody()->willReturn('banana');

        $httpClient = $this->prophesize(Client::class);
        $httpClient->request(
            'GET',
            'https://api.trademe.co.nz/v1/Listings',
            [
                'json' => ['param1' => 'Param 1'],
                'headers' => ['Content-Type' => 'application/json', 'header1' => 'Header 1'],
                'auth' => 'oauth',
            ]
        )->willReturn($response);

        $request = new Request($this->getOptionsStub(), $httpClient->reveal());
        $request->api('GET', 'Listings', ['param1' => 'Param 1'], ['header1' => 'Header 1']);
    }
}
