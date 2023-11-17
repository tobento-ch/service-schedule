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

namespace Tobento\Service\Schedule\Test\Console;

use PHPUnit\Framework\TestCase;
use Tobento\Service\Console\Test\TestCommand;
use Tobento\Service\Schedule\Console\ScheduleRunCommand;
use Tobento\Service\Schedule\Schedule;
use Tobento\Service\Schedule\ScheduleInterface;
use Tobento\Service\Schedule\Task;
use Tobento\Service\Schedule\Task\Schedule\Dates;
use Tobento\Service\Schedule\TaskProcessor;
use Tobento\Service\Schedule\TaskProcessorInterface;
use Tobento\Service\Schedule\ScheduleProcessor;
use Tobento\Service\Schedule\ScheduleProcessorInterface;
use Tobento\Service\Schedule\TaskSkipException;
use Tobento\Service\Container\Container;
use Tobento\Service\Clock\SystemClock;
use Psr\Clock\ClockInterface;

class ScheduleRunCommandTest extends TestCase
{
    public function testHandleScheduleTaskSuccess()
    {
        $schedule = new Schedule(name: 'default');
        $schedule->task(
            (new Task\CallableTask(
                callable: static function (): string {
                    return 'foo';
                },
            ))->id('foo')->name('Foo')
        );
        
        $container = new Container();
        $container->set(ScheduleInterface::class, $schedule);
        $container->set(TaskProcessorInterface::class, TaskProcessor::class);
        $container->set(ScheduleProcessorInterface::class, ScheduleProcessor::class);
        $container->set(ClockInterface::class, new SystemClock());
        
        (new TestCommand(
            command: ScheduleRunCommand::class,
        ))
        ->expectsOutputToContain('Schedule default starting')
        ->expectsTable(
            headers: ['Successful', 'Failed', 'Skipped'],
            rows: [
                [1, 0, 0],
            ],
        )
        ->expectsOutputToContain('Success: task Foo with the id foo')
        ->expectsOutputToContain('Schedule default finished')
        ->expectsExitCode(0)
        ->execute($container);
    }
    
    public function testHandleScheduleTaskFailed()
    {
        $schedule = new Schedule(name: 'default');
        $schedule->task(
            (new Task\CallableTask(
                callable: static function (): string {
                    throw new \Exception('message');
                },
            ))->id('foo')->name('Foo')
        );
        
        $container = new Container();
        $container->set(ScheduleInterface::class, $schedule);
        $container->set(TaskProcessorInterface::class, TaskProcessor::class);
        $container->set(ScheduleProcessorInterface::class, ScheduleProcessor::class);
        $container->set(ClockInterface::class, new SystemClock());
        
        (new TestCommand(
            command: ScheduleRunCommand::class,
        ))
        ->expectsOutputToContain('Schedule default starting')
        ->expectsTable(
            headers: ['Successful', 'Failed', 'Skipped'],
            rows: [
                [0, 1, 0],
            ],
        )
        ->expectsOutputToContain('Failed: task Foo with the id foo')
        ->expectsOutputToContain('Schedule default finished')
        ->expectsExitCode(1)
        ->execute($container);
    }
    
    public function testHandleScheduleTaskSkipped()
    {
        $schedule = new Schedule(name: 'default');
        $schedule->task(
            (new Task\CallableTask(
                callable: static function (): string {
                    return 'foo';
                },
            ))->id('foo')->name('Foo')->before(function () {
                throw new TaskSkipException();
            })
        );
        
        $container = new Container();
        $container->set(ScheduleInterface::class, $schedule);
        $container->set(TaskProcessorInterface::class, TaskProcessor::class);
        $container->set(ScheduleProcessorInterface::class, ScheduleProcessor::class);
        $container->set(ClockInterface::class, new SystemClock());
        
        (new TestCommand(
            command: ScheduleRunCommand::class,
        ))
        ->expectsOutputToContain('Schedule default starting')
        ->expectsTable(
            headers: ['Successful', 'Failed', 'Skipped'],
            rows: [
                [0, 0, 1],
            ],
        )
        ->expectsOutputToContain('Skipped: task Foo with the id foo')
        ->expectsOutputToContain('Schedule default finished')
        ->expectsExitCode(0)
        ->execute($container);
    }
    
