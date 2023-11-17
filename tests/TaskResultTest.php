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

namespace Tobento\Service\Schedule\Test;

use PHPUnit\Framework\TestCase;
use Tobento\Service\Schedule\TaskResult;
use Tobento\Service\Schedule\TaskResultInterface;
use Tobento\Service\Schedule\Task;
use Tobento\Service\Schedule\TaskSkipException;
use Tobento\Service\Schedule\TaskException;

class TaskResultTest extends TestCase
{
    public function testThatImplementsTaskResultInterface()
    {
        $result = new TaskResult(task: new Task\CallableTask(function() {}));
        $this->assertInstanceof(TaskResultInterface::class, $result);
    }
    
    public function testTaskMethod()
    {
        $task = new Task\CallableTask(function() {});
        $result = new TaskResult(task: $task);
        $this->assertSame($task, $result->task());
    }
    
    public function testOutputMethod()
    {
        $task = new Task\CallableTask(function() {});
        $this->assertSame('', (new TaskResult(task: $task))->output());
        $this->assertSame('foo', (new TaskResult(task: $task, output: 'foo'))->output());
    }
    
    public function testExceptionMethod()
    {
        $task = new Task\CallableTask(function() {});
        $this->assertNull((new TaskResult(task: $task))->exception());
        $exception = new \Exception();
        $this->assertSame($exception, (new TaskResult(task: $task, exception: $exception))->exception());
    }
    
    public function testIsSuccessfulMethod()
    {
        $task = new Task\CallableTask(function() {});
        
        $this->assertTrue((new TaskResult(task: $task))->isSuccessful());
        $this->assertFalse((new TaskResult(task: $task, exception: new \Exception()))->isSuccessful());
    }
    
    public function testIsFailureMethod()
    {
        $task = new Task\CallableTask(function() {});
        
        $this->assertFalse((new TaskResult(task: $task))->isFailure());
        $this->assertFalse((new TaskResult(task: $task, exception: new TaskSkipException()))->isFailure());
        $this->assertTrue((new TaskResult(task: $task, exception: new TaskException()))->isFailure());
        $this->assertTrue((new TaskResult(task: $task, exception: new \Exception()))->isFailure());
    }
    
    public function testIsSkippedMethod()
    {
        $task = new Task\CallableTask(function() {});
        
        $this->assertFalse((new TaskResult(task: $task))->isSkipped());
        $this->assertTrue((new TaskResult(task: $task, exception: new TaskSkipException()))->isSkipped());
        $this->assertFalse((new TaskResult(task: $task, exception: new TaskException()))->isSkipped());
        $this->assertFalse((new TaskResult(task: $task, exception: new \Exception()))->isSkipped());
    }
}