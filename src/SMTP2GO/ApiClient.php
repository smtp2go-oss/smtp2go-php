<?php

namespace SMTP2GO;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Message;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Utils;
use SMTP2GO\Contracts\BuildsRequest;

class ApiClient
{
    /**
     * the "base" url for the api
     * @var string
     */
    const API_URL = 'https://api.smtp2go.com/v3/';

    const HOST = 'api.smtp2go.com';

    /**
     * The region to use for the api
     * allowed options are 'us', 'eu', 'au'
     *
     * @var string
     */
    protected $apiRegion = '';

    /**
     * The last response recieved from the api
     *
     * @var \Psr\Http\Message\ResponseInterface
     */
    protected $lastResponse = null;

    /**
     * If an exception is thrown during the request,
     * the last request will be stored here
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


    /**
     * The maximum number of times to attempt to send the email
     * The client will attempt to iterate through available servers 
     * until the email is sent or the maximum number of attempts is reached
     * @var int
     */
    protected $maxSendAttempts = 1;

    /**
     * The timeout for the request
     * @var int|float
     */
    protected $timeout = 30;

    /**
     * The timeout increment to use when retrying the request
     * @var int|float
     */
    protected $timeoutIncrement = 5;

    /**
     * The number of failed attempts the most request has
     * @var int
     */
    protected $failedAttempts = 0;


    /**
     * The ips of the api servers. These will be used to resolve the
     * host name to an ip address if the first request fails
     * and the maxSendAttempts is greater than 1
     * @var array
     */
    protected array $apiServerIps = [];

    /**
     * In the case of the first request failing, this will  
     * be set to the ip address of the server that failed
     * so that it can be ignored in subsequent requests
     */
    private $ipToIgnore = null;

    /**
     * Holds information about requests that resulted in RequestException | ConnectException exceptions
     * This is useful for debugging and logging when utilising the retry feature, by setting maxSendAttempts > 1
     * @var array
     */
    protected $failedAttemptInfo = [];



    public function __construct($apiKey)
    {
        $this->apiKey = $apiKey;
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

        $this->failedAttempts = 0;

        $successful = false;

        $curlOpts = [];


        $this->ipToIgnore = null;

        $shouldRetry = $this->getMaxSendAttempts() > 1;

        $caPathOrFile = \Composer\CaBundle\CaBundle::getSystemCaRootBundlePath();
        do {
            try {
                $serverIpForRequest = $this->getServerIpForRequest();
                if (!empty($serverIpForRequest)) {
                    $curlOpts = [
                        'curl' => [
                            CURLOPT_RESOLVE => [
                                static::HOST . ':443:' . $serverIpForRequest,
                            ],
                        ],
                    ];
                }
                $this->lastResponse = $this->httpClient->request(
                    $service->getMethod(),
                    $this->getApiUrl() . $service->getEndpoint(),
                    //ensures user options can overwrite these defaults
                    $this->requestOptions + [
                        'json'   => $body,
                        'verify' => $caPathOrFile,
                        'headers' => [
                            'host' => static::HOST,
                        ],
                        'timeout' => $this->getTimeout(),
                        $curlOpts,
                        'on_stats' => function (\GuzzleHttp\TransferStats $stats) {
                            $handlerStats = $stats->getHandlerStats();
                            $this->ipToIgnore = $handlerStats['primary_ip'] ?? null;
                        },
                    ]
                );

                $successful = true;
            } catch (ClientException $e) {
                $this->lastRequest  = $e->getRequest();
                $this->lastResponse = $e->getResponse();
                $shouldRetry = false;
            } catch (RequestException | ConnectException $e) {
                $this->failedAttempts++;
                $this->lastRequest  = $e->getRequest();
                if ($e instanceof RequestException) {
                    $this->lastResponse = $e->getResponse();
                }
                $this->failedAttemptInfo[] = ['ip' => $serverIpForRequest, 'error' => $e->getMessage(),];
                $this->setTimeout($this->getTimeout() + $this->getTimeoutIncrement());
                if (empty($this->apiServerIps) && $this->maxSendAttempts > 1) {
                    $this->loadApiServerIps();
                }
            } catch (\Exception $e) {
                $shouldRetry = false;
            }
        } while (!$successful && $shouldRetry && $this->failedAttempts < $this->maxSendAttempts && !empty($this->apiServerIps));
        $statusCode = null;
        if (!empty($this->lastResponse)) {
            $statusCode = $this->lastResponse->getStatusCode();
        }


        return $statusCode === 200;
    }

