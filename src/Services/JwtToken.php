<?php

namespace App\Services;

use App\Models\Token;
use Carbon\Carbon;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Psr\Http\Message\ServerRequestInterface as Request;
class JwtToken
{
    const HS256_ALGORITHM  = 'HS256';

    public static function create(
        string $name,
        int $userId,
        ?int $expire,
        ?int $useLimit
    ): Token {
        //@see https://datatracker.ietf.org/doc/html/rfc7519
        $payload = [
            'iat' => Carbon::now()->timestamp, //Issued At
            'user_id' => $userId,
        ];

        if($expire) {
            $expire = Carbon::now()->addSeconds($expire);
            $payload['exp'] =  $expire->timestamp; //Expiration Time
//            $payload['expire_at'] =  $expire->format(); //Expiration Time
        }

        if($useLimit) {
            $payload['use_limit'] = $useLimit;
        }


        $token = JWT::encode($payload, $name, JwtToken::HS256_ALGORITHM);

        $tokenRecord = Token::create([
            'name' => $name,
            'user_id' => $userId,
            'expire_at' => $expire ? $expire->format('Y-m-d H:i:s'): null,
            'token' => $token,
        ]);

        return $tokenRecord;
    }

    public static function decodeToken(string $token, string $name): array {
        $decoded = JWT::decode($token, new Key($name, self::HS256_ALGORITHM));
        return (array)$decoded;
    }

    public static function getToken(Request $request): ?Token {
        global $app;
        if(!$request->hasHeader('Authorization')) {
            return null;
        }

        $authorization = explode(' ', current($request->getHeader('Authorization')));

        if($authorization[0] !== 'Bearer') {
            return null;
        }

        $token = $authorization[1];
        try {
            $tokenRecord = Token::where('token', $token)->first()->consume();
        } catch (\Exception $e) {
            $app->getContainer()->get('logger')->error('Invalid token: ' . $e->getMessage());
            return null;
        }
        return $tokenRecord;
    }
}