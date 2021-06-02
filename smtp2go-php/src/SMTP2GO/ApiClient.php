<?php

namespace SMTP2GO;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Psr7\Message;
use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\RequestInterface;
use SMTP2GO\Service\Concerns\BuildsRequest;

class ApiClient
{
    /**
     * the "base" url for the api
     *
     * @var string
     */
    const API_URL = 'https://api.smtp2go.com/v3/';

    /**
     * The last response relieved from the api as a json object
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
     */
    protected $apiKey;

    /**
     * The GuzzleHttp Client instance
     *
     * @var \GuzzleHttp\Client
     */
    protected $httpClient;

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
     * Consume a service on the SMTP2GO Api
     *
     * @param \SMTP2GO\Service\Concerns\BuildsRequest $service
     * @since 1.0.0
     * @return bool
     */
    public function consume(BuildsRequest $service): bool
    {
        $body = [];
        // new Request()
        $body = $service->buildRequestBody();

        $body['apiKey'] = $this->apiKey;

        $basepath = dirname(__FILE__, 3);

        try {
            $this->lastResponse = $this->httpClient->request(
                $service->getMethod(),
                static::API_URL . $service->getEndpoint(),
                [
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
            $statusCode = $this->lastResponse->getStatusCode(); // 200 or 400
        }

        return $statusCode === 200;
    }

    /**
     * Return the response body as a string
     *
     * @return string
     */
    public function getResponseBody(): string
    {
        if ($this->lastResponse) {
            return (string) $this->lastResponse->getBody();
        }
        return '';
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
        return '';
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
     * @return  self
     */
    public function setHttpClient(Client $httpClient)
    {
        $this->httpClient = $httpClient;

        return $this;
    }

    /**
     * Get last request - only set in the event of a ClientException being thrown
     *
     * @return  mixed
     */
    public function getLastRequest($asString = true)
    {
        return $asString ? Message::toString($this->lastRequest) : $this->lastRequest;
    }
}
