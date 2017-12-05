<?php

namespace eLife\Logging;

use Monolog\Formatter\JsonFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Processor\ProcessIdProcessor;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

final class LoggingFactory
{
    const STDERR = 'php://stderr';
    private $filesPath;
    private $loggingChannel;
    private $level;

    public function __construct(string $filesPath = null, string $loggingChannel = 'default', string $level = LogLevel::DEBUG)
    {
        $this->filesPath = rtrim($filesPath, '/');
        $this->loggingChannel = $loggingChannel;
        $this->level = $level;
    }

    public static function stderr($loggingChannel = 'default', string $level = LogLevel::DEBUG)
    {
        return new self(null, $loggingChannel, $level);
    }

    public function logger() : LoggerInterface
    {
        $logger = new Logger($this->loggingChannel);

        if ($this->filesPath) {
            $stream = new StreamHandler($this->filesPath.'/all.json', $this->level);
            $stream->pushProcessor(new ProcessIdProcessor());
            $stream->setFormatter(new JsonFormatter());
            $logger->pushHandler($stream);

            $stream = new StreamHandler($this->filesPath.'/error.json', LogLevel::ERROR);
            $stream->pushProcessor(new ProcessIdProcessor());
            $detailedFormatter = new JsonFormatter();
            $detailedFormatter->includeStacktraces();
            $stream->setFormatter($detailedFormatter);
            $logger->pushHandler($stream);
        } else {
            $stream = new StreamHandler(self::STDERR, $this->level);
            $stream->pushProcessor(new ProcessIdProcessor());
            $detailedFormatter = new JsonFormatter();
            $detailedFormatter->includeStacktraces();
            $stream->setFormatter($detailedFormatter);
            $logger->pushHandler($stream);
        }

        return $logger;
    }
}
