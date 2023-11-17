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
use Tobento\Service\Schedule\Task\CommandTask;
use Tobento\Service\Schedule\TaskInterface;
use Tobento\Service\Schedule\TaskScheduleInterface;
use Tobento\Service\Schedule\ParametersInterface;
use Tobento\Service\Schedule\Parameter;
use Tobento\Service\Container\Container;
use Tobento\Service\Console\Symfony;
use Tobento\Service\Console\ConsoleInterface;
use Tobento\Service\Console\InteractorInterface;
use Tobento\Service\Console\Command;

class CommandTaskTest extends TestCase
{
    public function testThatImplementsTaskInterface()
    {
        $this->assertInstanceof(TaskInterface::class, new CommandTask('command:name'));
    }

    public function testInterfaceMethods()
    {
        $task = new CommandTask('command:name');
        $this->assertTrue(strlen($task->getId()) > 10);
        $this->assertSame('command:name', $task->getName());
        $this->assertSame('', $task->getDescription());
        $this->assertInstanceof(TaskScheduleInterface::class, $task->getSchedule());
    }
    
    public function testParameterMethods()
    {
        $task = new CommandTask('command:name');
        $task->parameter(new Parameter\Monitor());
        $this->assertInstanceof(ParametersInterface::class, $task->parameters());
        $this->assertSame(1, count($task->parameters()->all()));
    }
    
    public function testHelperMethods()
    {
        $task = (new CommandTask('command:name'))
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
        $task = new CommandTask(command: 'command:name', input: ['--opt' => 'foo']);
        $this->assertSame('command:name', $task->getCommand());
        $this->assertSame(['--opt' => 'foo'], $task->getInput());
        $this->assertSame(null, $task->getConsole());
    }
    
    public function testProcessTaskMethod()
    {
        $container = new Container();
        
        $console = new Symfony\Console(name: 'app', container: $container);
        
        $command = (new Command(name: 'foo'))
            ->handle(function(InteractorInterface $io): int {
                $io->write('output');
                return 0;
            });
        
        $console->addCommand($command);
        
        $container->set(ConsoleInterface::class, $console);
        
        $task = new CommandTask('foo');
        
        $result = $task->processTask($container);
        
        $this->assertSame($task, $result->task());
        $this->assertTrue($result->isSuccessful());
        $this->assertSame('output', $result->output());
        $this->assertSame($console, $task->getConsole());
    }
    
    public function testProcessTaskMethodRetursFailedResultIfCommandFails()
    {
        $container = new Container();
        
        $console = new Symfony\Console(name: 'app', container: $container);
        
        $command = (new Command(name: 'foo'))
            ->handle(function(InteractorInterface $io): int {
                $io->write('output');
                return 1;
            });
        
        $console->addCommand($command);
        
        $container->set(ConsoleInterface::class, $console);
        
        $task = new CommandTask('foo');
        
        $result = $task->processTask($container);
        
        $this->assertSame($task, $result->task());
        $this->assertTrue($result->isFailure());
        $this->assertSame('output', $result->output());
        $this->assertSame('Command task failed with code 1', $result->exception()?->getMessage());
        $this->assertSame($console, $task->getConsole());
    }
}