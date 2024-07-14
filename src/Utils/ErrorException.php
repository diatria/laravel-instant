<?php
namespace Diatria\LaravelInstant\Utils;

class ErrorException extends \Exception
{
    private $errorCode;

    public function __construct($message, $errorCode = null)
    {
        parent::__construct($message);
        $this->errorCode = $errorCode;
    }

    public function getErrorCode()
    {
        return $this->errorCode ?: $this->getCode() ?: 500;
    }

    public function getResponse($data = null)
    {
        return Response::json(
            $data,
            $this->getMessage(),
            $this->getErrorCode() ?? 500,
            $this->getTrace()
        );
    }
}
