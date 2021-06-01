<?php

namespace SMTP2GO;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Psr7\Message;
use SMTP2GO\Service\Concerns\BuildsRequests;

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
     * Meta data about the last response from the api
     *
     * @var mixed
     */
    protected $last_meta;

    /**
     * Api key for the api service
     *
     * @var string
     */
    protected $apiKey;

    /**
     * store failed email sends, the plugin only sends one email at a time, so count will be 0 or 1
     *
     * @var array
     */
    private $failures = [];

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
     * @param \SMTP2GO\Service\Concerns\BuildsRequests $service
     * @since 1.0.0
     * @return bool
     */
    public function consume(BuildsRequests $service): bool
    {
        $payload = [];

        $payload = $service->buildRequestPayload();

        $payload['apiKey'] = $this->apiKey;

        $basepath = dirname(__FILE__, 3);

        try {
            $this->lastResponse = $this->httpClient->request(
                $service->getMethod(),
                static::API_URL . $service->getEndpoint(),
                [
                    'json'   => $payload,
                    'verify' => $basepath . '/ca-bundle.crt',
                ]
            );
        } catch (ClientException $e) {
            /**@todo - decide what to do with at this point */
            echo Message::toString($e->getRequest());
            echo Message::toString($e->getResponse());
        }
        $code = null;

        if (!empty($this->lastResponse)) {
            $code = $this->lastResponse->getStatusCode(); // 200 or 400
        }

        return $code === 200;
    }

    public function getResponseBody(): string
    {
        if ($this->lastResponse) {
            return (string) $this->lastResponse->getBody();
        }
        return '';
    }

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
}
