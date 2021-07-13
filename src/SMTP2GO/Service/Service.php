<?php
namespace SMTP2GO\Service;

use SMTP2GO\Contracts\BuildsRequest;

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

    /**
     *
     * @param string $endpoint relative to the api base URL
     * @param array $requestBody an array of key value pairs to send to the endpoint
     * @link https://apidoc.smtp2go.com/documentation/#/README
     */
    public function __construct(string $endpoint, $requestBody = [])
    {
        $this->setEndpoint($endpoint);
        $this->setRequestBody($requestBody);
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

    /**
     * Set the HTTP Method for the service
     *
     * @param string $method
     * @return void
     */
    public function setMethod(string $method)
    {
        $this->method = $method;
    }

    /**
     * Get the value of RequestBody
     */
    public function getRequestBody(): array
    {
        return $this->requestBody;
    }

    /**
     * Set the value of RequestBody
     *
     * @return  Service
     */
    public function setRequestBody(array $requestBody): Service
    {
        $this->requestBody = $requestBody;

        return $this;
    }

    /**
     * Build the request body to be sent to the api
     *
     * @return array
     */
    public function buildRequestBody(): array
    {
        return $this->requestBody;
    }
}
