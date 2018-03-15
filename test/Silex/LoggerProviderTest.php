<?php

namespace tests\eLife\Logging\Silex;

use Crell\ApiProblem\ApiProblem;
use eLife\ApiProblem\ApiProblemException;
use eLife\Logging\Silex\LoggerProvider;
use Exception;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Silex\Application;
use Silex\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Traversable;

final class LoggerProviderTest extends WebTestCase
{
    /** @var Application */
    protected $app;
    /** @var vfsStreamDirectory */
    private $logs;

    /**
     * @test
     * @dataProvider serviceProvider
     */
    public function it_creates_services(string $id, string $class)
    {
        $this->assertArrayHasKey($id, $this->app);
        $this->assertInstanceOf($class, $this->app[$id]);
    }

    public function serviceProvider() : Traversable
    {
        $services = [
            'logger' => LoggerInterface::class,
        ];

        foreach ($services as $id => $type) {
            yield $id => [$id, $type];
        }
    }

    /**
     * @test
     */
    public function it_logs_api_problems()
    {
        $client = $this->createClient();

        $client->request('GET', '/api-problem');

        $this->assertTrue($this->logs->hasChild('all.json'));
        $this->assertFalse($this->logs->hasChild('error.json'));

        $this->assertSame(ApiProblemException::class, $this->fetchLogs('all.json')[1]['context']['exception']['class']);
    }

    /**
     * @test
     */
    public function it_logs_exceptions()
    {
        $client = $this->createClient();

        $client->request('GET', '/exception');

        $this->assertTrue($this->logs->hasChild('all.json'));
        $this->assertTrue($this->logs->hasChild('error.json'));

        $this->assertSame(Exception::class, $this->fetchLogs('all.json')[1]['context']['exception']['class']);
        $this->assertSame(Exception::class, $this->fetchLogs('error.json')[0]['context']['exception']['class']);
    }

    /**
     * @test
     */
    public function it_logs_http_exceptions()
    {
        $client = $this->createClient();

        $client->request('GET', '/http-exception');

        $this->assertTrue($this->logs->hasChild('all.json'));
        $this->assertFalse($this->logs->hasChild('error.json'));

        $this->assertSame(HttpException::class, $this->fetchLogs('all.json')[1]['context']['exception']['class']);
    }

    public function createApplication() : Application
    {
        $this->logs = vfsStream::setup('logs');

        $app = new Application();
        $app['logger.channel'] = 'app';
        $app['logger.level'] = LogLevel::INFO;
        $app['logger.path'] = vfsStream::url($this->logs->getName());

        $app->register(new LoggerProvider());

        $app->get('/api-problem', function () {
            $apiProblem = new ApiProblem('api problem');
            $apiProblem->setStatus(Response::HTTP_I_AM_A_TEAPOT);

            throw new ApiProblemException($apiProblem);
        });

        $app->get('/exception', function () {
            throw new Exception('exception');
        });

        $app->get('/http-exception', function () {
            throw new HttpException(Response::HTTP_I_AM_A_TEAPOT, 'http problem');
        });

        $app->boot();
        $app->flush();

        return $app;
    }

    private function fetchLogs(string $name) : array
    {
        $logs = [];

        $handle = fopen($this->logs->getChild($name)->url(), 'r');
        while (false !== ($line = fgets($handle))) {
            $logs[] = json_decode($line, true);
        }
        fclose($handle);

        return $logs;
    }
}
