<?php

namespace JPCaparas\TradeMeAPI;

use JPCaparas\TradeMeAPI\Exceptions\ClientException;
use JPCaparas\TradeMeAPI\Exceptions\RequestException;

/**
 * @todo More methods in tow
 */
class Client
{
    const SCOPE_READ = 'MyTradeMeRead';
    const SCOPE_WRITE = 'MyTradeMeWrite';

    /**
     * @var Request
     */
    private $request;

    public function __construct(?Request $request = null)
    {
        $this->request = $request ?? new Request();
    }

    /**
     * Sell an item
     *
     * @param array $params
     *
     * @return string
     *
     * @throws ClientException
     * @throws RequestException
     */
    public function sellItem(array $params): string
    {
        $uri = 'Selling.json';

        $requiredKeys = [
            'Category',
            'Title',
            'Description',
            'Duration',
            'BuyNowPrice',
            'StartPrice',
            'PaymentMethods',
            'Pickup',
            'ShippingOptions'
        ];

        self::validateParams($requiredKeys, $params);

        return $this->send('POST', $uri, $params);
    }

    /**
     * Validates params against required keys for a request.
     *
     * @param array $requiredKeys
     * @param array $params
     *
     * @throws ClientException
     */
    private static function validateParams(array $requiredKeys, array $params): void
    {
        $paramKeys = array_keys($params);

        $matchCount = count(array_intersect($requiredKeys, $paramKeys));

        if ($matchCount < count($requiredKeys)) {
            throw new ClientException(
                sprintf(
                    'Params required from this request include: %s.',
                    join(', ', $requiredKeys)
                )
            );
        }
    }

    /**
     * General purpose method for sending requests
     *
     * @param $method
     * @param $uri
     * @param $params
     *
     * @return string
     *
     * @throws RequestException
     */
    public function send($method, $uri, $params): string
    {
        return $this->request->api($method, $uri, $params);
    }
}
