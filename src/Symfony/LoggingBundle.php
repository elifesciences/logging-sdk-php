<?php

namespace eLife\Logging\Symfony;

use eLife\Logging\Symfony\DependencyInjection\LoggingExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class LoggingBundle extends Bundle
{
    public function getContainerExtension(): LoggingExtension
    {
        return new LoggingExtension();
    }
}
