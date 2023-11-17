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

namespace Tobento\Service\Schedule\Event;

use Tobento\Service\Schedule\TaskInterface;

/**
 * TaskStarting
 */
final class TaskStarting
{
    /**
     * Create a new TaskStarting.
     *
     * @param TaskInterface $task
     */
    public function __construct(
        private TaskInterface $task,
    ) {}
    
    /**
     * Returns the task.
     *
     * @return TaskInterface
     */
    public function task(): TaskInterface
    {
        return $this->task;
    }
}