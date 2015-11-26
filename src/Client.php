<?php

namespace NABVelocity;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\ClientException as GuzzleClientException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use NABVelocity\Exceptions\Exception;
use NABVelocity\Exceptions\UnhandledException;

require_once __DIR__ . '/sdk/Velocity.php';

class Client
{
    const REST_URI = 'https://api.nabcommerce.com/';
    const REST_URI_CERT = 'https://api.cert.nabcommerce.com/';

    /**
     * @var GuzzleClient
     */
    private $client;

    /**
     * @var string
     */
    private $identityToken;

    /**
     * @var bool
     */
    private $isTestAccount;

    /**
     * @var int
     */
    private $applicationProfileId;

    /**
     * @var string
     */
    private $workflowId;

    /**
     * @var string
     */
    private $sessionToken;

    /**
     * @var string
     */
    private $serviceId;

    /**
     * @var Request
     */
    private $lastRequest;

    /**
     * @var Response
     */
    private $lastResponse;

    /**
     * @var array
     */
    private $options
        = [
            'base_uri'    => 'https://api.cert.nabcommerce.com/',
            'api_path'    => 'REST/{{api_version}}/SvcInfo',
            'api_version' => '2.0.18',

            'headers'     => [
                'User-Agent'   => 'php-nabvelocity-api (http://github.com/hatch-is/php-nabvelocity-api)',
                'Content-Type' => 'application/json',
                'Accept'       => 'application/json',
            ],
            'timeout'     => 30
        ];

    /**
     * @param int       $applicationProfileId
     * @param string    $identityToken
     * @param string    $workflowId
     * @param bool|true $isTestAccount
     */
    public function __construct($applicationProfileId, $identityToken,
        $workflowId,
        $isTestAccount = true
    ) {
        $this->identityToken = $identityToken;
        $this->workflowId = $workflowId;
        $this->isTestAccount = $isTestAccount;
        $this->applicationProfileId = $applicationProfileId;

        $this->options['base_uri'] = $isTestAccount ? self::REST_URI_CERT
            : self::REST_URI;
    }

    public function setApiVersion($version = '')
    {
        $this->options['api_version'] = $version;
    }

    public function buildPath($path)
    {
        $api_path = str_replace(
            '{{api_version}}', $this->options['api_version'],
            $this->options['api_path']
        );

        return $api_path.$path;
    }

    /**
     * Get client
     *
     * @return \GuzzleHttp\Client
     */
    public function getHttpClient()
    {
        return $this->client;
    }

    /**
     * @param Request $request
     * @param array   $options
     * @param null    $client
     *
     * @return mixed
     * @throws Exceptions\InvalidSecurityTokenException
     * @throws Exceptions\UnhandledException
     */
    public function send(Request $request, array $options = [], $client = null)
    {
        $this->lastResponse = null;
        $this->lastRequest = null;

        $uri = $request->getUri();
        $path = $this->buildPath($uri->getPath());
        $uri = $uri->withPath($path);
        $request = $request->withUri($uri);

        $this->lastRequest = $request;

        try {

            if ($client instanceof GuzzleClient) {
                $response = $client->send($request, $options);
            } else {
                $response = $this->client->send($request, $options);
            }

            $this->lastResponse = $response;

            return json_decode($response->getBody());
        } catch (GuzzleClientException $e) {

            $this->lastResponse = $e->getResponse();

            throw ExceptionHandler::getException($e);
        }
    }

    public function getIdentityToken()
    {
        return $this->identityToken;
    }

    public function getSessionToken()
    {
        return $this->sessionToken;
    }

    public function getLastResponse()
    {
        return $this->lastResponse;
    }

    /**
     * @param array $options
     *
     * @throws Exceptions\InvalidSecurityTokenException
     * @throws Exceptions\UnhandledException
     */
    public function signOn($options = [])
    {
        $options = array_merge(
            $this->options, $options, [
                'auth' => [
                    $this->identityToken,
                    ''
                ]
            ]
        );

        $client = new GuzzleClient($options);

        $request = new Request('get', '/token');

        $response = $this->send($request, [], $client);

        $this->sessionToken = $response;
    }

    protected function checkSignOn()
    {
        if (!isset($this->sessionToken)) {
            $this->signOn();
        }

        if (!isset($this->sessionToken)) {
            throw new UnhandledException("Couldn't sign in into API");
        }
    }

    /**
     * @return mixed|bool
     * @throws Exceptions\InvalidSecurityTokenException
     * @throws Exceptions\UnhandledException
     */
    public function getServiceInformation()
    {
        $this->checkSignOn();

        $options = array_merge(
            $this->options, [
                'auth' => [
                    $this->sessionToken,
                    ''
                ]
            ]
        );

        $client = new GuzzleClient($options);

        $request = new Request('get', '/serviceInformation');

        try {
            $response = $this->send($request, [], $client);

            return $response;
        } catch (Exception $e) {
            return false;
        }
    }

    public function getServiceId()
    {
        $this->checkSignOn();

        if (!isset($this->serviceId)) {
            $serviceInfo = $this->getServiceInformation();

            if (!empty($serviceInfo->BankcardServices)) {
                list($bankCardService) = $serviceInfo->BankcardServices;
                $this->serviceId = $bankCardService->ServiceId;
            } else {
                throw new UnhandledException(
                    "There is no bankcard service info"
                );
            }
        }

        return $this->serviceId;
    }

    /**
     * @param $merchantProfile
     *
     * @return bool|mixed
     * @throws Exceptions\InvalidSecurityTokenException
     * @throws UnhandledException
     */
    public function saveMerchantProfile($merchantProfile)
    {
        $this->checkSignOn();

        $options = array_merge(
            $this->options, [
                'auth' => [
                    $this->sessionToken,
                    ''
                ]
            ]
        );

        $client = new GuzzleClient($options);

        $serviceId = $this->getServiceId();
        $merchantProfile['WorkflowId'] = $this->workflowId;
        $merchantProfile['ServiceId'] = $this->getServiceId();

        $request = new Request(
            'put',
            "/merchProfile?serviceId=$serviceId",
            [],
            json_encode([$merchantProfile])
        );

        try {
            $response = $this->send($request, [], $client);

            return $response;
        } catch (Exception $e) {
            return false;
        }
    }

    public function authorizeAndCapture($options = [], $merchantProfileId)
    {
        $processor = $this->getVelocityProcessor($merchantProfileId);

        return $processor->authorizeAndCapture($options);
    }

    public function authorize($options = [], $merchantProfileId)
    {
        $processor = $this->getVelocityProcessor($merchantProfileId);

        return $processor->authorize($options);
    }

    public function undo($options = [], $merchantProfileId)
    {
        $processor = $this->getVelocityProcessor($merchantProfileId);

        return $processor->undo($options);
    }

    private function getVelocityProcessor($merchantProfileId)
    {
        $this->checkSignOn();

        return new \VelocityProcessor(
            $this->applicationProfileId, $merchantProfileId, $this->workflowId,
            $this->isTestAccount, $this->identityToken, $this->getSessionToken()
        );
    }
}