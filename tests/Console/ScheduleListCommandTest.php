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
use Tobento\Service\Schedule\Console\ScheduleListCommand;
use Tobento\Service\Schedule\Schedule;
use Tobento\Service\Schedule\ScheduleInterface;
use Tobento\Service\Schedule\Task;
use Tobento\Service\Container\Container;
use Tobento\Service\Clock\SystemClock;
use Psr\Clock\ClockInterface;

class ScheduleListCommandTest extends TestCase
{
    public function testCommand()
    {
        $schedule = new Schedule(name: 'default');
        $schedule->task(
            (new Task\CallableTask(
                callable: static function (): string {
                    return 'foo';
                },
            ))->id('foo')->name('Foo')->description('Foo Desc')
        );
        
        $container = new Container();
        $container->set(ScheduleInterface::class, $schedule);
        $container->set(ClockInterface::class, new SystemClock());
        
        $task = $schedule->getTask(id: 'foo');
        $nextDue = $task->getSchedule()
            ->getNextRunDate($container->get(ClockInterface::class)->now())
            ->format('Y-m-d H:i:s P');
        
        (new TestCommand(
            command: ScheduleListCommand::class,
        ))
        ->expectsOutput('Schedule Name: default')
        ->expectsTable(
            headers: ['ID', 'Name', 'Description', 'Next Due'],
            rows: [
                ['foo', 'Foo', 'Foo Desc', $nextDue],
            ],
        )
        ->expectsExitCode(0)
        ->execute($container);
    }
    
    public function testWithoutTasks()
    {
        $container = new Container();
        $container->set(ScheduleInterface::class, new Schedule(name: 'default'));
        $container->set(ClockInterface::class, new SystemClock());
        
        (new TestCommand(
            command: ScheduleListCommand::class,
        ))
        ->expectsOutput('No scheduled tasks registered.')
        ->expectsExitCode(0)
        ->execute($container);
    }
}