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

use Countable;

/**
 * ScheduleInterface
 */
interface ScheduleInterface extends Countable
{
    /**
     * Returns a schedule name.
     *
     * @return string
     */
    public function getName(): string;
    
    /**
     * Add a new task to the schedule.
     *
     * @param TaskInterface $task
     * @return TaskInterface
     */
    public function task(TaskInterface $task): TaskInterface;
    
    /**
     * Returns the task by id or null if not found.
     *
     * @return null|TaskInterface
     */
    public function getTask(string $id): null|TaskInterface;
    
    /**
     * Returns all tasks.
     *
     * @return iterable<int, TaskInterface>
     */
    public function all(): iterable;
}