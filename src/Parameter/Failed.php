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

use Tobento\Service\Schedule\TaskResultInterface;
use Tobento\Service\Autowire\Autowire;
use Psr\Container\ContainerInterface;

/**
 * Failed task gets processed.
 */
class Failed extends Parameter implements FailedTaskHandler
{
    /**
     * @var callable|FailedTaskHandler
     */
    protected $handler;
    
    /**
     * Create a new Failed.
     *
     * @param callable|FailedTaskHandler $handler
     * @param int $priority
     */
    public function __construct(
        callable|FailedTaskHandler $handler,
        protected int $priority = 0,
    ) {
        $this->handler = $handler;
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
     * Returns the priority.
     *
     * @return int
     */
    public function getPriority(): int
    {
        if ($this->handler instanceof FailedTaskHandler) {
            return $this->handler->getPriority();
        }
        
        return $this->priority;
    }
    
    /**
     * Handle failed task.
     *
     * @param TaskResultInterface $result
     * @param ContainerInterface $container
     * @return void
     */
    public function failedTask(TaskResultInterface $result, ContainerInterface $container): void
    {
        $autowire = new Autowire($container);
        
        if ($this->handler instanceof FailedTaskHandler) {
            $autowire->call($this->handler->getFailedTaskHandler(), ['result' => $result]);
        } else {
            $autowire->call($this->handler, ['result' => $result]);
        }
    }
}