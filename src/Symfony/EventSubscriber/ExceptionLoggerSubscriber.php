<?php

namespace eLife\Logging\Symfony\EventSubscriber;

use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Throwable;

final class ExceptionLoggerSubscriber implements EventSubscriberInterface
{
    public function __construct(private readonly LoggerInterface $logger) {
        //
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 0],
            KernelEvents::EXCEPTION => ['onKernelException', 0],
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        $this->logger->info(sprintf('> %s %s', $request->getMethod(), $request->getRequestUri()));
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $throwable = $event->getThrowable();
        $level = $this->resolveLevel($throwable);

        $this->logger->log($level, $throwable->getMessage(), ['exception' => $throwable]);
    }

    private function resolveLevel(Throwable $throwable): string
    {
        if ($throwable instanceof HttpExceptionInterface && $throwable->getStatusCode() < 500) {
            return LogLevel::INFO;
        }

        // Optional elife/api-problem integration
        if (
            class_exists(\eLife\ApiProblem\ApiProblemException::class)
            && $throwable instanceof \eLife\ApiProblem\ApiProblemException
            && $throwable->getApiProblem()->getStatus() < 500
        ) {
            return LogLevel::INFO;
        }

        return LogLevel::ERROR;
    }
}
