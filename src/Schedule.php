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

use Tobento\Service\Schedule\Task\CallableTask;
use Tobento\Service\Schedule\Task\CommandTask;
use Tobento\Service\Schedule\Task\PingTask;

/**
 * Schedule
 */
final class Schedule implements ScheduleInterface
{
    /**
     * @var array<int, TaskInterface>
     */
    private array $tasks = [];

    /**
     * Create a new Schedule.
     *
     * @param string $name
     */
    public function __construct(
        private string $name,
    ) {}
    
    /**
     * Returns a schedule name.
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }
    
    /**
     * Add a new task to the schedule.
     *
     * @param TaskInterface $task
     * @return TaskInterface
     */
    public function task(TaskInterface $task): TaskInterface
    {
        $this->tasks[] = $task;
        return $task;
    }
    
    /**
     * Returns the task by id or null if not found.
     *
     * @return null|TaskInterface
     */
    public function getTask(string $id): null|TaskInterface
    {
        foreach($this->all() as $task) {
            if ($task->getId() === $id) {
                return $task;
            }
        }
        
        return null;
    }
    
    /**
     * Returns all tasks.
     *
     * @return iterable<int, TaskInterface>
     */
    public function all(): iterable
    {
        return $this->tasks;
    }
    
    /**
     * Returns the number of tasks.
     *
     * @return int
     */
    public function count(): int
    {
        return count($this->tasks);
    }
}