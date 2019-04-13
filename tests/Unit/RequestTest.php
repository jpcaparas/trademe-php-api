<?php

namespace Tests\Unit;

use GuzzleHttp\Client;
use JPCaparas\TradeMeAPI\Exceptions\RequestException;
use JPCaparas\TradeMeAPI\Request;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Tests\TestCase;

/**
 * @coversDefaultClass \JPCaparas\TradeMeAPI\Request
 */
class RequestTest extends TestCase
{
    public function testWillThrowExceptionOnMissingConfig(): void
    {
        $this->expectException(RequestException::class);
        $this->expectExceptionMessage(
            'All requests must include the following OAuth directives: consumer_key, consumer_secret.'
        );

        new Request([]);
    }

    /**
     * @covers ::send
     */
    public function testSend(): void
    {
        $response = $this->prophesize(ResponseInterface::class);
        $stream = $this->prophesize(StreamInterface::class);

        $stream->getContents()->willReturn('banana');
        $response->getBody()->willReturn($stream->reveal());

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
        $stream = $this->prophesize(StreamInterface::class);

        $stream->getContents()->willReturn('banana');
        $response->getBody()->willReturn($stream->reveal());

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

    /**
     * @covers ::getOption
     */
    public function testGetOption(): void
    {
        $request = new Request(array_merge(['debug' => true, 'sandbox' => false], $this->getOptionsStub()));

        // Test flat options (e.g. debug
        $isDebug = $request->getOption('debug');
        $isSandbox = $request->getOption('sandbox');

        $this->assertTrue($isDebug);
        $this->assertFalse($isSandbox);

        // Test multidimensional options (e.g. oauth.consumer_key)
        $allOauth = $request->getOption('oauth');
        $this->assertEquals([
            'consumer_key' => 'foo',
            'consumer_secret' => 'bar',
            'token' => 'baz',
            'token_secret' => 'qux'

        ], $allOauth);

        $consumerKey = $request->getOption('oauth.consumer_key');
        $consumerSecret = $request->getOption('oauth.consumer_secret');
        $consumerBanana = $request->getOption('oauth.consumer_banana', 'banana'); // This option does not exist

        $this->assertEquals('foo', $consumerKey);
        $this->assertEquals('bar', $consumerSecret);
        $this->assertEquals('banana', $consumerBanana);
    }
}
