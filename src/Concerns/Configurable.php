<?php

namespace Bubasuma\RequestLogger\Concerns;

use Bubasuma\RequestLogger\MessageFormatter;

trait Configurable
{
    public function isEnabled()
    {
        return config('request-logger.enabled', false);
    }

    public function isMethodExcluded($method)
    {
        $methods = config('request-logger.exclude.methods', ['GET', 'HEAD', 'OPTIONS']);

        return !empty($methods) && in_array(strtolower($method), array_map('strtolower', $methods));
    }

    public function isPathExcluded($path)
    {
        $patterns = config('request-logger.exclude.paths', []);

        return !empty($patterns) &&
            !is_null(
                array_first(
                    $patterns,
                    function ($pattern) use ($path) {
                        return str_is($pattern, $path);
                    }
                )
            );
    }

    public function shouldQueue()
    {
        return config('request-logger.should_queue', false);
    }

    public function queueName()
    {
        return config('request-logger.queue_name');
    }

    public function queueConnection()
    {
        return config('request-logger.queue_connection');
    }

    public function logType()
    {
        $default = 'daily';

        if (app()->bound('config')) {
            return config('request-logger.log_type', $default);
        }

        return $default;
    }

    public function logLevel()
    {
        $default = 'debug';

        if (app()->bound('config')) {
            return config('request-logger.log_level', $default);
        }

        return $default;
    }

    public function logMaxFiles()
    {
        $default = 5;

        if (app()->bound('config')) {
            return config('request-logger.log_max_files', $default);
        }

        return $default;
    }

    public function logFile()
    {
        $default = storage_path('logs/http.log');

        if (app()->bound('config')) {
            return config('request-logger.log_file', $default);
        }

        return $default;
    }

    public function logFormat()
    {
        $default = MessageFormatter::DEBUG;

        if (app()->bound('config')) {
            return config('request-logger.log_format', $default);
        }

        return $default;
    }
}
