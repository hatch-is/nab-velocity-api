<?php
namespace NABVelocity\Tests;

use NABVelocity\Client;
use NABVelocity\Exceptions;

$identityToken = include 'identityToken.php';

class ClientTest extends \PHPUnit_Framework_TestCase
{

    protected $options = ['debug' => false];

    /**
     * @test
     * @expectedException     \NABVelocity\Exceptions\InvalidSecurityTokenException
     * @expectedExceptionCode 7002
     */
    public function shouldThrowInvalidSecurityToken()
    {
        $client = new Client();
        $client->signOn(123, $this->options);
        $sessionToken = $client->getSessionToken();

        $this->assertInstanceOf(GuzzleHttp\Psr7\Request, $this->lastRequest);
        $this->assertInstanceOf(GuzzleHttp\Psr7\Response, $this->lastResponse);
    }

    /**
     * @test
     */
    public function shouldReturnSessionToken()
    {
        global $identityToken;
        $client = new Client();
        $client->signOn($identityToken, $this->options);

        $sessionToken = $client->getSessionToken();

        $this->assertStringStartsWith('PHN', $sessionToken);
        $this->assertStringEndsWith('==', $sessionToken);
    }
}
