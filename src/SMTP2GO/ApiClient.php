<?php

namespace SMTP2GO;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Psr7\Message;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Utils;
use SMTP2GO\Contracts\BuildsRequest;

class ApiClient
{
    /**
     * the "base" url for the api
     *
     * @var string
     */
    const API_URL = 'https://api.smtp2go.com/v3/';

    /**
     * The last response recieved from the api as a json object
     *
     * @var \Psr\Http\Message\ResponseInterface
     */
    protected $lastResponse = null;

    /**
     * If an exception is thrown during the request, the last request will be stored here
     *
     * @var \Psr\Http\Message\RequestInterface
     */
    protected $lastRequest = null;

    /**
     * Api key for the api service
     *
     * @var string
     * @link https://app.smtp2go.com/settings/apikeys/
     */
    protected $apiKey;

    /**
     * The GuzzleHttp Client instance
     *
     * @var \GuzzleHttp\Client
     */
    protected $httpClient;

    /**
     * Array of key value pairs to pass to The GuzzleHttp Client request method
     * These will override any default options used by \SMTP2GO\ApiClient
     *
     * @var array
     * @link https://docs.guzzlephp.org/en/stable/request-options.html
     */
    protected $requestOptions = [];

    public function __construct($apiKey)
    {
        $this->setApiKey($apiKey);
        $this->httpClient = new Client;
    }

    /**
     * Set the SMTP2GO Api Key
     *
     * @param string $apiKey
     * @return void
     */
    public function setApiKey(string $apiKey)
    {
        $this->apiKey = $apiKey;
    }

    /**
     * Set request options for the GuzzleHTTP Client
     *
     * @param array $options
     * @return void
     * @link https://docs.guzzlephp.org/en/stable/request-options.html
     */
    public function setrequestOptions(array $options)
    {
        $this->requestOptions = $options;
    }

    /**
     * Consume a service on the SMTP2GO Api
     *
     * @param \SMTP2GO\Contracts\BuildsRequest $service
     * @since 1.0.0
     * @return bool
     */
    public function consume(BuildsRequest $service): bool
    {
        $body = [];

        $body = $service->buildRequestBody();

        $body['api_key'] = $this->apiKey;

        $basepath = dirname(__FILE__, 3);

        try {
            $this->lastResponse = $this->httpClient->request(
                $service->getMethod(),
                static::API_URL . $service->getEndpoint(),
                //ensures user options can overwrite these defaults
                $this->requestOptions + [
                    'json'   => $body,
                    'verify' => $basepath . '/ca-bundle.crt',
                ]
            );
        } catch (ClientException $e) {
            $this->lastRequest  = $e->getRequest();
            $this->lastResponse = $e->getResponse();
        }
        $statusCode = null;

        if (!empty($this->lastResponse)) {
            $statusCode = $this->lastResponse->getStatusCode();
        }

        return $statusCode === 200;
    }

    /**
     * Return the response body as a string
     *
     * @return \stdClass|string
     */
    public function getResponseBody($asJson = true)
    {
        if (!$this->lastResponse) {
            return '';
        }
        if (!$asJson) {
            return (string) $this->lastResponse->getBody();
        }
        if ($this->lastResponse) {
            return Utils::jsonDecode((string) $this->lastResponse->getBody());
        }
    }

    /**
     * Return the headers from the last response
     *
     * @return array
     */
    public function getResponseHeaders(): array
    {
        if ($this->lastResponse) {
            return $this->lastResponse->getHeaders();
        }
        return [];
    }

    /**
     * Get the GuzzleHttp Client instance
     *
     * @return  \GuzzleHttp\Client
     */
    public function getClient(): Client
    {
        return $this->httpClient;
    }

    /**
     * Set the GuzzleHttp Client instance
     *
     * @param  \GuzzleHttp\Client $client  The GuzzleHttp Client instance
     *
     * @return  ApiClient
     */
    public function setHttpClient(Client $httpClient): ApiClient
    {
        $this->httpClient = $httpClient;

        return $this;
    }

    /**
     * Get last request - only set in the event of a ClientException being thrown
     *
     * @return  string|Request
     */
    public function getLastRequest($asString = true)
    {
        return $asString ? Message::toString($this->lastRequest) : $this->lastRequest;
    }

    /**
     * Get custom guzzleHTTP client options used by \SMTP2GO\ApiClient
     *
     * @return  array
     */
    public function getrequestOptions(): array
    {
        return $this->requestOptions;
    }
}
