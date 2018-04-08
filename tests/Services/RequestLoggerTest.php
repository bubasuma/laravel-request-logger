<?php

namespace Bubasuma\RequestLogger\Tests\Services;

use Bubasuma\RequestLogger\MessageFormatter;
use Bubasuma\RequestLogger\Services\RequestLogger;
use Bubasuma\RequestLogger\Tests\TestCase;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Monolog\Handler\TestHandler;
use Monolog\Logger as Monolog;

class RequestLoggerTest extends TestCase
{
    public function testLog()
    {
        $handler = new TestHandler();
        $monolog = new Monolog('test');
        $monolog->pushHandler($handler);
        $service = $this->createPartialMock(RequestLogger::class, ['getLogger', 'getMessageFormatter']);
        $service->expects($this->any())
                ->method('getLogger')
                ->willReturn($monolog);

        $service->expects($this->any())
                ->method('getMessageFormatter')
                ->willReturn(new MessageFormatter('Hello World'));

        $request = $this->createMock(Request::class);
        $response = $this->createMock(Response::class);
        /** @var RequestLogger $service */
        $service->log($request, $response);

        $this->assertCount(1, $handler->getRecords());

        $log = $handler->getRecords()[0];
        $this->assertEquals('Hello World', $log['message']);
    }
}
