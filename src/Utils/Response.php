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
            ];
        }

        $payload = [
            "code" => self::translateCode($errorCode)['application_code'],
            "internal_code" => $errorCode,
            "message" => "{$message}",
            "data" => $data,
            "data_count" => 0,
            ...$payloadOptional,
        ];
        return response()->json($payload, self::translateCode($errorCode)['http_code']);
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
     * @return array {application_code: string, http_code: int}
     */
    static public function translateCode(int|string $code) : array {
        $httpCode = [
            200 => ["application_code" => "SUCCESS", "http_code" => 200],
            201 => ["application_code" => "CREATED", "http_code" => 201],
            202 => ["application_code" => "ACCEPTED", "http_code" => 201],
            401 => ["application_code" => "UNAUTHORIZED", "http_code" => 401],
            403 => ["application_code" => "FORBIDDEN", "http_code" => 403],
            404 => ["application_code" => "NOT_FOUND", "http_code" => 404],
            409 => ["application_code" => "CONFLICT", "http_code" => 409],
            419 => ["application_code" => "PAGE EXPIRED", "http_code" => 419],
            429 => ["application_code" => "TOO_MANY_REQUESTS", "http_code" => 429],
            500 => ["application_code" => "APPLICATION_ERROR", "http_code" => 500],
            4011 => ["application_code" => "AUTH_INVALID_TOKEN", "http_code" => 401],
            4012 => ["application_code" => "AUTH_INVALID_SIGNATURE", "http_code" => 401],
            4013 => ["application_code" => "AUTH_EXPIRED_TOKEN", "http_code" => 401],
            4041 => ["application_code" => "USER_NOT_FOUND", "http_code" => 404],
            23000 => ["application_code" => "DB_DUPLICATE_ENTRY", "http_code" => 500],
            22001 => ["application_code" => "DB_DATA_TOO_LONG", "http_code" => 500],
            "42S22" => ["application_code" => "DB_ERROR", "http_code" => 500],
        ];

        if (isset($httpCode[$code])) {
            return $httpCode[$code];
        } else return [
            "application_code" => "UNDEFINED_CODE",
            "http_code" => 500
        ];
    }
}
