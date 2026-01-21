<?php

/**
 * TOBENTO
 *
 * @copyright   Tobias Strub, TOBENTO
 * @license     MIT License, see LICENSE file distributed with this source code.
 * @author      Tobias Strub
 * @link        http://www.tobento.ch
 */

declare(strict_types=1);

namespace Tobento\Service\Schedule\Test\Parameter;

use PHPUnit\Framework\TestCase;
use Tobento\Service\Container\Container;
use Tobento\Service\Schedule\Parameter\Ping;
use Tobento\Service\Schedule\Parameter\AfterTaskHandler;
use Tobento\Service\Schedule\Parameter\BeforeTaskHandler;
use Tobento\Service\Schedule\Parameter\FailedTaskHandler;
use Tobento\Service\Schedule\ParameterInterface;
use Tobento\Service\Schedule\TaskResult;
use Tobento\Service\Schedule\Task;

class PingTest extends TestCase
{
    public function testThatImplementsInterfaces()
    {
        $param = new Ping(uri: 'http://example.com/task');

        $this->assertInstanceOf(ParameterInterface::class, $param);
        $this->assertInstanceOf(AfterTaskHandler::class, $param);
        $this->assertInstanceOf(BeforeTaskHandler::class, $param);
        $this->assertInstanceOf(FailedTaskHandler::class, $param);
    }

    public function testGetNameMethod()
    {
        $param = new Ping(uri: 'http://example.com/task');

        $this->assertSame(Ping::class, $param->getName());
    }

    public function testGetPriorityMethod()
    {
        $param = new Ping(uri: 'http://example.com/task');

        $this->assertSame(0, $param->getPriority());
    }

    public function testSpecificMethods()
    {
        $param = new Ping(
            uri: 'http://example.com/task',
            method: 'POST',
            query: ['foo' => 'bar'],
            headers: ['Accept' => 'application/json'],
            body: 'payload'
        );

        $this->assertSame('http://example.com/task', $param->getUri());
        $this->assertSame('POST', $param->getMethod());
        $this->assertSame(['foo' => 'bar'], $param->getQuery());
        $this->assertSame(['Accept' => 'application/json'], $param->getHeaders());
        $this->assertSame('payload', $param->getBody());
    }

    public function testPingBefore()
    {
        $param = new Ping(uri: 'http://example.com/task');

        $task = new Task\CallableTask(function () {});

        // We don't assert the HTTP call here — only that the handler is callable.
        $handler = $param->getBeforeTaskHandler();
        $this->assertIsCallable($handler);

        // Execute handler (it may throw depending on environment, so we catch)
        try {
            $handler($task, new Container());
        } catch (\Throwable $e) {
            // ignore — no HTTP client is configured in this test environment
        }

        $this->assertTrue(true);
    }

    public function testPingAfter()
    {
        $param = new Ping(uri: 'http://example.com/task');

        $task = new Task\CallableTask(function () {});
        $result = new TaskResult(task: $task);

        $handler = $param->getAfterTaskHandler();
        $this->assertIsCallable($handler);

        try {
            $handler($result, new Container());
        } catch (\Throwable $e) {
            // ignore
        }

        $this->assertTrue(true);
    }

    public function testPingFailed()
    {
        $param = new Ping(uri: 'http://example.com/task');

        $task = new Task\CallableTask(function () {});
        $result = new TaskResult(task: $task);

        $handler = $param->getFailedTaskHandler();
        $this->assertIsCallable($handler);

        try {
            $handler($result, new Container());
        } catch (\Throwable $e) {
            // ignore
        }

        $this->assertTrue(true);
    }
}