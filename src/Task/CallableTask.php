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

namespace Tobento\Service\Schedule\Task;

use Tobento\Service\Schedule\TaskResultInterface;
use Tobento\Service\Schedule\TaskResult;
use Tobento\Service\Autowire\Autowire;
use Psr\Container\ContainerInterface;

/**
 * CallableTask
 */
final class CallableTask extends AbstractTask
{
    /**
     * @var callable
     */
    private $callable;
    
    /**
     * Create a new CallableTask.
     *
     * @param callable $callable
     * @param array $params
     */
    public function __construct(
        callable $callable,
        private array $params = [],
    ) {
        $this->callable = $callable;
    }

    /**
     * Process the task.
     *
     * @param ContainerInterface $container
     * @return TaskResultInterface
     * @throws \Throwable
     */
    public function processTask(ContainerInterface $container): TaskResultInterface
    {
        $output = (new Autowire($container))->call($this->callable, $this->params);
        
        if ($output instanceof TaskResultInterface) {
            return $output;
        }
        
        if (is_string($output)) {
            return new TaskResult(task: $this, output: $output);
        }
        
        return new TaskResult(task: $this);
    }
    
    /**
     * Returns a task name.
     *
     * @return string
     */
    public function getName(): string
    {
        if (!empty($this->name)) {
            return $this->name;
        }
        
        if (is_object($this->callable)) {
            return (new \ReflectionClass($this->callable))->getShortName();
        }
        
        return (new \ReflectionClass($this))->getShortName();
    }
    
    /**
     * Returns the callable.
     *
     * @return callable
     */
    public function getCallable(): callable
    {
        return $this->callable;
    }
    
    /**
     * Returns the params.
     *
     * @return array
     */
    public function getParams(): array
    {
        return $this->params;
    }
}