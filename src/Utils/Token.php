<?php

namespace Diatria\LaravelInstant\Utils;

use Carbon\Carbon;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;

class Token
{
    /**
     * Atur secret key untuk mengamankan jwt token
     */
    private $secretKey;

    /**
     * Atur untuk menamai cookies
     */
    private $cookieName;

    protected function create($payload)
    {
        // Create Main Token
        $accessToken = JWT::encode(
            [
                "iss" => config("laravel-instant.app.name", "LI"), // Issuer (pihak yang mengeluarkan token)
                "exp" => Carbon::now()->addSeconds(config("laravel-instant.auth.token_expires", 3600))->getTimestamp(), // Expiration time (waktu kadaluarsa token)
                "iat" => Carbon::now()->getTimestamp(), // Issued at time (waktu token dikeluarkan)
                ...$payload,
            ],
            $this->getSecretKey(),
            "HS256",
        );

        // Create Refresh Token
        $refreshToken = JWT::encode(
            [
                "iss" => config("laravel-instant.app.name", "LI"), // Issuer (pihak yang mengeluarkan token)
                "exp" => Carbon::now()->addSeconds(config("laravel-instant.auth.token_refresh_expires", 21600))->getTimestamp(), // Expiration time (waktu kadaluarsa token)
                "iat" => Carbon::now()->getTimestamp(), // Issued at time (waktu token dikeluarkan)
                ...$payload,
            ],
            $this->getSecretKey(),
            "HS256",
        );

        // Set main token to cookies
        $this->setToken($accessToken, 'access', config("laravel-instant.cookies.expires", 3600));
        $this->setToken($refreshToken, 'refresh', config("laravel-instant.auth.token_refresh_expires", 21600));

        return [
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken,
        ];
    }

    protected function getCookieName()
    {
        return $this->cookieName ?? config("laravel-instant.cookies.name", 'LI');
    }

    protected function getSecretKey()
    {
        return $this->secretKey ?? sha1(config("laravel-instant.app.secret_key"));
    }

    protected function getToken(string $type = 'access')
    {
        $suffix = $type === 'refresh' ? '_REFRESH_TOKEN' : '_ACCESS_TOKEN';
        $cookieKey = strtolower($this->getCookieName() . $suffix);

        if (isset($_COOKIE[$cookieKey])) {
            return $_COOKIE[$cookieKey];
        }

        if ($type === 'access') {
            // Access token masih bisa fallback ke bearer
            return request()->bearerToken() ?? throw new \ErrorException("Access Token Not Found!", 401);
        }

        throw new \ErrorException("Refresh Token Not Found!", 401);
    }

    protected function logout()
    {
        // hapus access token cookie
        setcookie(strtolower($this->getCookieName() . "_ACCESS_TOKEN"), '', time() - 3600, '/');

        // hapus refresh token cookie
        setcookie(strtolower($this->getCookieName() . "_REFRESH_TOKEN"), '', time() - 3600, '/');
    }

    protected function setConfig(array $config = [])
    {
        if (isset($config['secret_key'])) {
            $this->secretKey = $config['secret_key'];
        }

        if (isset($config['cookie_name'])) {
            $this->cookieName = $config['cookie_name'];
        }

        return $this;
    }

    /**
     * Melakukan set token ke cookies
     */
    protected function setToken(string $token, string $type = 'access', int $expired = 3600): bool
    {
        $suffix = $type === 'refresh' ? '_REFRESH_TOKEN' : '_ACCESS_TOKEN';
        $domain = Helper::getDomain(config('laravel-instant.cookies.domain'), request()->domain ?? null, ["port" => false]);

        return setcookie(strtolower($this->getCookieName() . $suffix), $token, [
            "expires" => Carbon::now()->addSeconds($expired)->getTimestamp(),
            "path" => config("laravel-instant.cookies.path", "/"),
            "domain" => config("laravel-instant.cookies.domain", $domain),
            "secure" => config("laravel-instant.cookies.secure", false),
            "httponly" => config("laravel-instant.cookies.httponly", true),
            "samesite" => config("laravel-instant.cookies.samesite", "none"),
        ]);
    }

    protected function verification(?string $token = null)
    {
        try {
            if ($token) {
                $decoded = JWT::decode($token, new Key($this->getSecretKey(), "HS256"));
                return isset($decoded) ? $decoded : null;
            }

            $decoded = JWT::decode($this->getToken(), new Key($this->getSecretKey(), "HS256"));
            return isset($decoded) ? $decoded : null;
        } catch (ExpiredException $e) {
            $decoded = JWT::decode($this->getToken('refresh'), new Key($this->getSecretKey(), "HS256"));
            if ($decoded) {
                return $this->create(Helper::toArray($decoded));
            }
        }
    }

    public function __call($method, $args)
    {
        if (method_exists($this, $method)) {
            return $this->$method(...$args);
        }
        throw new \BadMethodCallException("Method {$method} tidak ditemukan.");
    }

    public static function __callStatic($method, $args)
    {
        $instance = new static;
        return $instance->$method(...$args);
    }
}
