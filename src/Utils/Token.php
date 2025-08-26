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
            if (config('laravel-instant.auth.allow_check_with_bearer', false)) {
                // Check with bearer and cookies
                if (self::getToken()) {
                    $decoded = JWT::decode(self::getToken(), new Key(env("JWT_KEY"), "HS256"));
                }
            } else {
                // Check only with cookies
                if (isset($_COOKIE[$cookieName])) {
                    $decoded = JWT::decode($_COOKIE[$cookieName], new Key(env("JWT_KEY"), "HS256"));
                }
            }

            return isset($decoded) ? true : false;
        } catch (SignatureInvalidException $e) {
            throw new ErrorException($e->getMessage(), 4012);
        } catch (ExpiredException $e) {
            throw new ErrorException($e->getMessage(), 4013);
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
     * Default expired 1 hours
     */
    public function createToken(array $payload)
    {
        return JWT::encode(
            [
                "iss" => env("APP_URL"), // Issuer (pihak yang mengeluarkan token)
                "exp" => Carbon::now()->addSeconds(config("laravel-instant.auth.token_expires", 3600))->getTimestamp(), // Expiration time (waktu kadaluarsa token)
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
                "exp" => Carbon::now()->addSeconds(config("laravel-instant.auth.token_refresh_expires", 21600))->getTimestamp(), // Expiration time (waktu kadaluarsa token)
                "iat" => Carbon::now()->getTimestamp(), // Issued at time (waktu token dikeluarkan)
                ...$payload,
            ],
            env("JWT_KEY"),
            "HS256",
        );
    }

    /**
     * Mengambil informasi token dari cookies dan bearer token
     */
    public static function getToken(): string
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
            throw new ErrorException($e->getMessage(), 4012);
        } catch (ExpiredException $e) {
            throw new ErrorException($e->getMessage(), 4013);
        } catch (ErrorException $e) {
            throw new ErrorException($e->getMessage(), $e->getErrorCode());
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Remove cookie / token
     */
    public static function revokeToken()
    {
        $domain = Helper::getDomain(null, request()->domain, ["port" => false]);
        setcookie(strtolower(env("APP_TOKEN_NAME") . "_TOKEN"), "", time() - 3600, "/", config("laravel-instant.cookies.domain", $domain), false, true);
    }

    /**
     * Melakukan generate ulang token menggunakan refresh token
     */
    public static function refreshToken(string $refreshToken)
    {
        $payload = self::verify($refreshToken);

        $payload = collect($payload)->except(['iss', 'exp', 'iat'])->toArray();
        $newToken = self::create($payload);
        self::setToken($newToken['token']);
        return $newToken;
    }

    /**
     * Melakukan set token ke cookies
     */
    public static function setToken(string $token): bool
    {
        $domain = Helper::getDomain(config('laravel-instant.cookies.domain'), request()->domain ?? null, ["port" => false]);
        return setcookie(strtolower(env("APP_TOKEN_NAME") . "_TOKEN"), $token, [
            "expires" => Carbon::now()->addSeconds(config("laravel-instant.cookies.expires", 3600))->getTimestamp(),
            "path" => config("laravel-instant.cookies.path", "/"),
            "domain" => config("laravel-instant.cookies.domain", $domain),
            "secure" => config("laravel-instant.cookies.secure", false),
            "httponly" => config("laravel-instant.cookies.httponly", true),
            "samesite" => config("laravel-instant.cookies.samesite", "none"),
        ]);
    }

    /**
     * Melakukan verifikasi token dari string $token
     * @return array{uuid: string, email: string, name: string, role: string}
     */
    public static function verify(string $token): array
    {
        try {
            // Verifikasi Token
            $decoded = JWT::decode($token, new Key(env("JWT_KEY"), "HS256"));
            return json_decode(json_encode($decoded), true);
        } catch (SignatureInvalidException $e) {
            throw new ErrorException($e->getMessage(), 4012);
        } catch (ExpiredException $e) {
            throw new ErrorException($e->getMessage(), 4013);
        } catch (ErrorException $e) {
            throw new ErrorException($e->getMessage(), $e->getErrorCode());
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), $e->getCode());
        }
    }
}
