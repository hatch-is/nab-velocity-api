<?php

namespace NABVelocity\Exceptions;

use GuzzleHttp\Exception\ClientException as GuzzleClientException;

abstract class Exception extends \Exception {

    public function __construct ($message = null, $code = 0, \Exception $previous = null) {
        $this->message = $message;
        $this->code = $code;
        $this->previous = $previous;
    }

    public function getResponse () {
        if($this->previous instanceof GuzzleClientException) {
            return $this->previous->getResponse();
        }

        return null;
    }

}
