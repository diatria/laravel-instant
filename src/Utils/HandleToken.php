<?php

namespace Diatria\LaravelInstant\Utils;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use App\Utils\Response;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\SignatureInvalidException;

class HandleToken
{
    public function verify(string $token): array
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