    public function testHandleScheduleWithNoDueTasks()
    {
        $schedule = new Schedule(name: 'default');
        $schedule->task(
            (new Task\CallableTask(
                callable: static function (): string {
                    return 'foo';
                },
            ))
            ->id('foo')
            ->name('Foo')
            ->schedule(new Dates(new \DateTime('2023-05-12 15:38:45')))
        );
        
        $container = new Container();
        $container->set(ScheduleInterface::class, $schedule);
        $container->set(TaskProcessorInterface::class, TaskProcessor::class);
        $container->set(ScheduleProcessorInterface::class, ScheduleProcessor::class);
        $container->set(ClockInterface::class, new SystemClock());
        
        (new TestCommand(
            command: ScheduleRunCommand::class,
        ))
        ->expectsOutput('Schedule default starting')
        ->expectsOutput('No scheduled tasks are ready to run.')
        ->expectsOutput('Schedule default finished')
        ->expectsExitCode(0)
        ->execute($container);
    }
    
    public function testHandleIdTaskSuccess()
    {
        $schedule = new Schedule(name: 'default');
        $schedule->task(
            (new Task\CallableTask(
                callable: static function (): string {
                    return 'foo';
                },
            ))->id('foo')->name('Foo')
        );
        
        $container = new Container();
        $container->set(ScheduleInterface::class, $schedule);
        $container->set(TaskProcessorInterface::class, TaskProcessor::class);
        $container->set(ScheduleProcessorInterface::class, ScheduleProcessor::class);
        $container->set(ClockInterface::class, new SystemClock());
        
        (new TestCommand(
            command: ScheduleRunCommand::class,
            input: ['--id' => ['foo']],
        ))
        ->expectsOutputToContain('Success: task Foo with the id foo')
        ->expectsExitCode(0)
        ->execute($container);
    }
    
    public function testHandleIdTaskFailed()
    {
        $schedule = new Schedule(name: 'default');
        $schedule->task(
            (new Task\CallableTask(
                callable: static function (): string {
                    throw new \Exception('message');
                },
            ))->id('foo')->name('Foo')
        );
        
        $container = new Container();
        $container->set(ScheduleInterface::class, $schedule);
        $container->set(TaskProcessorInterface::class, TaskProcessor::class);
        $container->set(ScheduleProcessorInterface::class, ScheduleProcessor::class);
        $container->set(ClockInterface::class, new SystemClock());
        
        (new TestCommand(
            command: ScheduleRunCommand::class,
            input: ['--id' => ['foo']],
        ))
        ->expectsOutputToContain('Failed: task Foo with the id foo')
        ->expectsExitCode(1)
        ->execute($container);
    }
    
    public function testHandleIdTaskSkipped()
    {
        $schedule = new Schedule(name: 'default');
        $schedule->task(
            (new Task\CallableTask(
                callable: static function (): string {
                    return 'foo';
                },
            ))->id('foo')->name('Foo')->before(function () {
                throw new TaskSkipException();
            })
        );
        
        $container = new Container();
        $container->set(ScheduleInterface::class, $schedule);
        $container->set(TaskProcessorInterface::class, TaskProcessor::class);
        $container->set(ScheduleProcessorInterface::class, ScheduleProcessor::class);
        $container->set(ClockInterface::class, new SystemClock());
        
        (new TestCommand(
            command: ScheduleRunCommand::class,
            input: ['--id' => ['foo']],
        ))
        ->expectsOutputToContain('Skipped: task Foo with the id foo')
        ->expectsExitCode(0)
        ->execute($container);
    }
    
    public function testHandleIdTaskNotFound()
    {
        $schedule = new Schedule(name: 'default');        
        $container = new Container();
        $container->set(ScheduleInterface::class, $schedule);
        $container->set(TaskProcessorInterface::class, TaskProcessor::class);
        $container->set(ScheduleProcessorInterface::class, ScheduleProcessor::class);
        $container->set(ClockInterface::class, new SystemClock());
        
        (new TestCommand(
            command: ScheduleRunCommand::class,
            input: ['--id' => ['foo']],
        ))
        ->expectsOutputToContain('Task with the id foo not found')
        ->expectsExitCode(1)
        ->execute($container);
    }
}