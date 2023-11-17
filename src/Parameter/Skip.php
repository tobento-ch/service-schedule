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
use Tobento\Service\Schedule\TaskSkipException;
use Tobento\Service\Autowire\Autowire;
use Psr\Container\ContainerInterface;

/**
 * Skips task if the specified skip parameter evaluates to true.
 */
class Skip extends Parameter implements BeforeTaskHandler
{
    /**
     * @var bool|callable
     */
    protected $skip;
    
    /**
     * Create a new Skip.
     *
     * @param bool|callable $skip
     * @param string $reason A reason why the task is skipped.
     */
    public function __construct(
        bool|callable $skip,
        protected string $reason = '',
    ) {
        $this->skip = $skip;
    }
    
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
     * Returns the before task handler.
     *
     * @return callable
     */
    public function getBeforeTaskHandler(): callable
    {
        return [$this, 'beforeTask'];
    }
    
    /**
     * Before task.
     *
     * @param TaskInterface $task
     * @param ContainerInterface $container
     * @return void
     */
    public function beforeTask(TaskInterface $task, ContainerInterface $container): void
    {
        if (is_callable($this->skip)) {
            $this->skip = (new Autowire($container))->call($this->skip, ['task' => $task]);
        }
        
        if ($this->skip) {
            throw new TaskSkipException($task, $this->reason);
        }
    }
}