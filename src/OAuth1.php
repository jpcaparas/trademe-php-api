<?php

namespace JPCaparas\TradeMeAPI;


use GuzzleHttp\Subscriber\Oauth\Oauth1 as Oauth1Base;
use JPCaparas\TradeMeAPI\Exceptions\OAuthException;
use Psr\Http\Message\RequestInterface;

/**
 * We are using the OAuth1 subscriber to furnish the needed Authorization headers when sending API requests
 *
 * Out of the box, the vanilla OAuth1 subscriber works, but does not match the required signature required by Trade Me,
 * so we override the method that generates the signature.
 */
class OAuth1 extends Oauth1Base
{
    /**
     * @var string
     */
    private $consumerSecret;

    /** @var string */
    private $tokenSecret;

    /**
     * Oauth1 constructor.
     * @param $config
     *
     * @throws OAuthException
     */
    public function __construct($config)
    {
        parent::__construct($config);

        $this->consumerSecret = $config['consumer_secret'] ?? '';

        $this->tokenSecret = $config['token_secret'] ?? '';

        if (empty($this->consumerSecret) || empty($this->tokenSecret)) {
            throw new OAuthException('Both the consumer secret and token secret are required to send an API request.');
        }
    }

    /**
     * Trade Me's OAuth1 signature is a simple concatenation of the consumer secret and token secret,
     * instead of the usual base64 encoding done by the base OAuth subscriber
     *
     * @param RequestInterface $request
     * @param array $params
     *
     * @return string
     */
    public function getSignature(RequestInterface $request, array $params)
    {
        return $this->consumerSecret . '&' . $this->tokenSecret;
    }
}
