<?php

/**
 * TOBENTO
 *
 * @copyright   Tobias Strub, TOBENTO
 * @license     MIT License, see LICENSE file distributed with this source code.
 * @author      Tobias Strub
 * @link        https://www.tobento.ch
 */

declare(strict_types=1);

namespace Tobento\Service\Schedule\Test\Task;

use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Tobento\Service\Schedule\Task\PingTask;
use Tobento\Service\Schedule\TaskInterface;
use Tobento\Service\Schedule\TaskScheduleInterface;
use Tobento\Service\Schedule\ParametersInterface;
use Tobento\Service\Schedule\Parameter;
use Tobento\Service\Container\Container;

class PingTaskTest extends TestCase
{
    public function testThatImplementsTaskInterface()
    {
        $this->assertInstanceof(TaskInterface::class, new PingTask(uri: 'https://example.com/task'));
    }

    public function testInterfaceMethods()
    {
        $task = new PingTask(uri: 'https://example.com/task');
        $this->assertTrue(strlen($task->getId()) > 10);
        $this->assertSame('Ping: [GET] https://example.com/task', $task->getName());
        $this->assertSame('', $task->getDescription());
        $this->assertInstanceof(TaskScheduleInterface::class, $task->getSchedule());
    }
    
    public function testParameterMethods()
    {
        $task = new PingTask(uri: 'https://example.com/task');
        $task->parameter(new Parameter\Monitor());
        $this->assertInstanceof(ParametersInterface::class, $task->parameters());
        $this->assertSame(1, count($task->parameters()->all()));
    }
    
    public function testHelperMethods()
    {
        $task = (new PingTask(uri: 'https://example.com/task'))
            ->id('foo')
            ->name('Foo')
            ->description('Lorem')
            ->before(function () {})
            ->after(function () {})
            ->failed(function () {})
            ->skip(function () { return true; }, reason: 'reason')
            ->withoutOverlapping()
            ->monitor();
        
        $this->assertSame('foo', $task->getId());
        $this->assertSame('Foo', $task->getName());
        $this->assertSame('Lorem', $task->getDescription());
        $this->assertInstanceof(Parameter\Before::class, $task->parameters()->name(Parameter\Before::class)->first());
        $this->assertInstanceof(Parameter\After::class, $task->parameters()->name(Parameter\After::class)->first());
        $this->assertInstanceof(Parameter\Failed::class, $task->parameters()->name(Parameter\Failed::class)->first());
        $this->assertInstanceof(Parameter\Skip::class, $task->parameters()->name(Parameter\Skip::class)->first());
        $this->assertInstanceof(
            Parameter\WithoutOverlapping::class,
            $task->parameters()->name(Parameter\WithoutOverlapping::class)->first()
        );
        $this->assertInstanceof(Parameter\Monitor::class, $task->parameters()->name(Parameter\Monitor::class)->first());
    }

    public function testSpecificMethods()
    {
        $failure = function () {};
        
        $task = new PingTask(
            uri: 'http://example.com/task',
            method: 'POST',
            query: ['foo' => 'bar'],
            headers: ['Accept' => 'application/json'],
            body: 'payload',
            failure: $failure,
        );

        $this->assertSame('http://example.com/task', $task->getUri());
        $this->assertSame('POST', $task->getMethod());
        $this->assertSame(['foo' => 'bar'], $task->getQuery());
        $this->assertSame(['Accept' => 'application/json'], $task->getHeaders());
        $this->assertSame('payload', $task->getBody());
        $this->assertSame(null, $task->getResponse());
        $this->assertSame($failure, $task->getFailure());
    }
    
    public function testProcessTaskMethod()
    {
        $container = new Container();

        // PSR-18 client (Symfony)
        $client = new \Symfony\Component\HttpClient\Psr18Client(
            new \Symfony\Component\HttpClient\MockHttpClient([
                new \Symfony\Component\HttpClient\Response\MockResponse(
                    'Hello, World',
                    ['response_headers' => ['X-Foo' => 'Bar'], 'http_code' => 200]
                )
            ])
        );

        // Bind into container
        $container->set(ClientInterface::class, $client);
        $container->set(RequestFactoryInterface::class, new Psr17Factory());
        $container->set(StreamFactoryInterface::class, new Psr17Factory());

        // Create task
        $task = new PingTask(uri: '/');

        // Execute
        $result = $task->processTask($container);

        // Assertions
        $this->assertSame($task, $result->task());
        $this->assertTrue($result->isSuccessful());
        $this->assertSame('Hello, World', $result->output());
        $this->assertSame('Hello, World', (string)$task->getResponse()?->getBody());
    }
    
    public function testProcessTaskBuildsCorrectRequest()
    {
        $container = new Container();
        $client = new PingTaskSpyClient();

        $container->set(ClientInterface::class, $client);
        $container->set(RequestFactoryInterface::class, new Psr17Factory());
        $container->set(StreamFactoryInterface::class, new Psr17Factory());

        $task = new PingTask(
            uri: 'https://example.com/ping',
            method: 'POST',
            query: ['foo' => 'bar'],
            headers: ['Accept' => 'application/json'],
            body: 'payload'
        );

        $task->processTask($container);

        $request = $client->capturedRequest;

        $this->assertSame('POST', $request->getMethod());
        $this->assertSame('https://example.com/ping?foo=bar', (string)$request->getUri());
        $this->assertSame(['application/json'], $request->getHeader('Accept'));
        $this->assertSame('payload', (string)$request->getBody());
    }
    
    public function testProcessTaskFailsOnHttpErrorByDefault()
    {
        $container = new Container();

        $client = new \Symfony\Component\HttpClient\Psr18Client(
            new \Symfony\Component\HttpClient\MockHttpClient([
                new \Symfony\Component\HttpClient\Response\MockResponse(
                    'Error',
                    ['http_code' => 500]
                )
            ])
        );

        $container->set(ClientInterface::class, $client);
        $container->set(RequestFactoryInterface::class, new Psr17Factory());
        $container->set(StreamFactoryInterface::class, new Psr17Factory());

        $task = new PingTask(uri: '/');

        $result = $task->processTask($container);

        $this->assertFalse($result->isSuccessful());
        $this->assertInstanceOf(\Throwable::class, $result->exception());
        $this->assertSame('Error', $result->output());
    }
    
    public function testProcessTaskUsesCustomFailureCallback()
    {
        $container = new Container();

        $client = new \Symfony\Component\HttpClient\Psr18Client(
            new \Symfony\Component\HttpClient\MockHttpClient([
                new \Symfony\Component\HttpClient\Response\MockResponse(
                    'OK',
                    ['http_code' => 200]
                )
            ])
        );

        $container->set(ClientInterface::class, $client);
        $container->set(RequestFactoryInterface::class, new Psr17Factory());
        $container->set(StreamFactoryInterface::class, new Psr17Factory());

        $failure = function (ResponseInterface $res, PingTask $task): void {
            throw new \RuntimeException('custom-failure');
        };

        $task = new PingTask(uri: '/', failure: $failure);

        $result = $task->processTask($container);

        $this->assertFalse($result->isSuccessful());
        $this->assertSame('custom-failure', $result->exception()->getMessage());
    }
}

class PingTaskSpyClient implements ClientInterface
{
    public ?RequestInterface $capturedRequest = null;

    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        $this->capturedRequest = $request;
        return new \Nyholm\Psr7\Response(200, ['X-Foo' => 'Bar'], 'Hello, World');
    }
}