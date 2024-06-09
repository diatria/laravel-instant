<?php
namespace App\Utils;

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
            $decoded = JWT::decode(
                $_COOKIE["csl_token"],
                new Key(env("JWT_KEY"), "HS256")
            );
            return $decoded ? true : false;
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

    public static function info(): array
    {
        try {
            // Verifikasi Token
            $decoded = JWT::decode(
                $_COOKIE["csl_token"],
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
