<?php

namespace eLife\Logging\Silex;

use eLife\Logging\LoggingFactory;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

final class LoggerProvider implements ServiceProviderInterface
{
    public function register(Container $app)
    {
        $app['logger.factory'] = function () use ($app) {
            return new LoggingFactory($app['logger.path'], $app['logger.channel'], $app['logger.level']);
        };

        $app['logger'] = function () use ($app) {
            return $app['logger.factory']->logger();
        };
    }
}
