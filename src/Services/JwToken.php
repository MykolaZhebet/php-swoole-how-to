<?php

namespace App\Services;

use App\Models\Token;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Psr\Http\Message\ServerRequestInterface as Request;
class JwToken
{
    const HS256_ALGORITHM  = 'HS256';

    public static function decodeToken(string $token, string $name): array {
        $decoded = JWT::decode($token, new Key($name, self::HS256_ALGORITHM));
        return (array)$decoded;
    }

    public static function getToken(Request $request): ?Token {
        if(!$request->hasHeader('Authorization')) {
            return null;
        }

        $authorization = explode(' ', current($request->getHeader('Authorization')));

        if($authorization[0] !== 'Bearer') {
            return null;
        }

        $token = $authorization[1];

        return Token::where('token', $token)->first();
    }
}