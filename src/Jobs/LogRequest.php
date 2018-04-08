<?php

namespace Bubasuma\RequestLogger\Jobs;

use Bubasuma\RequestLogger\Services\RequestLogger;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LogRequest implements ShouldQueue
{
    use Queueable;
    /**
     * @var Request
     */
    private $request;
    /**
     * @var Response
     */
    private $response;

    public function __construct(Request $request, Response $response)
    {
        $this->request = $request;
        $this->response = $response;
    }

    public function handle(RequestLogger $logger)
    {
        $logger->log($this->request, $this->response);
    }
}
