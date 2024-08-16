<?php

namespace Diatria\LaravelInstant\Utils;

use Throwable;

class Response
{
    public static function json(
        $data = null,
        string $message = "",
        $errorCode = 200,
        array $trace = []
    ) {
        $payloadOptional = [];
        if (env("APP_DEBUG")) {
            $payloadOptional = [
                "memory_usage" => Helper::convertDiskCapacity(
                    memory_get_usage()
                ),
                "request" => request()->all(),
                "trace" => self::traceError($trace),
                "http_code" => $errorCode,
            ];
        }

        $payload = [
            "code" => self::toApplicationCode($errorCode),
            "message" => "{$message}",
            "data" => $data,
            "data_count" => 0,
            ...$payloadOptional,
        ];
        return response()->json($payload, self::toHttpCode($errorCode));
    }

    public static function error($errorCode, string $message)
    {
        if (self::isHttpCode($errorCode)) {
            throw new \Exception($message, $errorCode);
        } else {
            throw new ErrorException($message, $errorCode);
        }
    }

    public static function errorJson(Throwable $exception)
    {
        if ($exception instanceof ErrorException) {
            return self::json(
                null,
                $exception->getMessage(),
                $exception->getErrorCode(),
                $exception->getTrace()
            );
        }

        return self::json(
            null,
            $exception->getMessage(),
            $exception->getCode(),
            $exception->getTrace()
        );
    }

    public static function traceError(array $errors)
    {
        $getSpecificsTrace = collect($errors)->filter(function ($error) {
            $needle = Helper::get($error, "class");
            foreach (config('app.response.read_class', []) as $haystack) {
                if (str_contains($needle, $haystack)) {
                    return true;
                }
            }

            return false;
        });

        $traceArray = $getSpecificsTrace
            ->map(function ($error) {
                $classFullPath = Helper::get($error, "class");
                $classPathArray = explode("\\", $classFullPath);
                $class = end($classPathArray);
                $function = $function = Helper::get($error, "function");

                return $class . "." . $function;
            })
            ->reverse();

        return implode(" -> ", $traceArray->toArray());
    }

    /**
     * @param Number code
     * @param Array|Any data
     * @param String message
     */
    public static function getResponse(\Exception $exception, $data = null)
    {
        return self::json(
            $data,
            $exception->getMessage(),
            $exception->getCode() ?? 500,
            $exception->getTrace()
        );
    }

    public static function isHttpCode(int|string $code): bool
    {
        $listHttpCode = [200, 201, 202, 401, 403, 500];
        return in_array($code, $listHttpCode);
    }

    /**
     * Mengembalikan nilai custom kode manjadi nilai kode Http
     */
    public static function toApplicationCode(int|string $statusCode)
    {
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

        $isFoundApplicationCode = array_search($statusCode, $listStatusCode);
        return $isFoundApplicationCode ?: "UNDEFINED_CODE";
    }

    public static function toHttpCode(int|string $statusCode)
    {
        $listApplicationCode = [
            4001 => 401,
            4002 => 401,
            4003 => 401,
            0 => 500,
            "42S22" => 500,
            23000 => 500,
            22001 => 500,
        ];

        $isFoundHttpCode = Helper::get($listApplicationCode, $statusCode);
        if (gettype($statusCode) === "string") {
            return 500;
        }
        return $isFoundHttpCode ?: $statusCode;
    }
}
