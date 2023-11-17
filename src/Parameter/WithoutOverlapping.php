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
use Tobento\Service\Schedule\TaskSkipException;
use Psr\SimpleCache\CacheInterface;

/**
 * WithoutOverlapping.
 */
class WithoutOverlapping extends Parameter implements BeforeTaskHandler, AfterTaskHandler, FailedTaskHandler
{
    /**
     * Create a new WithoutOverlapping.
     *
     * @param null|string $id A unique id. If null it uses the task id.
     * @param int $ttl Maximum expected lock duration in seconds.
     */
    public function __construct(
        protected null|string $id = null,
        protected int $ttl = 86400,
    ) {}
    
    /**
     * Returns the priority.
     *
     * @return int
     */
    public function getPriority(): int
    {
        return 100000;
    }
    
    /**
     * Returns the unique id.
     *
     * @return null|string
     */
    public function id(): null|string
    {
        return $this->id;
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
        return [$this, 'failedTask'];
    }
    
    /**
     * Before task.
     *
     * @param TaskInterface $task
     * @param CacheInterface $cache
     * @return void
     */
    public function beforeTask(TaskInterface $task, CacheInterface $cache): void    
    {
        if ($cache->has($this->getTaskCacheKey($task))) {
            throw new TaskSkipException($task, 'Task running in another process.');
        }
        
        // add to cache so we can determine if the task is running:
        $cache->set(key: $this->getTaskCacheKey($task), value: true, ttl: $this->ttl);
    }
    
    /**
     * After task.
     *
     * @param TaskResultInterface $result
     * @param CacheInterface $cache
     * @return void
     */
    public function afterTask(TaskResultInterface $result, CacheInterface $cache): void
    {
        $cache->delete($this->getTaskCacheKey($result->task()));
    }
    
    /**
     * Failed task.
     *
     * @param TaskResultInterface $result
     * @param CacheInterface $cache
     * @return void
     */
    public function failedTask(TaskResultInterface $result, CacheInterface $cache): void
    {
        $cache->delete($this->getTaskCacheKey($result->task()));
    }
    
    /**
     * Returns the cache key for the specified task.
     *
     * @param TaskInterface $task
     * @return string
     */
    protected function getTaskCacheKey(TaskInterface $task): string
    {
        $uniqueId = $this->id() ?: $task->getId();
        
        return 'task-processing:'.$uniqueId;
    }
}