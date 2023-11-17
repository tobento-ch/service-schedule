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

use Throwable;

/**
 * TaskResult
 */
final class TaskResult implements TaskResultInterface
{
    /**
     * Create a new Result.
     *
     * @param Closure $callable
     */
    public function __construct(
        private TaskInterface $task,
        private string $output = '',
        private null|Throwable $exception = null,
    ) {}
    
    /**
     * Returns the the task.
     *
     * @return TaskInterface
     */
    public function task(): TaskInterface
    {
        return $this->task;
    }
    
    /**
     * Returns the task output.
     *
     * @return string
     */
    public function output(): string
    {
        return $this->output;
    }
    
    /**
     * Returns the exception.
     *
     * @return null|Throwable
     */
    public function exception(): null|Throwable
    {
        return $this->exception;
    }
    
    /**
     * Returns true if the task was successful, otherwise false.
     *
     * @return bool
     */
    public function isSuccessful(): bool
    {
        return is_null($this->exception());
    }
    
    /**
     * Returns true if the task failed, otherwise false.
     *
     * @return bool
     */
    public function isFailure(): bool
    {
        return !is_null($this->exception()) && !$this->exception() instanceof TaskSkipException;
    }
    
    /**
     * Returns true if the task was skipped, otherwise false.
     *
     * @return bool
     */
    public function isSkipped(): bool
    {
        return $this->exception() instanceof TaskSkipException;
    }
}