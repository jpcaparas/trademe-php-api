<?php

namespace Tests\Unit;


use JPCaparas\TradeMeAPI\Exceptions\OAuthException;
use JPCaparas\TradeMeAPI\OAuth1;
use Psr\Http\Message\RequestInterface;
use Tests\TestCase;

/**
 * @coversDefaultClass \JPCaparas\TradeMeAPI\OAuth1
 */
class Oauth1Test extends TestCase
{
    public function testWillThrowAnExceptionOnMissingConfig(): void
    {
        $this->expectException(OAuthException::class);

        new OAuth1([]);
    }

    /**
     * @covers ::getSignature
     */
    public function testGetSignature(): void
    {
        $config = [
            'consumer_secret' => 'foo',
            'token_secret' => 'bar',
        ];

        $subscriber = new OAuth1($config);

        $request = $this->prophet->prophesize(RequestInterface::class);

        $signature = $subscriber->getSignature($request->reveal(), []);

        $this->assertEquals('foo&bar', $signature, 'The signature produced does not match expectation.');
    }
}
