<?php

namespace Diatria\LaravelInstant\Utils;

use Carbon\Carbon;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\SignatureInvalidException;

class Token
{
    /**
     * Melakukan pengecekan / validasi JWT token
     */
    public static function check(): bool
    {
        try {
            // Verifikasi Token
            $cookieName = strtolower(env("APP_TOKEN_NAME") . "_TOKEN");
            if (isset($_COOKIE[$cookieName])) {
                $decoded = JWT::decode($_COOKIE[$cookieName], new Key(env("JWT_KEY"), "HS256"));
            }
            return isset($decoded) ? true : false;
        } catch (SignatureInvalidException $e) {
            throw new ErrorException($e->getMessage(), 4001);
        } catch (ExpiredException $e) {
            throw new ErrorException($e->getMessage(), 4002);
        }
    }

    /**
     * Create token dan refresh token
     */
    public static function create(array $payload): array
    {
        return [
            "token" => (new self())->createToken($payload),
            "token_refresh" => (new self())->createTokenRefresh($payload),
        ];
    }

    /**
     * Create token only
     * Default expired 6 hours
     */
    public function createToken(array $payload)
    {
        return JWT::encode(
            [
                "iss" => env("APP_URL"), // Issuer (pihak yang mengeluarkan token)
                "exp" => Carbon::now()->addHours(6)->getTimestamp(), // Expiration time (waktu kadaluarsa token)
                "iat" => Carbon::now()->getTimestamp(), // Issued at time (waktu token dikeluarkan)
                ...$payload,
            ],
            env("JWT_KEY"),
            "HS256",
        );
    }

    /**
     * Create refresh token only
     */
    public function createTokenRefresh($payload)
    {
        return JWT::encode(
            [
                "iss" => env("APP_URL"), // Issuer (pihak yang mengeluarkan token)
                "exp" => Carbon::now()->addDays(3)->getTimestamp(), // Expiration time (waktu kadaluarsa token)
                "iat" => Carbon::now()->getTimestamp(), // Issued at time (waktu token dikeluarkan)
                ...$payload,
            ],
            env("JWT_KEY"),
            "HS256",
        );
    }

    /**
     * Mengambil informasi token dari cookies
     */
    public static function getToken()
    {
        if (isset($_COOKIE[strtolower(env("APP_TOKEN_NAME") . "_TOKEN")])) {
            return $_COOKIE[strtolower(env("APP_TOKEN_NAME") . "_TOKEN")];
        }

        return request()->bearerToken() ?? throw new ErrorException("Token Not Found!", 401);
    }

    /**
     * Mengambil informasi token
     * @return array{uuid: string, email: string, name: string, role: string}
     */
    public static function info()
    {
        try {
            // Decrypt token
            $decoded = JWT::decode(self::getToken(), new Key(env("JWT_KEY"), "HS256"));
            return (array) $decoded;
        } catch (SignatureInvalidException $e) {
            throw new ErrorException($e->getMessage(), 4001);
        } catch (ExpiredException $e) {
            throw new ErrorException($e->getMessage(), 4002);
        }
    }

    /**
     * Remove cookie / token
     */
    public static function revokeToken()
    {
        $domain = Helper::getDomain(null, request()->domain, ["port" => false]);
        setcookie(strtolower(env("APP_TOKEN_NAME") . "_TOKEN"), "", time() - 3600, "/", config("laravel-instant.cookie.domain", $domain), false, true);
    }

    /**
     * Melakukan set token ke cookies
     */
    public static function setToken(string $token)
    {
        $domain = Helper::getDomain(null, request()->domain ?? null, ["port" => false]);
        setcookie(strtolower(env("APP_TOKEN_NAME") . "_TOKEN"), $token, [
            "expires" => Carbon::now()->addHours(6)->getTimestamp(),
            "path" => config("laravel-instant.cookie.path", "/"),
            "domain" => config("laravel-instant.cookie.domain", $domain),
            "secure" => config("laravel-instant.cookie.secure", false),
            "httponly" => config("laravel-instant.cookie.httponly", true),
            "samesite" => config("laravel-instant.cookie.samesite", "none"),
        ]);
    }

    /**
     * Melakukan verifikasi token dari string $token
     */
    public static function verify(string $token): array
    {
        try {
            // Verifikasi Token
            $decoded = JWT::decode($token, new Key(env("JWT_KEY"), "HS256"));
            return json_decode(json_encode($decoded), true);
        } catch (SignatureInvalidException $e) {
            return Response::error($e->getMessage(), 4001);
        } catch (ExpiredException $e) {
            return Response::error($e->getMessage(), 4002);
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), $e->getCode());
        }
    }

    public static function verify(string $token): array
    {
        try {
            // Verifikasi Token
            $decoded = JWT::decode($token, new Key(env("JWT_KEY"), "HS256"));
            return json_decode(json_encode($decoded), true);
        } catch (SignatureInvalidException $e) {
            return Response::error($e->getMessage(), 4001);
        } catch (ExpiredException $e) {
            return Response::error($e->getMessage(), 4002);
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), $e->getCode());
        }
    }
}
