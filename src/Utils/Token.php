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
    public static function check()
    {
        try {
            // Verifikasi Token
            $cookieName = env("APP_TOKEN_NAME") . "_TOKEN";
            if (isset($_COOKIE[$cookieName])) {
                $decoded = JWT::decode(
                    $_COOKIE[$cookieName],
                    new Key(env("JWT_KEY"), "HS256")
                );
            }
            return isset($decoded) ? $decoded : false;
        } catch (SignatureInvalidException $e) {
            throw new ErrorException($e->getMessage(), 4001);
        } catch (ExpiredException $e) {
            throw new ErrorException($e->getMessage(), 4002);
        } catch (\Exception $e) {
            throw new ErrorException($e->getMessage(), $e->getCode());
        } catch (ErrorException $e) {
            throw new ErrorException($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Create token dan refresh token
     */
    public static function create($payload): array
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

    public static function getToken()
    {
        $bearer = request()->bearerToken();
        if ($bearer) {
            return $bearer;
        }

        if (isset($_COOKIE[env("APP_TOKEN_NAME") . "_token"])) {
            return $_COOKIE[env("APP_TOKEN_NAME") . "_token"];
        }
    }

    public static function info()
    {
        try {
            // Verifikasi Token
            $decoded = JWT::decode(
                self::getToken(),
                new Key(env("JWT_KEY"), "HS256")
            );
            return (array) $decoded;
        } catch (SignatureInvalidException $e) {
            return Response::error(4001, $e->getMessage());
        } catch (ExpiredException $e) {
            return Response::error(4002, $e->getMessage());
        } catch (\Exception $e) {
            return Response::error($e->getCode(), $e->getMessage());
        } catch (ErrorException $e) {
            return Response::error($e->getErrorCode(), $e->getMessage());
        }
    }
}
