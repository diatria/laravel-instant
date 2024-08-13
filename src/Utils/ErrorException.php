<?php
namespace Diatria\LaravelInstant\Utils;

class ErrorException extends \Exception
{
    private $errorCode;

    public function __construct($message, $code = null)
    {
        $errorCode = $this->translateCode($code);
        parent::__construct($message, $errorCode);
        
        $this->errorCode = $code;
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

    public function translateCode(int|string $code) : int {
        $listStatusCode = [
            "SUCCESS" => 200,
            "CREATED" => 201,
            "ACCEPTED" => 202,
            "UNAUTHORIZED" => 401,
            "FORBIDDEN" => 403,
            "NOT_FOUND" => 404,
            "CONFLICT" => 409,
            "APPLICATION_ERROR" => 500,
            "AUTH_INVALID_TOKEN" => 4001,
            "AUTH_INVALID_SIGNATURE" => 4002,
            "AUTH_EXPIRED_TOKEN" => 4003,
            "DB_ERROR" => "42S22",
            "DB_DUPLICATE_ENTRY" => 23000,
            "DB_DATA_TOO_LONG" => 22001,
        ];

        $foundApplicationCode = array_search($code, $listStatusCode);
        if (!$foundApplicationCode) return 500;
        return $listStatusCode[$foundApplicationCode] ?: 500;
    }
}
