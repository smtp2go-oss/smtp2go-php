<?php
namespace SMTP2GO\Service;

use SMTP2GO\Service\Concerns\BuildsRequests;

/**
 * Generic service class which can be used to consume any endpoint in the API
 */
class Service implements BuildsRequests
{
    protected $endpoint = '';

    protected $method = 'POST';

    protected $payload = [];

    public function __construct($endpoint)
    {
        $this->setEndpoint($endpoint);
    }

    public function getEndpoint(): string
    {
        return $this->endpoint;
    }

    public function setEndpoint(string $endpoint)
    {
        $this->endpoint = $endpoint;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function setMethod($method)
    {
        $this->method = $method;
    }

    /**
     * Get the value of payload
     */
    public function getPayload()
    {
        return $this->payload;
    }

    /**
     * Set the value of payload
     *
     * @return  self
     */
    public function setPayload($payload)
    {
        $this->payload = $payload;

        return $this;
    }

    public function buildRequestPayload(): array
    {
        return $this->payload;
    }
}
