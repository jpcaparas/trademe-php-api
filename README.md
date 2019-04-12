# Trade Me PHP API

An unofficial PHP client to make it easy to interface with Trade Me's [API platform](https://developer.trademe.co.nz/).

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
    
    ['oauth_token' => $oauthToken, 'oauth_secret' => $oauthSecret] = $client->getTemporaryAccessTokens();
    
#### Getting the OAuth token verifier to validate temporary access tokens (Step 2)

_Adding..._
    
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