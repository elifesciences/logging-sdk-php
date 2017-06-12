<?php

namespace tests\eLife\Logging;

use eLife\Logging\LoggingFactory;
use Monolog\Logger;
use Psr\Log\LoggerInterface;

class LoggingFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testCreatingALogger()
    {
        $factory = new LoggingFactory(__DIR__, 'my-application', Logger::INFO);
        $logger = $factory->logger();
        $this->assertInstanceOf(LoggerInterface::class, $logger);
    }
}
