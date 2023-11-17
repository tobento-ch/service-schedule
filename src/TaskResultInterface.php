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
 * TaskResultInterface
 */
interface TaskResultInterface
{
    /**
     * Returns the the task.
     *
     * @return TaskInterface
     */
    public function task(): TaskInterface;
    
    /**
     * Returns the task output.
     *
     * @return string
     */
    public function output(): string;
    
    /**
     * Returns the exception.
     *
     * @return null|Throwable
     */
    public function exception(): null|Throwable;
    
    /**
     * Returns true if the task was successful, otherwise false.
     *
     * @return bool
     */
    public function isSuccessful(): bool;
    
    /**
     * Returns true if the task failed, otherwise false.
     *
     * @return bool
     */
    public function isFailure(): bool;
    
    /**
     * Returns true if the task was skipped, otherwise false.
     *
     * @return bool
     */
    public function isSkipped(): bool;    
}