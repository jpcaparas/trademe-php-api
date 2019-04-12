<?php

namespace Tests\Unit;

use JPCaparas\TradeMeAPI\Client;
use JPCaparas\TradeMeAPI\Exceptions\ClientException;
use JPCaparas\TradeMeAPI\Request;
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
}
