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

use Tobento\Service\Schedule\TaskResultInterface;

/**
 * TaskFinished
 */
final class TaskFinished
{
    /**
     * Create a new TaskFinished.
     *
     * @param TaskResultInterface $result
     */
    public function __construct(
        private TaskResultInterface $result,
    ) {}
    
    /**
     * Returns the task result.
     *
     * @return TaskResultInterface
     */
    public function result(): TaskResultInterface
    {
        return $this->result;
    }
}