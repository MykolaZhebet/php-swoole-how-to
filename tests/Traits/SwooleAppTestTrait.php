<?php

namespace Tests\Traits;

use Nekofar\Slim\Test\Traits\AppTestTrait;
use Nekofar\Slim\Test\TestResponse;
use Fig\Http\Message\RequestMethodInterface;
trait SwooleAppTestTrait
{
    use AppTestTrait;

    public function PostJson(string $url, array $data, array $headers = []): TestResponse {
        $request = $this->createJsonRequest(RequestMethodInterface::METHOD_POST, $url, $data);
        $request->getBody()->rewind();
        return $this->send($request, $headers);
    }
    public function PutJson(string $url, array $data, array $headers = []): TestResponse {
        $request = $this->createJsonRequest(RequestMethodInterface::METHOD_PUT, $url, $data);
        $request->getBody()->rewind();
        return $this->send($request, $headers);
    }


}