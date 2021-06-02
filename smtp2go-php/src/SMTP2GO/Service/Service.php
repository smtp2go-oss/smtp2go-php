<?php
namespace SMTP2GO\Service;

use SMTP2GO\Service\Concerns\BuildsRequest;

/**
 * Generic service class which can be used to consume any endpoint in the API
 */
class Service implements BuildsRequest
{
    /**
     * Relative to the base api url e.g ***stats/email_bounces***
     */
    protected $endpoint = '';

    protected $method = 'POST';

    protected $requestBody = [];

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
     * Get the value of RequestBody
     */
    public function getRequestBody()
    {
        return $this->requestBody;
    }

    /**
     * Set the value of RequestBody
     *
     * @return  self
     */
    public function setRequestBody($RequestBody)
    {
        $this->requestBody = $RequestBody;

        return $this;
    }

    public function buildRequestBody(): array
    {
        return $this->requestBody;
    }
}
