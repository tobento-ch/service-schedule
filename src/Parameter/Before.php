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
use Tobento\Service\Autowire\Autowire;
use Psr\Container\ContainerInterface;

/**
 * Before task gets processed.
 */
class Before extends Parameter implements BeforeTaskHandler
{
    /**
     * @var callable|BeforeTaskHandler
     */
    protected $handler;
    
    /**
     * Create a new Before.
     *
     * @param callable|BeforeTaskHandler $handler
     * @param int $priority
     */
    public function __construct(
        callable|BeforeTaskHandler $handler,
        protected int $priority = 0,
    ) {
        $this->handler = $handler;
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
     * Returns the priority.
     *
     * @return int
     */
    public function getPriority(): int
    {
        if ($this->handler instanceof BeforeTaskHandler) {
            return $this->handler->getPriority();
        }
        
        return $this->priority;
    }
    
    /**
     * Handle before task.
     *
     * @param TaskInterface $task
     * @param ContainerInterface $container
     * @return void
     */
    public function beforeTask(TaskInterface $task, ContainerInterface $container): void
    {
        $autowire = new Autowire($container);
        
        if ($this->handler instanceof BeforeTaskHandler) {
            $autowire->call($this->handler->getBeforeTaskHandler(), ['task' => $task]);
        } else {
            $autowire->call($this->handler, ['task' => $task]);
        }
    }
}