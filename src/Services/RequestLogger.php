<?php

namespace Bubasuma\RequestLogger\Services;

use Bubasuma\RequestLogger\Concerns\Configurable;
use Bubasuma\RequestLogger\MessageFormatter;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequestLogger
{
    use Configurable;

    protected $formatter;

    public function log(Request $request, Response $response)
    {
        $this->getLogger()
             ->log(
                 $this->getLogLevel($request, $response),
                 $this->getLogMessage($request, $response),
                 $this->getLogContext($request, $response)
             );
    }

    /**
     * @return \Psr\Log\LoggerInterface
     */
    protected function getLogger()
    {
        return app()->make('request-logger');
    }

    protected function getLogMessage(Request $request, Response $response)
    {
        return $this->getMessageFormatter()->format($request, $response);
    }

    public function getLogLevel(Request $request, Response $response)
    {
        if ($response->isSuccessful()) {
            return $this->logLevel();
        }

        return 'error';
    }

    public function getLogContext(Request $request, Response $response)
    {
        $context = [];

        if ($request->user()) {
            $context = array_merge(
                $context,
                ['userId' => $request->user()->getKey(), 'userClass' => get_class($request->user())]
            );
        }

        return $context;
    }

    public function getMessageFormatter()
    {
        if (is_null($this->formatter)) {
            $this->formatter = new MessageFormatter($this->logFormat());
        }

        return $this->formatter;
    }
}
