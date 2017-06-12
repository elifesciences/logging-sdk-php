<?php

namespace eLife\Logging;

use Monolog\Formatter\JsonFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Processor\ProcessIdProcessor;

class LoggingFactory
{
    private $filesPath;
    private $loggingChannel;
    private $level;

    public function __construct(string $filesPath, $loggingChannel = 'default', $level = Logger::DEBUG)
    {
        $this->filesPath = realpath($filesPath).'/';
        $this->loggingChannel = $loggingChannel;
        $this->level = $level;
    }

    public function logger()
    {
        $logger = new Logger($this->loggingChannel);

        $stream = new StreamHandler($this->filesPath.'all.json', $this->level);
        $stream->pushProcessor(new ProcessIdProcessor());
        $stream->setFormatter(new JsonFormatter());
        $logger->pushHandler($stream);

        $stream = new StreamHandler($this->filesPath.'error.json', Logger::ERROR);
        $stream->pushProcessor(new ProcessIdProcessor());
        $detailedFormatter = new JsonFormatter();
        $detailedFormatter->includeStacktraces();
        $stream->setFormatter($detailedFormatter);
        $logger->pushHandler($stream);

        return $logger;
    }
}
