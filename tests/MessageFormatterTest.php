<?php

namespace Bubasuma\RequestLogger\Tests;

use Bubasuma\RequestLogger\MessageFormatter;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class MessageFormatterTest extends TestCase
{
    public function testFormat()
    {
        $formatter = new MessageFormatter('{method} {target} {code}');

        $request = $this->createMock(Request::class);
        $response = $this->createMock(Response::class);

        $request->expects($this->any())->method('getMethod')->willReturn('GET');
        $request->expects($this->any())->method('getRequestUri')->willReturn('/path/to/action');
        $response->expects($this->any())->method('getStatusCode')->willReturn(200);

        $this->assertEquals('GET /path/to/action 200', $formatter->format($request, $response));
    }
}
