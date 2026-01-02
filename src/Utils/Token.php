<?php

namespace Diatria\LaravelInstant\Utils;

use Carbon\Carbon;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

/**
 * Class Token
 *
 * Static helper annotations to improve IDE autocompletion when calling
 * methods via Token::method(). Only public instance methods can be
 * proxied statically at runtime.
 *
 * @method static \Diatria\LaravelInstant\Utils\Token authConfig()
 * @method static array create(array $payload)
 * @method static array|mixed verification(?string $token = null)
 * @method static void logout()
 * @method static \Diatria\LaravelInstant\Utils\Token setSecretKey(string $secretKey)
 */
class Token
{
    /**
     * Nama cookie untuk menyimpan access token.
     */
    private string $accessTokenName;

    /**
     * Nama cookie untuk menyimpan refresh token.
     */
    private string $refreshTokenName;

    /**
     * Durasi kedaluwarsa access token dalam detik.
     * 3600 → 1 jam
     */
    private int $accessTokenExpires;

    /**
     * Durasi kedaluwarsa refresh token dalam detik.
     * 21600 → 6 jam
     */
    private int $refreshTokenExpires;

    /**
     * Domain tempat cookie akan disimpan.
     * Contoh: .example.com
     */
    private ?string $domain = null;

    /**
     * Issuer (iss) pada JWT.
     * Mengidentifikasi aplikasi / auth server penerbit token
     */
    private string $issuer;

    /**
     * Secret key untuk signing dan verifikasi JWT.
     */
    private ?string $secretKey = null;

    /**
     * Algoritma kriptografi JWT.
     * RS256 | HS256
     */
    private $algorithm;

    public function __construct()
    {
        $this->authConfig();
    }

    /**
     * Load auth config
     *
     * @return void
     */
    public function authConfig()
    {
        $this->accessTokenName = config('laravel-instant.auth.access_token_name', 'laravel_instant');
        $this->refreshTokenName = config('laravel-instant.auth.refresh_token_name', 'laravel_instant');
        $this->accessTokenExpires = config('laravel-instant.auth.access_token_expires', 3600);
        $this->refreshTokenExpires = config('laravel-instant.auth.refresh_token_expires', 21600);
        $this->issuer = config('app.name', 'laravel_instant');
        $this->secretKey = config('laravel-instant.auth.secret_key');
        $this->algorithm = config('laravel-instant.auth.algorithm', 'HS256');
        $this->domain = config('laravel-instant.cookies.domain', '');

        return $this;
    }

    public function create(array $payload)
    {
        return [
            'access_token' => $this->generateAccessToken($payload),
            'refresh_token' => $this->generateRefreshToken($payload),
        ];
    }

    /**
     * Create access token
     *
     * @param  array  $payload  isi dari data yang di enkripsi
     */
    public function generateAccessToken(array $payload)
    {
        $token = JWT::encode(
            [
                'iss' => $this->issuer, // Issuer (pihak yang mengeluarkan token)
                'exp' => Carbon::now()->addSeconds($this->accessTokenExpires)->getTimestamp(), // Expiration time (waktu kadaluarsa token)
                'iat' => Carbon::now()->getTimestamp(), // Issued at time (waktu token dikeluarkan)
                ...$payload,
            ],
            $this->secretKey,
            $this->algorithm,
        );

        /**
         * Melakukan set cookie
         */
        $this->setTokenCookie($token, $this->accessTokenName, $this->accessTokenExpires);

        return $token;
    }

    /**
     * Create refresh token
     *
     * @param  array  $payload  isi dari data yang di enkripsi
     */
    public function generateRefreshToken(array $payload)
    {
        $token = JWT::encode(
            [
                'iss' => $this->issuer, // Issuer (pihak yang mengeluarkan token)
                'exp' => Carbon::now()->addSeconds($this->refreshTokenExpires)->getTimestamp(), // Expiration time (waktu kadaluarsa token)
                'iat' => Carbon::now()->getTimestamp(), // Issued at time (waktu token dikeluarkan)
                ...$payload,
            ],
            $this->secretKey,
            $this->algorithm,
        );

        /**
         * Melakukan set cookie
         */
        $this->setTokenCookie($token, $this->refreshTokenName, $this->refreshTokenExpires);

        return $token;
    }

    /**
     * Get access token from cookies or bearer token
     *
     * @return string
     */
    public function getAccessToken()
    {
        // Return cookies token
        if (isset($_COOKIE[$this->accessTokenName])) {
            return $_COOKIE[$this->accessTokenName];
        }

        // Return default ke bearer token
        $token = request()->bearerToken();
        if (! $token) {
            throw new \ErrorException('Access Token Not Found!', 401);
        }

        return $token;
    }

    /**
     * Get refresh token from cookies
     *
     * @return string
     */
    public function getRefreshToken()
    {
        if (isset($_COOKIE[$this->refreshTokenName])) {
            return $_COOKIE[$this->refreshTokenName];
        } else {
            throw new \ErrorException('Refresh Token Not Found!', 401);
        }
    }

    /**
     * Logout user by deleting token cookies
     *
     * @return void
     */
    public function logout()
    {
        // hapus access token cookie
        setcookie(
            $this->accessTokenName,
            '',
            time() - 3600,
            '/',
            $this->domain,
            config('laravel-instant.cookies.secure', true),
            config('laravel-instant.cookies.httponly', true)
        );

        // hapus refresh token cookie
        setcookie(
            $this->refreshTokenName,
            '',
            time() - 3600,
            '/',
            $this->domain,
            config('laravel-instant.cookies.secure', true),
            config('laravel-instant.cookies.httponly', true)
        );
    }

    public function setDomain(string $domain)
    {
        $this->domain = $domain;

        return $this;
    }

    public function setSecretKey(string $secretKey)
    {
        $this->secretKey = $secretKey;

        return $this;
    }

    /**
     * Melakukan set token ke cookies
     */
    public function setTokenCookie(string $token, string $tokenName, int $expired = 3600): bool
    {
        $domain = Helper::getDomain($this->domain, request()->getHost() ?? null, ['port' => false]);

        return setcookie($tokenName, $token, [
            'expires' => Carbon::now()->addSeconds($expired)->getTimestamp(),
            'path' => config('laravel-instant.cookies.path', '/'),
            'domain' => config('laravel-instant.cookies.domain', $domain),
            'secure' => config('laravel-instant.cookies.secure', true),
            'httponly' => config('laravel-instant.cookies.httponly', true),
            'samesite' => config('laravel-instant.cookies.samesite', 'none'),
        ]);
    }

    /**
     * Verifikasi token
     *
     * @return array
     */
    public function verification(?string $token = null)
    {
        try {
            // Verifikasi string token
            if ($token) {
                $decoded = JWT::decode(
                    $token,
                    new Key($this->secretKey, $this->algorithm),
                );

                return Helper::toArray($decoded) ?? [];
            }

            // Verifikasi token dari cookies
            $decoded = JWT::decode(
                $this->getAccessToken(),
                new Key($this->secretKey, $this->algorithm),
            );

            return Helper::toArray($decoded) ?? [];
        } catch (ExpiredException $e) {
            try {
                $decoded = JWT::decode(
                    $this->getRefreshToken(),
                    new Key($this->secretKey, $this->algorithm),
                );
                if ($decoded) {
                    return $this->create(Helper::toArray($decoded));
                }
            } catch (ErrorException $e) {
                throw new ErrorException('Token verification failed, can`t find Access Token or Refresh Token', 401);
            }

            throw new ErrorException('Token has expired', 401);
        } catch (\Exception $e) {
            throw new ErrorException('Token verification failed', 401);
        }
    }
}