<?php

namespace JPCaparas\TradeMeAPI;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\RequestOptions;
use JPCaparas\TradeMeAPI\Exceptions\OAuthException;
use JPCaparas\TradeMeAPI\Exceptions\RequestException;

class Request
{
    private const BASE_DOMAIN_PRODUCTION = 'trademe.co.nz';
    private const BASE_DOMAIN_SANDBOX = 'tmsandbox.co.nz';

    private const API_VERSION = 'v1';

    /**
     * @var string
     */
    private $lastResponse;

    /**
     * @var bool
     */
    private $debug = false;

    /**
     * @var bool
     */
    private $sandbox = false;

    /**
     * @var HttpClient
     */
    private $httpClient;

    public function __construct($options = [], ?HttpClient $httpClient = null)
    {
        $this->debug = $options['debug'] ?? false;
        $this->sandbox = $options['sandbox'] ?? false;

        $stack = HandlerStack::create();

        // Handle OAuth requests seamlessly
        try {
            $oauthMiddleware = new OAuth1([
                'signature_method' => OAuth1::SIGNATURE_METHOD_PLAINTEXT,
                'consumer_key' => $options['oauth']['consumer_key'] ?? '',
                'consumer_secret' => $options['oauth']['consumer_secret'] ?? '',
                'token' => $options['oauth']['token'] ?? '',
                'token_secret' => $options['oauth']['token_secret'] ?? '',
            ]);
        } catch (OAuthException $e) {
            throw new RequestException($e->getMessage());
        }

        $stack->push($oauthMiddleware);

        // Set the client for making HTTP requests
        $this->httpClient = $httpClient ?? new HttpClient([
                'handler' => $stack,
                'debug' => $this->debug
            ]);
    }

    /**
     * Send a request to the API endpoint
     *
     * @param string $method
     * @param string $uri
     * @param array $parameters
     * @param array $headers
     *
     * @return string
     *
     * @throws RequestException
     */
    public function api(string $method, string $uri, array $parameters = [], array $headers = []): string
    {
        $apiUrl = sprintf(
            'https://api.%s/%s/%s',
            $this->getBaseUrl(),
            self::API_VERSION,
            ltrim($uri, '/')
        );

        $options = [
            'auth' => 'oauth'
        ];

        $headers = array_merge(['Content-Type' => 'application/json'], $headers);

        return $this->send($method, $apiUrl, $parameters, $headers, $options);
    }

    private function getBaseUrl(): string
    {
        return $this->sandbox ? self::BASE_DOMAIN_SANDBOX : self::BASE_DOMAIN_PRODUCTION;
    }

    /**
     * Send a request
     *
     * @param string $method
     * @param string $url
     * @param array $parameters
     * @param array $headers
     * @param array $options
     *
     * @return Response
     *
     * @throws RequestException
     */
    public function send(
        string $method,
        string $url,
        array $parameters = [],
        array $headers = [],
        array $options = []
    ): string
    {
        $defaultOptions = [
            RequestOptions::JSON => $parameters,
            'headers' => $headers,
        ];

        $options = array_merge($defaultOptions, $options);

        try {
            $response = $this->httpClient->request($method, $url, $options);
        } catch (GuzzleException $e) {
            throw new RequestException($e->getMessage());
        }

        $this->lastResponse = (string)$response->getBody();

        return $this->lastResponse;
    }

    public function getLastResponse()
    {
        return $this->lastResponse;
    }
}
