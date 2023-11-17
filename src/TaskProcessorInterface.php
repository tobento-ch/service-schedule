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

/**
 * TaskProcessorInterface
 */
interface TaskProcessorInterface
{
    /**
     * Process the task.
     *
     * @param TaskInterface $task
     * @return TaskResultInterface
     */
    public function processTask(TaskInterface $task): TaskResultInterface;
}