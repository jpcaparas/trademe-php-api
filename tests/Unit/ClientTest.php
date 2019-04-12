<?php

namespace Tests\Unit;

use JPCaparas\TradeMeAPI\Client;
use JPCaparas\TradeMeAPI\Exceptions\ClientException;
use JPCaparas\TradeMeAPI\Request;
use Prophecy\Argument;
use Tests\TestCase;

/**
 * @coversDefaultClass \JPCaparas\TradeMeAPI\Client
 */
class ClientTest extends TestCase
{
    /**
     * @covers ::sellItem
     */
    public function testSellItemThrowsExceptionWhenIncompleteRequestParamsGiven(): void
    {
        $this->expectException(ClientException::class);

        $request = $this->prophesize(Request::class);

        $client = new Client([], $request->reveal());

        $params = [
            'Category' => 'TestCategory',
            'Title' => 'TestTitle',
            'Description' => ['TestDescriptionLine1'],
            'Duration' => 1,
            'BuyNowPrice' => 99,
            'StartPrice' => 90,
            'PaymentMethods' => [2, 4],
            'Pickup' => 1,
        ];

        $client->sellItem($params);
    }

    /**
     * @covers ::sellItem
     */
    public function testSellItem(): void
    {
        $request = $this->prophesize(Request::class);
        $request->api(
            "POST",
            "Selling.json",
            [
                "Category" => "TestCategory",
                "Title" => "TestTitle",
                "Description" => ["TestDescriptionLine1"],
                "Duration" => 1,
                "BuyNowPrice" => 99,
                "StartPrice" => 90,
                "PaymentMethods" => [2, 4],
                "Pickup" => 1,
                "ShippingOptions" => [["Type" => 1]]
            ]
        )->willReturn('banana');

        $client = new Client([], $request->reveal());

        $params = [
            'Category' => 'TestCategory',
            'Title' => 'TestTitle',
            'Description' => ['TestDescriptionLine1'],
            'Duration' => 1,
            'BuyNowPrice' => 99,
            'StartPrice' => 90,
            'PaymentMethods' => [2, 4],
            'Pickup' => 1,
            'ShippingOptions' => [
                ['Type' => 1],
            ],
        ];

        $client->sellItem($params);
    }

    /**
     * @covers ::api
     */
    public function testApi(): void
    {
        $request = $this->prophesize(Request::class);
        $request->api(
            "GET",
            "SomeURI.json",
            [
                "Category" => "TestCategory",
                "Title" => "TestTitle",
                "Description" => ["TestDescriptionLine1"],
                "Duration" => 1,
                "BuyNowPrice" => 99,
                "StartPrice" => 90,
                "PaymentMethods" => [2, 4],
                "Pickup" => 1,
                "ShippingOptions" => [["Type" => 1]]
            ]
        )->willReturn('banana');

        $client = new Client([], $request->reveal());

        $params = [
            'Category' => 'TestCategory',
            'Title' => 'TestTitle',
            'Description' => ['TestDescriptionLine1'],
            'Duration' => 1,
            'BuyNowPrice' => 99,
            'StartPrice' => 90,
            'PaymentMethods' => [2, 4],
            'Pickup' => 1,
            'ShippingOptions' => [
                ['Type' => 1],
            ],
        ];

        $client->api('GET', 'SomeURI.json', $params);
    }

    /**
     * @covers ::getTemporaryAccessTokens
     */
    public function testGetTemporaryAccessTokens(): void
    {
        $request = $this->prophesize(Request::class);
        $request
            ->oauth('POST', '/RequestToken?scope=MyTradeMeRead,MyTradeMeWrite')
            ->willReturn(
                'oauth_token=foo&oauth_token_secret=bar'
            );

        $client = new Client([], $request->reveal());

        $tokens = $client->getTemporaryAccessTokens();

        $this->assertEquals(
            ['oauth_token' => 'foo', 'oauth_token_secret' => 'bar'],
            $tokens,
            'The access tokens do not match expectations.'
        );
    }

    /**
     * @covers ::getAccessTokenVerifierURL
     */
    public function testGetAccessTokenVerifierURL(): void
    {
        $request = $this->prophesize(Request::class);
        $request->getBaseDomain()->willReturn('trademe.co.nz');

        $client = new Client([], $request->reveal());

        $url = $client->getAccessTokenVerifierURL('foo');

        $this->assertEquals(
            'https://secure.trademe.co.nz/Oauth/Authorize?oauth_token=foo',
            $url,
            'The URL generated is not valid.'
        );
    }

    /**
     * @covers ::getFinalAccessTokens
     */
    public function testGetFinalAccessTokensWillThrowExceptionOnMissingConfig(): void
    {
        $this->expectException(ClientException::class);
        $this->expectExceptionMessage(
            'To get the final access tokens, specify the following: temp_token, temp_token_secret, token_verifier'
        );

        $request = $this->prophesize(Request::class);

        $client = new Client([], $request->reveal());

        $client->getFinalAccessTokens([]);
    }

    /**
     * @covers ::getFinalAccessTokens
     */
    public function testGetFinalAccessTokens(): void
    {
        $request = $this->prophesize(Request::class);

        $request->getOption('consumer_key')->willReturn('foo');
        $request->getOption('consumer_secret')->willReturn('bar');

        $request->oauth(
            'POST',
            "/AccessToken?oauth_verifier=quux",
            [],
            new Argument\Token\ArrayEntryToken(
                'Authorization',
                new Argument\Token\StringContainsToken(
                    'OAuth consumer_key="foo", consumer_secret="bar", token="baz", token_secret="qux", oauth_version="1.0", oauth_signature_method="PLAINTEXT"'
                )
            )
        )->willReturn('oauth_token=foo&oauth_token_secret=bar');

        $client = new Client([], $request->reveal());

        $config = [
            'consumer_key' => 'foo',
            'consumer_secret' => 'bar',
            'temp_token' => 'baz',
            'temp_token_secret' => 'qux',
            'token_verifier' => 'quux'
        ];

        $client->getFinalAccessTokens($config);
    }
}
