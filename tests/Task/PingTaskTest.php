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

use PHPUnit\Framework\TestCase;
use Tobento\Service\Schedule\Task\PingTask;
use Tobento\Service\Schedule\TaskInterface;
use Tobento\Service\Schedule\TaskScheduleInterface;
use Tobento\Service\Schedule\ParametersInterface;
use Tobento\Service\Schedule\Parameter;
use Tobento\Service\Container\Container;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;

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
        $task = new PingTask(
            uri: 'https://example.com/task',
            method: 'POST',
            options: ['key' => 'value'],
        );
        
        $this->assertSame('https://example.com/task', $task->getUri());
        $this->assertSame('POST', $task->getMethod());
        $this->assertSame(['key' => 'value'], $task->getOptions());
        $this->assertSame(null, $task->getResponse());
    }
    
    public function testProcessTaskMethod()
    {
        $container = new Container();
        
        $mock = new MockHandler([
            new Response(200, ['X-Foo' => 'Bar'], 'Hello, World'),
        ]);

        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);
        
        $container->set(ClientInterface::class, $client);
        
        $task = new PingTask(uri: '/');
        
        $result = $task->processTask($container);
        
        $this->assertSame($task, $result->task());
        $this->assertTrue($result->isSuccessful());
        $this->assertSame('Hello, World', $result->output());
        $this->assertSame('Hello, World', (string)$task->getResponse()?->getBody());
    }
}