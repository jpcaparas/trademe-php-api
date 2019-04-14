# Trade Me PHP API

[![Build Status](https://travis-ci.org/jpcaparas/trademe-php-api.svg?branch=master)](https://travis-ci.org/jpcaparas/trademe-php-api) [![Coverage Status](https://coveralls.io/repos/github/jpcaparas/trademe-php-api/badge.svg?branch=master)](https://coveralls.io/github/jpcaparas/trademe-php-api?branch=master)

An unofficial PHP client to make it easy to interface with Trade Me's [API platform](https://developer.trademe.co.nz/).

## Installation

    composer require jpcaparas/trademe-php-api

## Authorizing

#### Getting temporary access tokens (Step 1)

    $config = [
        'sandbox' => true,
        'oauth' => [
            'consumer_key' => 'foo',
            'consumer_secret' => 'bar',
        ],
    ];
        
    $client = new \JPCaparas\TradeMeAPI\Client($config);
    
    ['oauth_token' => $tempAccessToken, 'oauth_token_secret' => $tempAccessTokenSecret] = $client->getTemporaryAccessTokens();
    
#### Getting the OAuth token verifier to validate temporary access tokens (Step 2)

    $tokenVerifierUrl = $client->getAccessTokenVerifierURL($tempOAuthToken); // Visit this URL and store the verifier code
    
#### Getting the final access tokens (Step 3)

    // The config values are a culmination of steps 1 and 2
    $config = [
        'temp_token' => 'baz',
        'temp_token_secret' => 'qux',
        'token_verifier' => 'quux'
    ];

    ['oauth_token' => $accessToken, 'oauth_token_secret' => $accessTokenSecret] = $client->getFinalAccessTokens($config);
    
## Making API calls

You can make API calls once you've gotten your final access tokens:

#### Selling an item

    $config = [
        'sandbox' => true,
        'oauth' => [
            'consumer_key' => 'foo',
            'consumer_secret' => 'bar',
            'token' => 'baz',
            'token_secret' => 'qux',
            'token_verifier' => 'quu',
        ],
    ];
    
    $client = new Client($config);
    
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

## Tests

    vendor/bin/phpunit
    
## References

- OAuth 1.0 PLAINTEXT workflow [(link)](https://developer.trademe.co.nz/api-overview/example-plaintext-workflow/)
- API reference [(link)](https://developer.trademe.co.nz/api-reference/)

## Badges

_Coming soon..._

