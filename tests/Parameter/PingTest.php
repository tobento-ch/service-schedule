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

namespace Tobento\Service\Schedule\Test\Parameter;

use PHPUnit\Framework\TestCase;
use Tobento\Service\Schedule\Parameter\Ping;
use Tobento\Service\Schedule\Parameter\AfterTaskHandler;
use Tobento\Service\Schedule\Parameter\BeforeTaskHandler;
use Tobento\Service\Schedule\Parameter\FailedTaskHandler;
use Tobento\Service\Schedule\ParameterInterface;
use Tobento\Service\Schedule\TaskResult;
use Tobento\Service\Schedule\Task;
use GuzzleHttp\Exception\ClientException;

class PingTest extends TestCase
{
    public function testThatImplementsInterfaces()
    {
        $param = new Ping(uri: 'https://example.com/task');
        
        $this->assertInstanceof(ParameterInterface::class, $param);
        $this->assertInstanceof(AfterTaskHandler::class, $param);
        $this->assertInstanceof(BeforeTaskHandler::class, $param);
        $this->assertInstanceof(FailedTaskHandler::class, $param);
    }

    public function testGetNameMethod()
    {
        $param = new Ping(uri: 'https://example.com/task');
        
        $this->assertSame(Ping::class, $param->getName());
    }
    
    public function testGetPriorityMethod()
    {
        $param = new Ping(uri: 'https://example.com/task');
        
        $this->assertSame(0, $param->getPriority());
    }
    
    public function testSpecificMethods()
    {
        $param = new Ping(
            uri: 'https://example.com/task',
            method: 'POST',
            options: ['key' => 'value'],
        );

        $this->assertSame('https://example.com/task', $param->getUri());
        $this->assertSame('POST', $param->getMethod());
        $this->assertSame(['key' => 'value'], $param->getOptions());
    }
    
    public function testPingBefore()
    {
        $param = new Ping(uri: 'https://example.com/task');
        
        $task = (new Task\CallableTask(function() {}));
        
        try {
            $param->getBeforeTaskHandler()($task);
        } catch (ClientException $e) {
            //
        }
        
        $this->assertTrue(true);
    }
    
    public function testPingAfter()
    {
        $param = new Ping(uri: 'https://example.com/task');
        
        $task = (new Task\CallableTask(function() {}));
        $result = new TaskResult(task: $task);
        
        try {
            $param->getAfterTaskHandler()($result);
        } catch (ClientException $e) {
            //
        }
        
        $this->assertTrue(true);
    }
    
    public function testPingFailed()
    {
        $param = new Ping(uri: 'https://example.com/task');
        
        $task = (new Task\CallableTask(function() {}));
        $result = new TaskResult(task: $task);
        
        try {
            $param->getFailedTaskHandler()($result);
        } catch (ClientException $e) {
            //
        }
        
        $this->assertTrue(true);
    }
}