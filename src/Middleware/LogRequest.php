<?php

namespace Bubasuma\RequestLogger\Middleware;

use Bubasuma\RequestLogger\Concerns\Configurable;
use Closure;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LogRequest
{
    use DispatchesJobs, Configurable;

    public function handle(Request $request, Closure $next)
    {
        return $next($request);
    }

    public function terminate(Request $request, Response $response)
    {
        if (!$this->shouldLogRequest($request)) {
            return;
        }

        $job = new \Bubasuma\RequestLogger\Jobs\LogRequest($request, $response);
        if ($this->shouldQueue()) {
            $this->dispatch($job->onQueue($this->queueName())->onConnection($this->queueConnection()));
            return;
        }

        $this->dispatchNow($job);
    }

    protected function shouldLogRequest(Request $request)
    {
        return $this->isEnabled()
            && !$this->isMethodExcluded($request->getMethod())
            && !$this->isPathExcluded($request->decodedPath());
    }
}
