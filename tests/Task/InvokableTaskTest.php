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
use Tobento\Service\Schedule\Test\Mock;
use Tobento\Service\Schedule\Task\InvokableTask;
use Tobento\Service\Schedule\TaskInterface;
use Tobento\Service\Schedule\TaskScheduleInterface;
use Tobento\Service\Schedule\ParametersInterface;
use Tobento\Service\Schedule\Parameter;
use Tobento\Service\Container\Container;

class InvokableTaskTest extends TestCase
{
    public function testThatImplementsTaskInterface()
    {
        $task = new class() extends InvokableTask {
            public function __invoke(Mock\Foo $foo): string
            {
                return 'output';
            }
        };
        
        $this->assertInstanceof(TaskInterface::class, $task);
    }

    public function testInterfaceMethods()
    {
        $task = new class() extends InvokableTask {
            public function __invoke(Mock\Foo $foo): string
            {
                return 'output';
            }
        };
        
        $this->assertTrue(strlen($task->getId()) > 10);
        $this->assertTrue(strlen($task->getName()) > 10);
        $this->assertSame('', $task->getDescription());
        $this->assertInstanceof(TaskScheduleInterface::class, $task->getSchedule());
    }
    
    public function testParameterMethods()
    {
        $task = new class() extends InvokableTask {
            public function __invoke(Mock\Foo $foo): string
            {
                return 'output';
            }
        };
        
        $task->parameter(new Parameter\Monitor());
        $this->assertInstanceof(ParametersInterface::class, $task->parameters());
        $this->assertSame(1, count($task->parameters()->all()));
    }
    
    public function testHelperMethods()
    {
        $task = (new class() extends InvokableTask {
            public function __invoke(Mock\Foo $foo): string
            {
                return 'output';
            }
        })
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
    
    public function testProcessTaskMethod()
    {
        $container = new Container();
        
        $task = new class() extends InvokableTask {
            public function __invoke(Mock\Foo $foo): string
            {
                return 'output';
            }
        };
        
        $result = $task->processTask($container);
        
        $this->assertSame($task, $result->task());
        $this->assertTrue($result->isSuccessful());
        $this->assertSame('output', $result->output());
    }
    
    public function testProcessTaskMethodWithoutOutput()
    {
        $container = new Container();
        
        $task = new class() extends InvokableTask {
            public function __invoke(Mock\Foo $foo): void
            {
                //
            }
        };
        
        $result = $task->processTask($container);
        
        $this->assertSame($task, $result->task());
        $this->assertTrue($result->isSuccessful());
        $this->assertSame('', $result->output());
    }
}