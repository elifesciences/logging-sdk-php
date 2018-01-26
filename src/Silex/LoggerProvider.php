<?php

namespace eLife\Logging\Silex;

use eLife\ApiProblem\ApiProblemException;
use eLife\Logging\LoggingFactory;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Psr\Log\LogLevel;
use Silex\Api\BootableProviderInterface;
use Silex\Application;
use Silex\EventListener\LogListener;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

final class LoggerProvider implements BootableProviderInterface, ServiceProviderInterface
{
    public function boot(Application $app)
    {
        if (isset($app['logger.listener'])) {
            $app['dispatcher']->addSubscriber($app['logger.listener']);
        }
    }

    public function register(Container $app)
    {
        $app['logger.factory'] = function () use ($app) {
            return new LoggingFactory($app['logger.path'], $app['logger.channel'], $app['logger.level']);
        };

        $app['logger'] = function () use ($app) {
            return $app['logger.factory']->logger();
        };

        $app['logger.listener'] = function () use ($app) {
            return new LogListener($app['logger'], function (Throwable $e) {
                if (
                    $e instanceof HttpExceptionInterface && $e->getStatusCode() < 500
                    ||
                    $e instanceof ApiProblemException && $e->getApiProblem()->getStatus() < 500
                ) {
                    return LogLevel::INFO;
                }

                return LogLevel::CRITICAL;
            });
        };
    }
}
