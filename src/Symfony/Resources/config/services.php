<?php

use eLife\Logging\LoggingFactory;
use eLife\Logging\Symfony\EventSubscriber\ExceptionLoggerSubscriber;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set(LoggingFactory::class)
        ->args([
            '%elife_logging.path%',
            '%elife_logging.channel%',
            '%elife_logging.level%',
        ]);

    $services->set('elife.logger', LoggerInterface::class)
        ->factory([service(LoggingFactory::class), 'logger']);

    $services->set(ExceptionLoggerSubscriber::class)
        ->args([service('elife.logger')])
        ->tag('kernel.event_subscriber');
};
