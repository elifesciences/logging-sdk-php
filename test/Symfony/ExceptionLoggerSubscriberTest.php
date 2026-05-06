<?php

namespace tests\eLife\Logging\Symfony;

use Crell\ApiProblem\ApiProblem;
use eLife\ApiProblem\ApiProblemException;
use eLife\Logging\Symfony\EventSubscriber\ExceptionLoggerSubscriber;
use Exception;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;

final class ExceptionLoggerSubscriberTest extends TestCase
{
    private HttpKernelInterface $kernel;

    protected function setUp(): void
    {
        $this->kernel = $this->createStub(HttpKernelInterface::class);
    }

    #[Test]
    public function it_subscribes_to_the_correct_events(): void
    {
        $events = ExceptionLoggerSubscriber::getSubscribedEvents();

        $this->assertArrayHasKey('kernel.request', $events);
        $this->assertArrayHasKey('kernel.exception', $events);
    }

    #[Test]
    public function it_logs_incoming_requests(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())
            ->method('info')
            ->with($this->stringContains('GET'));

        $event = new RequestEvent($this->kernel, Request::create('/some-path', 'GET'), HttpKernelInterface::MAIN_REQUEST);
        (new ExceptionLoggerSubscriber($logger))->onKernelRequest($event);
    }

    #[Test]
    public function it_skips_sub_requests(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->never())->method('info');
        $logger->expects($this->never())->method('log');

        $event = new RequestEvent($this->kernel, Request::create('/sub', 'GET'), HttpKernelInterface::SUB_REQUEST);
        (new ExceptionLoggerSubscriber($logger))->onKernelRequest($event);
    }

    #[Test]
    public function it_logs_http_exceptions_below_500_as_info(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())
            ->method('log')
            ->with(LogLevel::INFO, $this->anything(), $this->anything());

        $event = new ExceptionEvent(
            $this->kernel,
            Request::create('/'),
            HttpKernelInterface::MAIN_REQUEST,
            new HttpException(Response::HTTP_NOT_FOUND, 'Not Found')
        );

        (new ExceptionLoggerSubscriber($logger))->onKernelException($event);
    }

    #[Test]
    public function it_logs_http_exceptions_500_and_above_as_error(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())
            ->method('log')
            ->with(LogLevel::ERROR, $this->anything(), $this->anything());

        $event = new ExceptionEvent(
            $this->kernel,
            Request::create('/'),
            HttpKernelInterface::MAIN_REQUEST,
            new HttpException(Response::HTTP_INTERNAL_SERVER_ERROR, 'Server Error')
        );

        (new ExceptionLoggerSubscriber($logger))->onKernelException($event);
    }

    #[Test]
    public function it_logs_generic_exceptions_as_error(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())
            ->method('log')
            ->with(LogLevel::ERROR, $this->anything(), $this->anything());

        $event = new ExceptionEvent(
            $this->kernel,
            Request::create('/'),
            HttpKernelInterface::MAIN_REQUEST,
            new Exception('something broke')
        );

        (new ExceptionLoggerSubscriber($logger))->onKernelException($event);
    }

    #[Test]
    public function it_logs_api_problem_exceptions_below_500_as_info(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())
            ->method('log')
            ->with(LogLevel::INFO, $this->anything(), $this->anything());

        $apiProblem = new ApiProblem('api problem');
        $apiProblem->setStatus(Response::HTTP_I_AM_A_TEAPOT);

        $event = new ExceptionEvent(
            $this->kernel,
            Request::create('/'),
            HttpKernelInterface::MAIN_REQUEST,
            new ApiProblemException($apiProblem)
        );

        (new ExceptionLoggerSubscriber($logger))->onKernelException($event);
    }
}
