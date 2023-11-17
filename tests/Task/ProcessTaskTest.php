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
use Tobento\Service\Schedule\Task\ProcessTask;
use Tobento\Service\Schedule\TaskInterface;
use Tobento\Service\Schedule\TaskScheduleInterface;
use Tobento\Service\Schedule\ParametersInterface;
use Tobento\Service\Schedule\Parameter;
use Tobento\Service\Container\Container;
use Symfony\Component\Process\Process;

class ProcessTaskTest extends TestCase
{
    public function testThatImplementsTaskInterface()
    {
        $this->assertInstanceof(TaskInterface::class, new ProcessTask('/foo/bar'));
    }

    public function testInterfaceMethods()
    {
        $task = new ProcessTask('/foo/bar');
        $this->assertTrue(strlen($task->getId()) > 10);
        $this->assertSame('/foo/bar', $task->getName());
        $this->assertSame('', $task->getDescription());
        $this->assertInstanceof(TaskScheduleInterface::class, $task->getSchedule());
    }
    
    public function testParameterMethods()
    {
        $task = new ProcessTask('/foo/bar');
        $task->parameter(new Parameter\Monitor());
        $this->assertInstanceof(ParametersInterface::class, $task->parameters());
        $this->assertSame(1, count($task->parameters()->all()));
    }
    
    public function testHelperMethods()
    {
        $task = (new ProcessTask('/foo/bar'))
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
        $task = new ProcessTask('/foo/bar');
        $this->assertInstanceof(Process::class, $task->getProcess());
    }
    
    public function testProcessTaskMethod()
    {
        $container = new Container();
        
        $task = new ProcessTask('echo foo');
        
        $result = $task->processTask($container);
        
        $this->assertSame($task, $result->task());
        $this->assertTrue($result->isSuccessful());
        $this->assertStringContainsString('foo', $result->output());
    }
    
    public function testProcessTaskMethodProcessFailed()
    {
        $container = new Container();
        
        $task = new ProcessTask('/foo/bar');
        
        $result = $task->processTask($container);
        
        $this->assertSame($task, $result->task());
        $this->assertTrue($result->isFailure());
        $this->assertIsString($result->output());
        $this->assertNotNull($result->exception());
    }
}