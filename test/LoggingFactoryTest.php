<?php

namespace tests\eLife\Logging;

use eLife\Logging\LoggingFactory;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

final class LoggingFactoryTest extends TestCase
{
    /**
     * @test
     */
    public function it_creates_a_logger()
    {
        $factory = new LoggingFactory(__DIR__, 'my-application', LogLevel::INFO);
        $logger = $factory->logger();
        $this->assertInstanceOf(LoggerInterface::class, $logger);
    }
}
