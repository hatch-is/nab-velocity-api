<?php
namespace NABVelocity;

use NABVelocity\Exceptions;
use GuzzleHttp\Exception\ClientException as GuzzleClientException;

class ExceptionHandler {

    public static function getException (\Exception $e) {
        $message = $e->getMessage();
        $code = $e->getCode();

        if($e instanceof GuzzleClientException) {
            $response = $e->getResponse();
            $error = @json_decode($response->getBody(), true);

            if(is_array($error) && isset($error['ErrorId'])) {
                $message = $error['Messages'];
                if(is_array($error['Messages'])) {
                    $message = implode('\n', $error['Messages']);
                }

                $code = $error['ErrorId'];
            }
        }

        switch($code)
        {
            case 7002:
                $exception = new Exceptions\InvalidSecurityTokenException($message, $code, $e);
                break;
            default:
                $exception = new Exceptions\UnhandledException('Unhandled exception', 0, $e);
        }

        return $exception;
    }

}
