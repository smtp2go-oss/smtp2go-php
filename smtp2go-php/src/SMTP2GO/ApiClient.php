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
    protected $last_response = null;

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
    protected $api_key;

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

    public function setApiKey(string $apiKey)
    {
        $this->api_key = $apiKey;
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

        $payload['api_key'] = $this->api_key;

        $basepath = dirname(__FILE__, 3);

        try {
            $this->last_response = $this->httpClient->request(
                $service->getMethod(),
                static::API_URL . $service->getEndpoint(),
                [
                    'json'   => $payload,
                    'verify' => $basepath . '/ca-bundle.crt',
                ]
            );
        } catch (ClientException $e) {
            echo Message::toString($e->getRequest());
            echo Message::toString($e->getResponse());
        }
        $code = null;

        if (!empty($this->last_response)) {
            $code = $this->last_response->getStatusCode(); // 200 or 400
        }

        return $code === 200;
    }

    public function getResponseBody(): string
    {
        if ($this->last_response) {
            return (string) $this->last_response->getBody();
        }
        return '';
    }

    public function getResponseHeaders(): string
    {
        if ($this->last_response) {
            return (string) $this->last_response->getHeaders();
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
        return $this->client;
    }

    /**
     * Set the GuzzleHttp Client instance
     *
     * @param  \GuzzleHttp\Client $client  The GuzzleHttp Client instance
     *
     * @return  self
     */
    public function setClient(Client $client)
    {
        $this->client = $client;

        return $this;
    }
}
