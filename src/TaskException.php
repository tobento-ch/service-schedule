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

use Exception;
use Throwable;

/**
 * TaskException
 */
class TaskException extends Exception
{
    /**
     * Create a new TaskException.
     *
     * @param null|TaskInterface $task
     * @param string $message The message
     * @param int $code
     * @param null|Throwable $previous
     */
    public function __construct(
        protected null|TaskInterface $task = null,
        string $message = '',
        int $code = 0,
        null|Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
    
    /**
     * Returns the task.
     *
     * @return null|TaskInterface
     */
    public function task(): null|TaskInterface
    {
        return $this->task;
    }
}