<?php

namespace SMTP2GO\Service\Concerns;

interface BuildsRequest
{
    /**
     * Returns the HTTP Request Method Used for the endpoint GET|POST|PUT etc
     *
     * @return string
     */
    public function getMethod() : string;
    
    /**
     * Returns the endpoint to the service
     *
     * @return string
     */
    public function getEndpoint(): string;

    /**
     * Builds the request body to send
     *
     * @return array
     */
    public function buildRequestBody(): array;

}