<?php
namespace Diatria\LaravelInstant\Utils;

class ErrorException extends \Exception
{
    private $errorCode, $data;

    public function __construct($message, $code, $data = null)
    {
        $errorCode = (new Response())->translateCode($code)["http_code"];
        parent::__construct($message, $errorCode);

        $this->errorCode = $code;
        $this->data = $data;
    }

    public function getErrorCode()
    {
        return $this->errorCode ?: $this->getCode() ?: 500;
    }

    public function getPayload()
    {
        return $this->data;
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function getResponse($data = null)
    {
        return Response::json($data, $this->getMessage(), $this->getErrorCode() ?? 500, $this->getTrace());
    }
}
