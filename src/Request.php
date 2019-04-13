<?php

namespace JPCaparas\TradeMeAPI;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\RequestOptions;
use JPCaparas\TradeMeAPI\Concerns\ValidatesRequired;
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
        $oauthMiddleware = new OAuth1([
            'signature_method' => OAuth1::SIGNATURE_METHOD_PLAINTEXT,
            'consumer_key' => $this->getOption('oauth.consumer_key'),
            'consumer_secret' => $this->getOption('oauth.consumer_secret'),
            'token' => $this->getOption('oauth.token'),
            'token_secret' => $this->getOption('oauth.token_secret'),
        ]);

        $stack->push($oauthMiddleware);

        // Set the client for making HTTP requests
        $this->httpClient = $httpClient ?? new HttpClient([
                'handler' => $stack,
                'debug' => $this->debug
            ]);
    }

    /**
     * Gets an option value by its key. Supports dot notation (e.g. oauth.consumer_key)
     *
     * @param string $key
     * @param mixed $default
     *
     * @return mixed
     */
    public function getOption(string $key, $default = null)
    {
        return $this->getByDotNotation($key, $default, $this->options);
    }

    /**
     * Gets an array value by its key. Supports dot notation (e.g. oauth.consumer_key)
     *
     * @param string $key
     * @param mixed $default
     * @param array $data
     *
     * @return mixed
     */
    private function getByDotNotation(string $key, $default = null, array $data = [])
    {
        $keys = explode('.', $key);

        $firstKey = array_shift($keys);

        // All keys exhausted
        if (empty($keys)) {
            return $data[$firstKey] ?? $default;
        }

        $remainingKeys = join('.', $keys);

        return $this->getByDotNotation($remainingKeys, $default, $data[$firstKey]);
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
        } catch (\Exception $e) {
            if ($e instanceof \GuzzleHttp\Exception\RequestException) {
                throw new RequestException($e->getResponse()->getBody(true));
            } else {
                throw new RequestException($e->getMessage());
            }
        }

        $body = $response->getBody();

        $this->lastResponse = $body->getContents();

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
            $this->getBaseDomain() . '/Oauth',
            ltrim($uri, '/'));

        return $this->send($method, $OAuthUrl, $parameters, $headers);
    }

    public function getLastResponse()
    {
        return $this->lastResponse;
    }
}