    /**
     * Get the url to use for the api request, optionally based on the region set by the user
     * @return string 
     */
    public function getApiUrl()
    {
        if ($this->getApiRegion() === '') {
            return static::API_URL;
        }
        return sprintf('https://%s-api.smtp2go.com/v3/', $this->getApiRegion());
    }

    protected function getServerIpForRequest()
    {
        if (empty($this->apiServerIps)) {
            return;
        }
        $next = array_pop($this->apiServerIps);

        return $next;
    }

    private function loadApiServerIps()
    {
        if (empty($this->getApiServerIps())) {
            $ips = gethostbynamel(static::HOST);
            if (!empty($ips) && is_array($ips)) {
                $this->setApiServerIps(array_filter($ips, function ($ip) {
                    return $ip !== $this->ipToIgnore;
                }));
            }
        }
    }

    public function setApiServerIps(array $ips)
    {
        $this->apiServerIps = $ips;
    }


    /**
     * Return the response body as a json object or string
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
     * Get last response
     *
     * @return  \Psr\Http\Message\ResponseInterface|null
     */
    public function getLastResponse(): ?\Psr\Http\Message\ResponseInterface
    {
        return $this->lastResponse ?? null;
    }

    /**
     * Get the status code from the last response,
     * which is a 3-digit integer result code of the server's attempt to understand and satisfy the request.
     *
     * @return  int
     */
    public function getLastResponseStatusCode()
    {
        if ($this->lastResponse) {
            return $this->lastResponse->getStatusCode();
        }
        return null;
    }

    /**
     * Get last request - only set in the event of a ClientException being thrown
     *
     * @return  string|Request
     */
    public function getLastRequest($asString = true)
    {
        if (!$this->lastRequest) {
            return $asString ? '' : null;
        }
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

    /**
     * Get the region to use for the api
     *
     * @return  string
     */
    public function getApiRegion(): string
    {
        return $this->apiRegion;
    }

    /**
     * Set the region to use for the api
     *
     * @param  string  $apiRegion  The region to use for the api
     *
     * @return  self
     */
    public function setApiRegion(string $apiRegion)
    {
        if (!in_array($apiRegion, ['us', 'eu', 'au'])) {
            throw new \InvalidArgumentException('Invalid region provided. Must be either us, eu or au');
        }
        $this->apiRegion = $apiRegion;

        return $this;
    }

    /**
     * Get the maximum number of times to try and send the request
     *
     * @return  int
     */
    public function getMaxSendAttempts()
    {
        return $this->maxSendAttempts;
    }

    /**
     * Set the maximum number of times to try and send the request
     *
     * @param  int  $maxSendAttempts
     *
     * @return  self
     */
    public function setMaxSendAttempts(int $maxSendAttempts)
    {
        $this->maxSendAttempts = $maxSendAttempts;

        return $this;
    }

    /**
     * Get the timeout for the request
     *
     * @return  int|float
     */
    public function getTimeout()
    {
        return $this->timeout;
    }

    /**
     * Set the timeout for the request
     *
     * @param  int|float  $timeout  The timeout for the request
     *
     * @return  self
     */
    public function setTimeout($timeout)
    {
        $this->timeout = $timeout;

        return $this;
    }



    /**
     * Get the timeout increment to use when retrying the request
     *
     * @return  int|float
     */
    public function getTimeoutIncrement()
    {
        return $this->timeoutIncrement;
    }

    /**
     * Set the timeout increment to use when retrying the request
     *
     * @param  int|float  $timeoutIncrement  The timeout increment to use when retrying the request
     *
     * @return  self
     */
    public function setTimeoutIncrement($timeoutIncrement)
    {
        $this->timeoutIncrement = $timeoutIncrement;

        return $this;
    }

    /**
     * get IP addresses for the host 
     *
     * @return  array
     */
    public function getApiServerIps()
    {
        return $this->apiServerIps;
    }

    /**
     * Get the number of failed attempts the most request has
     *
     * @return  int
     */
    public function getFailedAttempts()
    {
        return $this->failedAttempts;
    }

    /**
     * Get the failed attempt info
     *
     * @return  array
     */
    public function getFailedAttemptInfo()
    {
        return $this->failedAttemptInfo;
    }
}
