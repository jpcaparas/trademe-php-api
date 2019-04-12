<?php

namespace JPCaparas\TradeMeAPI;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\RequestOptions;
use JPCaparas\TradeMeAPI\Concerns\ValidatesRequired;
use JPCaparas\TradeMeAPI\Exceptions\OAuthException;
use JPCaparas\TradeMeAPI\Exceptions\RequestException;

class Request
{
    use ValidatesRequired;

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

    /**
     * @var array
     */
    private $options;

    public function __construct($options = [], ?HttpClient $httpClient = null)
    {
        // Set options (and throw an error if required ones are missing
        $requiredOptions = ['consumer_key', 'consumer_secret'];

        self::validateRequired($requiredOptions, $options['oauth'] ?? [], function (array $requiredOptions) {
            $errorMsg = sprintf(
                'All requests must include the following OAuth directives: %s.',
                join(', ', $requiredOptions)
            );

            throw new RequestException($errorMsg);
        });

        $this->options = $options;

        $this->debug = $this->getOption('debug', false);
        $this->sandbox = $this->getOption('sandbox', false);

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
     * Gets an option value by its key
     *
     * @param string $key
     * @param mixed $default
     *
     * @return mixed
     */
    public function getOption(string $key, $default = null)
    {
        return $this->options[$key] ?? $default;
    }

    /**
     * Gets all options
     *
     * @return array
     */
    public function getOptions(): array
    {
        return $this->options;
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
            $this->getBaseDomain(),
            self::API_VERSION,
            ltrim($uri, '/')
        );

        $options = [
            'auth' => 'oauth'
        ];

        $headers = array_merge(['Content-Type' => 'application/json'], $headers);

        return $this->send($method, $apiUrl, $parameters, $headers, $options);
    }

    public function getBaseDomain(): string
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

    /**
     * Send a request to the OAuth endpoint
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
    public function oauth(string $method, string $uri, array $parameters = [], array $headers = []): string
    {
        $OAuthUrl = sprintf(
            'https://secure.%s/%s',
            $this->getBaseDomain() . '/Oauth/',
            ltrim($uri, '/'));

        return $this->send($method, $OAuthUrl, $parameters, $headers);
    }

    public function getLastResponse()
    {
        return $this->lastResponse;
    }
}
