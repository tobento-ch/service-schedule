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
use Tobento\Service\Schedule\Parameter\SendResultTo;
use Tobento\Service\Schedule\Parameter\AfterTaskHandler;
use Tobento\Service\Schedule\Parameter\FailedTaskHandler;
use Tobento\Service\Schedule\ParameterInterface;
use Tobento\Service\Schedule\TaskResult;
use Tobento\Service\Schedule\Task;
use Tobento\Service\Filesystem\Dir;
use Tobento\Service\Filesystem\File;

class SendResultToTest extends TestCase
{
    public function testThatImplementsInterfaces()
    {
        $param = new SendResultTo(file: __DIR__.'/../log/file.log');
        
        $this->assertInstanceof(ParameterInterface::class, $param);
        $this->assertInstanceof(AfterTaskHandler::class, $param);
        $this->assertInstanceof(FailedTaskHandler::class, $param);
    }

    public function testGetNameMethod()
    {
        $param = new SendResultTo(file: __DIR__.'/../log/file.log');
        
        $this->assertSame(SendResultTo::class, $param->getName());
    }
    
    public function testGetPriorityMethod()
    {
        $param = new SendResultTo(file: __DIR__.'/../log/file.log');
        $this->assertSame(0, $param->getPriority());
    }
    
    public function testSpecificMethods()
    {
        $param = new SendResultTo(file: __DIR__.'/../log/file.log');

        $this->assertSame(__DIR__.'/../log/file.log', $param->getFile());
    }
    
    public function testSendingResultAfter()
    {
        $param = new SendResultTo(file: __DIR__.'/../log/file.log');
        
        $task = (new Task\CallableTask(function() {
            return 'task output';
        }))->id('foo')->name('Foo')->description('Lorem');
        
        $result = new TaskResult(task: $task, output: 'task output');
        
        $param->getAfterTaskHandler()($result);
        
        $content = (new File(__DIR__.'/../log/file.log'))->getContent();
        
        $this->assertStringContainsString('Task Time:', $content);
        $this->assertStringContainsString('Task ID:', $content);
        $this->assertStringContainsString('foo', $content);
        $this->assertStringContainsString('Task Name:', $content);
        $this->assertStringContainsString('Foo', $content);
        $this->assertStringContainsString('Task Description:', $content);
        $this->assertStringContainsString('Lorem', $content);
        $this->assertStringContainsString('Task Output:', $content);
        $this->assertStringContainsString('task output', $content);
        
        (new Dir())->delete(__DIR__.'/../log/');
    }
    
    public function testSendingResultFailed()
    {
        $param = new SendResultTo(file: __DIR__.'/../log/file.log');
        
        $task = (new Task\CallableTask(function() {
            return 'task output';
        }))->id('foo')->name('Foo')->description('Lorem');
        
        $result = new TaskResult(task: $task, output: 'task output', exception: new \Exception('message'));
        
        $param->getFailedTaskHandler()($result);
        
        $content = (new File(__DIR__.'/../log/file.log'))->getContent();
        
        $this->assertStringContainsString('Task Time:', $content);
        $this->assertStringContainsString('Task ID:', $content);
        $this->assertStringContainsString('foo', $content);
        $this->assertStringContainsString('Task Name:', $content);
        $this->assertStringContainsString('Foo', $content);
        $this->assertStringContainsString('Task Description:', $content);
        $this->assertStringContainsString('Lorem', $content);
        $this->assertStringContainsString('Task Exception:', $content);
        $this->assertStringContainsString('Stack trace', $content);
        $this->assertStringContainsString('Task Output:', $content);
        $this->assertStringContainsString('task output', $content);
        
        (new Dir())->delete(__DIR__.'/../log/');
    }
}