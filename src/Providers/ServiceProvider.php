<?php

namespace Bubasuma\RequestLogger\Providers;

use Bubasuma\RequestLogger\Concerns\Configurable;
use Monolog\Logger as Monolog;
use Illuminate\Log\LogServiceProvider;
use Illuminate\Log\Writer;

class ServiceProvider extends LogServiceProvider
{
    use Configurable;

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('request-logger', function () {
            return $this->createLogger();
        });
    }

    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'../../config/request-logger.php' => config_path('request-logger.php'),
        ]);
    }

    /**
     * Create the logger.
     *
     * @return Writer
     */
    public function createLogger()
    {
        $log = new Writer(new Monolog($this->channel()));

        $this->configureHandler($log);

        return $log;
    }

    /**
     * {@inheritdoc}
     */
    protected function configureSingleHandler(Writer $log)
    {
        $log->useFiles($this->logFile(), $this->logLevel());
    }

    /**
     * {@inheritdoc}
     */
    protected function configureDailyHandler(Writer $log)
    {
        $log->useDailyFiles($this->logFile(), $this->maxFiles(), $this->logLevel());
    }

    /**
     * {@inheritdoc}
     */
    protected function handler()
    {
        return $this->logType();
    }

    /**
     * {@inheritdoc}
     */
    protected function maxFiles()
    {
        return $this->logMaxFiles();
    }
}
