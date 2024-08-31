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
                $decoded = JWT::decode(
                    $_COOKIE[$cookieName],
                    new Key(env("JWT_KEY"), "HS256")
                );
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
            "HS256"
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
            "HS256"
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
     */
    public static function info()
    {
        try {
            // Decrypt token
            $decoded = JWT::decode(
                self::getToken(),
                new Key(env("JWT_KEY"), "HS256")
            );
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
    public static function  revokeToken()
    {
        setcookie(
            strtolower(env("APP_TOKEN_NAME") . "_TOKEN"),
            "",
            time() - 3600,
            "/",
            Helper::getDomain(),
            false,
            true
        );
    }

    /**
     * Melakukan set token ke cookies
     */
    public static function setToken(string $token)
    {
        setcookie(
            strtolower(env("APP_TOKEN_NAME") . "_TOKEN"),
            $token,
            Carbon::now()->addHours(6)->getTimestamp(),
            "/",
            Helper::getDomain(),
            false,
            true
        );
    }
}
