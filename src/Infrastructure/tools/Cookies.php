<?php
#Inspired by https://github.com/lotharthesavior/chocookies but not supported now
namespace App\Infrastructure\tools;

use Carbon\Carbon;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class Cookies
{
    public static function setCookie(
        ResponseInterface|ServerRequestInterface &$serverObject,
        string $cookieKey,
        string $cookieValue,
        ?Carbon $expireHour = null
    ): void {
        if (null === $expireHour) {
            $expireHour = Carbon::now()->addHours(8)->format('D, j F Y H:i:s T');
        } else {
            $expireHour = $expireHour->format('D, j F Y H:i:s T');
        }

        $serverObject = $serverObject->withHeader(
            'Set-Cookie',
            $cookieKey . '=' . $cookieValue . '; Expires=' . $expireHour
        );
    }

    public static function getCookie(ResponseInterface|ServerRequestInterface $serverObject, string $key): ?string
    {
        $cookies = $serverObject->getCookieParams();

        if (!isset($cookies[$key])) {
            return null;
        }

        return $cookies[$key];
    }

    public static function expireCookie(
        ServerRequestInterface $request,
        ResponseInterface &$response,
        string $key
    ): void {
        if (!isset($request->getCookieParams()[$key])) {
            return;
        }

        $response = $response->withHeader(
            'Set-Cookie',
            self::getExpiredCookie($request, $key)
        );
    }

    public static function getExpiredCookie(ServerRequestInterface $request, string $key): string
    {
        $data = self::getCookie($request, $key);
        $expireHour = Carbon::now()->subHour(1)->format('D, j F Y H:i:s T');
        return $key . '=' . $data . '; Expires=' . $expireHour;
    }
}