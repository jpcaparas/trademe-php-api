<?php

namespace JPCaparas\TradeMeAPI;

use JPCaparas\TradeMeAPI\Concerns\ValidatesRequired;
use JPCaparas\TradeMeAPI\Exceptions\ClientException;
use JPCaparas\TradeMeAPI\Exceptions\RequestException;

/**
 * @todo More methods in tow
 */
class Client
{
    use ValidatesRequired;

    const SCOPE_READ = 'MyTradeMeRead';
    const SCOPE_WRITE = 'MyTradeMeWrite';

    /**
     * @var Request $request An (optional) pre-configured request object
     */
    private $request;

    public function __construct(array $requestOptions = [], ?Request $request = null)
    {
        $this->request = $request ?? new Request($requestOptions);
    }

    /**
     * Sell an item
     *
     * @param array $params
     *
     * @return string
     *
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

        self::validateRequired($requiredKeys, $params, function (array $requiredKeys) {
            $errorMsg = sprintf(
                'In order to sell an item, you must include specify the following: %s.',
                join(', ', $requiredKeys)
            );

            throw new ClientException($errorMsg);
        });

        return $this->api('POST', $uri, $params);
    }

    /**
     * General purpose method for sending API requests.
     *
     * @param $method
     * @param $uri
     * @param $params
     *
     * @return string
     *
     * @throws RequestException
     */
    public function api($method, $uri, $params): string
    {
        return $this->request->api($method, $uri, $params);
    }

    /**
     * Gets temporary access tokens.
     *
     * The access tokens will have to be stored somewhere for future use.
     *
     * @param null|array $scopes Scopes that the token has access to
     *
     * @throws RequestException
     *
     * @return array An array containing both the OAuth token and key
     */
    public function getTemporaryAccessTokens(?array $scopes = null): array
    {
        $scopes = $scopes ?? [self::SCOPE_READ, self::SCOPE_WRITE];

        $uri = sprintf('/RequestToken?scope=%s', join(',', $scopes));

        $response = $this->request->oauth('POST', $uri);

        parse_str($response, $parsed);

        return $parsed;
    }

    /**
     * Generates a URL that will generate a verifier to get the final access tokens.
     *
     * User will have to must authenticate with said URL in order get the verifier code.
     *
     * The verifier code will have to be stored somewhere for future use.
     *
     * From the API docs:
     *
     * The user is asked to sign in (if they haven’t already) and then asked whether to allow or deny your application access.
     * When the user clicks the Allow button, they will be redirected off to the callback address you provided in the first step,
     * with the oauth_token and oauth_verifier as query string parameters.
     *
     * @param string The temporary OAuth access token
     *
     * @see Client::getTemporaryAccessTokens()
     *
     * @return string
     */
    public function getAccessTokenVerifierURL(string $tempAccessToken): string
    {
        return sprintf(
            '%s/Oauth/Authorize?oauth_token=%s',
            'https://secure.' . $this->request->getBaseDomain(),
            urlencode($tempAccessToken)
        );
    }
}
