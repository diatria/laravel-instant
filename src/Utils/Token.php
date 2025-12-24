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

    function __construct()
    {
        $this->cookieName = env("LI_COOKIE_NAME", "laravel-instant");
    }

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
        $this->setToken(
            $accessToken,
            "access",
            config("laravel-instant.cookies.expires", 3600),
        );
        $this->setToken(
            $refreshToken,
            "refresh",
            config("laravel-instant.auth.token_refresh_expires", 21600),
        );

        return [
            "access_token" => $accessToken,
            "refresh_token" => $refreshToken,
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

    protected function getAccessToken()
    {

        if (isset($_COOKIE[$this->getAccessTokenName()])) {
            return $_COOKIE[$this->getAccessTokenName()];
        } else {
            // Access token masih bisa fallback ke bearer
            return request()->bearerToken() ??
                throw new \ErrorException("Access Token Not Found!", 401);
        }
    }

    protected function getRefreshToken()
    {
        if (isset($_COOKIE[$this->getRefreshTokenName()])) {
            return $_COOKIE[$this->getRefreshTokenName()];
        } else {
            throw new \ErrorException("Refresh Token Not Found!", 401);
        }
    }

    protected function logout()
    {
        // hapus access token cookie
        setcookie(
            $this->getAccessTokenName(),
            "",
            time() - 3600,
            "/",
        );

        // hapus refresh token cookie
        setcookie(
            $this->getRefreshTokenName(),
            "",
            time() - 3600,
            "/",
        );
    }

    protected function setConfig(array $config = [])
    {
        if (isset($config["secret_key"])) {
            $this->secretKey = $config["secret_key"];
        }

        if (isset($config["cookie_name"])) {
            $this->cookieName = $config["cookie_name"];
        }

        return $this;
    }

    /**
     * Melakukan set token ke cookies
     */
    protected function setToken(
        string $token,
        string $type = "access",
        int $expired = 3600,
    ): bool {
        $cookieName = $type === "refresh" ? $this->getRefreshTokenName() : $this->getAccessTokenName();
        $domain = Helper::getDomain(
            config("laravel-instant.cookies.domain"),
            request()->domain ?? null,
            ["port" => false],
        );

        return setcookie($cookieName, $token, [
            "expires" => Carbon::now()->addSeconds($expired)->getTimestamp(),
            "path" => config("laravel-instant.cookies.path", "/"),
            "domain" => config("laravel-instant.cookies.domain", $domain),
            "secure" => config("laravel-instant.cookies.secure", false),
            "httponly" => config("laravel-instant.cookies.httponly", true),
            "samesite" => config("laravel-instant.cookies.samesite", "none"),
        ]);
    }

    /**
     * Verify token
     *
     * @param string|null $token
     * @return array
     */
    protected function verification(?string $token = null): array
    {
        try {
            if ($token) {
                $decoded = JWT::decode(
                    $token,
                    new Key($this->getSecretKey(), "HS256"),
                );
                return Helper::toArray($decoded) ?? [];
            }

            $decoded = JWT::decode(
                $this->getAccessToken(),
                new Key($this->getSecretKey(), "HS256"),
            );
            return Helper::toArray($decoded) ?? [];
        } catch (ExpiredException $e) {
            try {
                $decoded = JWT::decode(
                    $this->getRefreshToken(),
                    new Key($this->getSecretKey(), "HS256"),
                );
                if ($decoded) {
                    return $this->create(Helper::toArray($decoded));
                }
            } catch (ErrorException $e) {
                throw new ErrorException("Token verification failed", 401);
            }

            throw new ErrorException("Token has expired", 401);
        } catch (\Exception $e) {
            throw new ErrorException("Token verification failed", 401);
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
        $instance = new static();
        return $instance->$method(...$args);
    }
}
