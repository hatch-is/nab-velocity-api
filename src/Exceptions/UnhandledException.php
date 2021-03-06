<?php
namespace NABVelocity\Exceptions;

class UnhandledException extends \Exception {

    public function __construct ($message = null, $code = 0, \Exception $previous = null) {
        $this->message = $message;
        $this->code = $code;
        $this->previous = $previous;
    }

}
