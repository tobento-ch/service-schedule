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
use Tobento\Service\Schedule\Parameter\Monitor;
use Tobento\Service\Schedule\Parameter\AfterTaskHandler;
use Tobento\Service\Schedule\Parameter\BeforeTaskHandler;
use Tobento\Service\Schedule\Parameter\FailedTaskHandler;
use Tobento\Service\Schedule\ParameterInterface;
use Tobento\Service\Schedule\TaskResult;
use Tobento\Service\Schedule\Task;

class MonitorTest extends TestCase
{
    public function testThatImplementsInterfaces()
    {
        $param = new Monitor();
        
        $this->assertInstanceof(ParameterInterface::class, $param);
        $this->assertInstanceof(AfterTaskHandler::class, $param);
        $this->assertInstanceof(BeforeTaskHandler::class, $param);
        $this->assertInstanceof(FailedTaskHandler::class, $param);
    }

    public function testGetNameMethod()
    {
        $param = new Monitor();
        
        $this->assertSame(Monitor::class, $param->getName());
    }
    
    public function testGetPriorityMethod()
    {
        $param = new Monitor();
        $this->assertSame(1000000, $param->getPriority());
    }
    
    public function testMonitoring()
    {
        $param = new Monitor();
        
        $task = (new Task\CallableTask(function() {
            return 'task output';
        }));
        
        $param->getBeforeTaskHandler()($task);
        
        $this->assertSame(0, $param->runtimeInSeconds());
        $this->assertSame(0, $param->memoryUsage());
        
        $result = new TaskResult(task: $task);
        
        $param->getAfterTaskHandler()($result);
        
        $this->assertTrue($param->runtimeInSeconds() > 0);
        $this->assertTrue($param->memoryUsage() >= 0);
    }
    
    public function testMonitoringFailedTask()
    {
        $param = new Monitor();
        
        $task = (new Task\CallableTask(function() {
            return 'task output';
        }));
        
        $param->getBeforeTaskHandler()($task);
        
        $this->assertSame(0, $param->runtimeInSeconds());
        $this->assertSame(0, $param->memoryUsage());
        
        $result = new TaskResult(task: $task);
        
        $param->getFailedTaskHandler()($result);
        
        $this->assertTrue($param->runtimeInSeconds() > 0);
        $this->assertTrue($param->memoryUsage() >= 0);
    }
}