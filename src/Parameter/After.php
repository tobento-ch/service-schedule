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
 * After task gets processed.
 */
class After extends Parameter implements AfterTaskHandler
{
    /**
     * @var callable|AfterTaskHandler
     */
    protected $handler;
    
    /**
     * Create a new After.
     *
     * @param callable|AfterTaskHandler $handler
     * @param int $priority
     */
    public function __construct(
        callable|AfterTaskHandler $handler,
        protected int $priority = 0,
    ) {
        $this->handler = $handler;
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
     * Returns the priority.
     *
     * @return int
     */
    public function getPriority(): int
    {
        if ($this->handler instanceof AfterTaskHandler) {
            return $this->handler->getPriority();
        }
        
        return $this->priority;
    }
    
    /**
     * Handle after task.
     *
     * @param TaskResultInterface $result
     * @param ContainerInterface $container
     * @return void
     */
    public function afterTask(TaskResultInterface $result, ContainerInterface $container): void
    {
        $autowire = new Autowire($container);
        
        if ($this->handler instanceof AfterTaskHandler) {
            $autowire->call($this->handler->getAfterTaskHandler(), ['result' => $result]);
        } else {
            $autowire->call($this->handler, ['result' => $result]);
        }
    }
}