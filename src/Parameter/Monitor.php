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

namespace Tobento\Service\Schedule\Parameter;

use Tobento\Service\Schedule\TaskInterface;
use Tobento\Service\Schedule\TaskResultInterface;

/**
 * Monitors the task process.
 */
class Monitor extends Parameter implements BeforeTaskHandler, AfterTaskHandler, FailedTaskHandler
{
    /**
     * @var string
     */
    protected string $startedAt = '';
    
    /**
     * @var int|float
     */
    protected int|float $startTime = 0;
    
    /**
     * @var int|float
     */
    protected int|float $runtimeInSeconds = 0;

    /**
     * @var int|float
     */
    protected int|float $startMemory = 0;
    
    /**
     * @var int|float
     */
    protected int|float $memoryUsage = 0;
    
    /**
     * Returns the priority.
     *
     * @return int
     */
    public function getPriority(): int
    {
        return 1000000;
    }

    /**
     * Returns the time the task started.
     *
     * @return string
     */
    public function startedAt(): string
    {
        return $this->startedAt;
    }
    
    /**
     * Returns the runtime in seconds.
     *
     * @return int|float
     */
    public function runtimeInSeconds(): int|float
    {
        return $this->runtimeInSeconds;
    }
    
    /**
     * Returns the memory usage in bytes.
     *
     * @return int|float
     */
    public function memoryUsage(): int|float
    {
        return $this->memoryUsage;
    }
    
    /**
     * Returns the before task handler.
     *
     * @return callable
     */
    public function getBeforeTaskHandler(): callable
    {
        return [$this, 'beforeTask'];
    }
    
    /**
     * Returns the after task handler.
     *
     * @return callable
     */
    public function getAfterTaskHandler(): callable
    {
        return [$this, 'afterTask'];
    }
    
    /**
     * Returns the failed task handler.
     *
     * @return callable
     */
    public function getFailedTaskHandler(): callable
    {
        return [$this, 'afterTask'];
    }
    
    /**
     * Before task.
     *
     * @param TaskInterface $task
     * @return void
     */
    public function beforeTask(TaskInterface $task): void
    {
        $this->startedAt = date('c');
        $this->startTime = hrtime(true);
        $this->startMemory = memory_get_usage(true);
    }
    
    /**
     * After task.
     *
     * @param TaskInterface $task
     * @return void
     */
    public function afterTask(TaskResultInterface $result): void
    {
        $this->runtimeInSeconds = (hrtime(true) - $this->startTime) / 1e+6 / 1000;
        $this->memoryUsage = memory_get_usage(true) - $this->startMemory;
    }
}