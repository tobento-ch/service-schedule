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
use Tobento\Service\Schedule\ScheduleProcessor;
use Tobento\Service\Schedule\ScheduleProcessorInterface;
use Tobento\Service\Schedule\Schedule;
use Tobento\Service\Schedule\TaskResultsInterface;
use Tobento\Service\Schedule\TaskProcessor;
use Tobento\Service\Schedule\Task;
use Tobento\Service\Schedule\Event;
use Tobento\Service\Schedule\TaskSkipException;
use Tobento\Service\Container\Container;
use Tobento\Service\Event\Events;

class ScheduleProcessorTest extends TestCase
{
    public function testThatImplementsScheduleProcessorInterface()
    {
        $taskProcessor = new TaskProcessor(container: new Container());
        
        $this->assertInstanceof(ScheduleProcessorInterface::class, new ScheduleProcessor($taskProcessor));
    }

    public function testEventDispatcherMethod()
    {
        $taskProcessor = new TaskProcessor(container: new Container());
        
        $processor = new ScheduleProcessor($taskProcessor);
        $this->assertNull($processor->eventDispatcher());
        
        $eventDispatcher = new Events();
        $processor = new ScheduleProcessor($taskProcessor, $eventDispatcher);
        
        $this->assertSame($eventDispatcher, $processor->eventDispatcher());
    }
    
    public function testProcessScheduleWithoutAnyTask()
    {
        $taskProcessor = new TaskProcessor(container: new Container());
        $processor = new ScheduleProcessor($taskProcessor);
        $schedule = new Schedule(name: 'default');
        
        $results = $processor->processSchedule(schedule: $schedule, now: new \DateTime());
        $this->assertInstanceof(TaskResultsInterface::class, $results);
        $this->assertCount(0, $results);
        $this->assertCount(0, $results->successful());
        $this->assertCount(0, $results->failed());
        $this->assertCount(0, $results->skipped());
    }
    
    public function testProcessesDueTasks()
    {
        $taskProcessor = new TaskProcessor(container: new Container());
        $processor = new ScheduleProcessor($taskProcessor);
        $schedule = new Schedule(name: 'default');
        $schedule->task((new Task\CallableTask(function() {}))->id('task1'));
        $schedule->task((new Task\CallableTask(function() {}))->id('task2')->cron('30 * * * *'));
        $schedule->task((new Task\CallableTask(function() {}))->id('task3')->cron('15 * * * *'));
        
        $results = $processor->processSchedule(schedule: $schedule, now: new \DateTime('2023-11-14 16:15'));
        $this->assertCount(2, $results);
        $this->assertCount(2, $results->successful());
        $this->assertCount(0, $results->failed());
        $this->assertCount(0, $results->skipped());
    }
    
    public function testProcessesDueTasksWithDifferentTimezones()
    {
        $taskProcessor = new TaskProcessor(container: new Container());
        $processor = new ScheduleProcessor($taskProcessor);
        $schedule = new Schedule(name: 'default');
        $schedule->task((new Task\CallableTask(function() {}))->id('task1')->cron('30 15 * * *', 'Europe/London'));
        
        $results = $processor->processSchedule(
            schedule: $schedule,
            now: new \DateTime('2023-11-14 15:30', new \DateTimeZone('Europe/Berlin'))
        );
        
        $this->assertCount(0, $results);
        
        $results = $processor->processSchedule(
            schedule: $schedule,
            now: new \DateTime('2023-11-14 16:30', new \DateTimeZone('Europe/Berlin'))
        );
        
        $this->assertCount(1, $results);
    }
    
    public function testProcessesDueTasksWithFailures()
    {
        $taskProcessor = new TaskProcessor(container: new Container());
        $processor = new ScheduleProcessor($taskProcessor);
        $schedule = new Schedule(name: 'default');
        $schedule->task((new Task\CallableTask(function() {}))->id('task1'));
        $schedule->task((new Task\CallableTask(function() {}))->id('task2')->cron('30 * * * *'));
        $schedule->task((new Task\CallableTask(function() {$test();}))->id('task3')->cron('15 * * * *'));
        
        $results = $processor->processSchedule(schedule: $schedule, now: new \DateTime('2023-11-14 16:15'));
        $this->assertCount(2, $results);
        $this->assertCount(1, $results->successful());
        $this->assertCount(1, $results->failed());
        $this->assertCount(0, $results->skipped());
    }
    
    public function testProcessesDueTasksWithSkipped()
    {
        $taskProcessor = new TaskProcessor(container: new Container());
        $processor = new ScheduleProcessor($taskProcessor);
        $schedule = new Schedule(name: 'default');
        $schedule->task((new Task\CallableTask(function() {}))->id('task1'));
        $schedule->task((new Task\CallableTask(function() {}))->id('task2')->cron('30 * * * *'));
        $schedule->task((new Task\CallableTask(function() { throw new TaskSkipException(); }))->id('task3')->cron('15 * * * *'));
        
        $results = $processor->processSchedule(schedule: $schedule, now: new \DateTime('2023-11-14 16:15'));
        $this->assertCount(2, $results);
        $this->assertCount(1, $results->successful());
        $this->assertCount(0, $results->failed());
        $this->assertCount(1, $results->skipped());
    }
    
    public function testEventsShouldBeDispatched()
    {
        $container = new Container();
        $taskProcessor = new TaskProcessor(container: $container);
        
        $events = new Events();
        $events->listen(function(Event\ScheduleStarting $event) use ($container) {
            $container->set('schedule.starting', $event);
        });
        $events->listen(function(Event\ScheduleFinished $event) use ($container) {
            $container->set('schedule.finished', $event);
        });
        $events->listen(function(Event\TaskStarting $event) use ($container) {
            $container->set('task.starting', $event);
        });
        $events->listen(function(Event\TaskFinished $event) use ($container) {
            $container->set('task.finished', $event);
        });
        
        $processor = new ScheduleProcessor($taskProcessor, $events);
        $schedule = new Schedule(name: 'default');
        $task = (new Task\CallableTask(function() {}))->id('task1');
        $schedule->task($task);
        
        $results = $processor->processSchedule(schedule: $schedule, now: new \DateTime());
        $this->assertCount(1, $results);
        $this->assertSame($schedule, $container->get('schedule.starting')->schedule());
        $this->assertSame($schedule, $container->get('schedule.finished')->schedule());
        $this->assertSame($results, $container->get('schedule.finished')->results());
        $this->assertSame($task, $container->get('task.starting')->task());
        $this->assertSame($results->all()[0], $container->get('task.finished')->result());
    }
}