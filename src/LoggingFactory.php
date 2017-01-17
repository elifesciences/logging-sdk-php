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

    public function __construct(string $filesPath, $loggingChannel = 'default')
    {
        $this->filesPath = realpath($filesPath).'/';
        $this->loggingChannel = $loggingChannel;
    }
    
    public function logger()
    {
        $logger = new Logger($this->loggingChannel);

        $stream = new StreamHandler($this->filesPath.'all.json', Logger::DEBUG);
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
