<?php
namespace NABVelocity;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\ClientException as GuzzleClientException;
use GuzzleHttp\Psr7\Request;

use NABVelocity\ExceptionHandler;

class Client {

    const REST_URI = 'https://api.nabcommerce.com/REST/2.0.18/Txn';
    const REST_URI_CERT = 'https://api.cert.nabcommerce.com/REST/2.0.18/Txn';

    /**
     * @var GuzzleHttp\Client
     */
    private $client;

    /**
     * @var string
     */
    private $identityToken;

    /**
     * @var string
     */
    private $sessionToken;

    /**
     * @var GuzzleHttp\Psr7\Request
     */
    private $lastRequest;

    /**
     * @var GuzzleHttp\Psr7\Response
     */
    private $lastResponse;

    /**
     * @var array
     */
    private $options = [
        'base_uri'    => 'https://api.cert.nabcommerce.com/',
        'api_path'        => 'REST/{{api_version}}/SvcInfo',
        'api_version' => '2.0.18',

        'headers'     => [
            'User-Agent'   => 'php-nabvelocity-api (http://github.com/hatch-is/php-nabvelocity-api)',
            'Content-Type' => 'application/json',
            'Accept'       => 'application/json',
        ],
        'timeout'     => 10
    ];

    /**
     * Guzzle client constructor
     */
    public function __construct () {
    }

    public function setApiVersion ($version = '') {
        $this->options['api_version'] = $version;
    }

    public function buildPath ($path) {
        $api_path = str_replace('{{api_version}}', $this->options['api_version'], $this->options['api_path']);

        return $api_path . $path;
    }

    /**
     * Get client
     *
     * @return \GuzzleHttp\Client
     */
    public function getHttpClient () {
        return $this->client;
    }

    /**
     * Send request
     */
    public function send (Request $request, array $options = [], $client = null) {
        $this->lastResponse = null;
        $this->lastRequest = null;

        $uri = $request->getUri();
        $path = $this->buildPath($uri->getPath());
        $uri = $uri->withPath($path);
        $request = $request->withUri($uri);

        $this->lastRequest = $request;

        try {

            if($client instanceof GuzzleClient) {
                $response = $client->send($request, $options);
            }
            else {
                $response = $this->client->send($request, $options);
            }

            $this->lastResponse = $response;
            return json_decode($response->getBody());
        }
        catch (GuzzleClientException $e) {
            $this->lastResponse = $e->getResponse();

            throw ExceptionHandler::getException($e);
        }
    }

    public function getIdentityToken () {
        return $this->identityToken;
    }

    public function getSessionToken () {
        return $this->sessionToken;
    }

    public function getLastResponse () {
        return $this->lastResponse;
    }

    /**
     * Sign on
     *
     * @link http://docs.nabvelocity.com/hc/en-us/articles/202476453-Sign-On-Authentication#SignOn
     */
    public function signOn ($identityToken, $options = []) {
        $this->identityToken = $identityToken;

        $options = array_merge($this->options, $options, [
            'auth' => [
                $identityToken,
                ''
            ]
        ]);

        $client = new GuzzleClient($options);

        $request = new Request('get', '/token');

        $response = $this->send($request, [], $client);

        $this->sessionToken = $response;
    }


}
