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

namespace Tobento\Service\Schedule;

use Tobento\Service\Schedule\Event;
use Tobento\Service\Autowire\Autowire;
use Tobento\Service\Autowire\AutowireException;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use DateTimeInterface;
use Throwable;

/**
 * ScheduleProcessor
 */
class ScheduleProcessor implements ScheduleProcessorInterface
{
    /**
     * Create a new ScheduleProcessor.
     *
     * @param TaskProcessorInterface $taskProcessor
     * @param null|EventDispatcherInterface $eventDispatcher
     */
    public function __construct(
        protected TaskProcessorInterface $taskProcessor,
        protected null|EventDispatcherInterface $eventDispatcher = null,
    ) {}

    /**
     * Process the schedule.
     *
     * @param ScheduleInterface $schedule
     * @param DateTimeInterface $now
     * @return TaskResultsInterface
     */
    public function processSchedule(ScheduleInterface $schedule, DateTimeInterface $now): TaskResultsInterface
    {
        $results = new TaskResults();
        
        $this->eventDispatcher?->dispatch(new Event\ScheduleStarting($schedule));
        
        foreach($this->getDueTasks($schedule, $now) as $task) {
            
            $this->eventDispatcher?->dispatch(new Event\TaskStarting($task));

            $result = $this->taskProcessor->processTask($task);
            
            $results->add($result);
            
            $this->eventDispatcher?->dispatch(new Event\TaskFinished($result));
        }
        
        $this->eventDispatcher?->dispatch(new Event\ScheduleFinished($schedule, $results));
        
        return $results;
    }
    
    /**
     * Returns the event dispatcher or null if none.
     *
     * @return null|EventDispatcherInterface
     */
    public function eventDispatcher(): null|EventDispatcherInterface
    {
        return $this->eventDispatcher;
    }
    
    /**
     * Returns the due tasks.
     *
     * @param ScheduleInterface $schedule
     * @return iterable<int, TaskInterface>
     */
    protected function getDueTasks(ScheduleInterface $schedule, DateTimeInterface $now): iterable
    {
        foreach($schedule->all() as $task) {
            
            $date = $task->getSchedule()->getNextRunDate(now: $now);
            
            if (! $this->isDue($date, $now)) {
                continue;
            }
            
            yield $task;
        }
    }
    
    /**
     * Returns the due tasks.
     *
     * @param DateTimeInterface $date
     * @param DateTimeInterface $now
     * @return bool
     */
    protected function isDue(DateTimeInterface $date, DateTimeInterface $now): bool
    {
        if ($now instanceof \DateTimeImmutable) {
            $now = \DateTime::createFromFormat('U', $now->format('U'));
        } else {
            $now = clone $now;
        }
        
        if ($date instanceof \DateTimeImmutable) {
            $date = \DateTime::createFromFormat('U', $date->format('U'));
        } else {
            $date = clone $date;
        }

        $now->setTimezone($date->getTimeZone());

        // drop the seconds to 0:
        $now->setTime((int) $now->format('H'), (int) $now->format('i'), 0);
        $date->setTime((int) $date->format('H'), (int) $date->format('i'), 0);
        
        return $date->getTimestamp() === $now->getTimestamp();
    }
}