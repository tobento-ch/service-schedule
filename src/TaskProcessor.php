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

use Tobento\Service\Autowire\Autowire;
use Psr\Container\ContainerInterface;
use Throwable;

/**
 * TaskProcessor
 */
class TaskProcessor implements TaskProcessorInterface
{
    /**
     * Create a new TaskProcessor.
     *
     * @param ContainerInterface $container
     */
    public function __construct(
        protected ContainerInterface $container,
    ) {}
    
    /**
     * Process the task.
     *
     * @param TaskInterface $task
     * @return TaskResultInterface
     */
    public function processTask(TaskInterface $task): TaskResultInterface
    {
        try {
            $this->handleBeforeTask($this->container, $task);
            $result = $task->processTask($this->container);
            $this->handleAfterTask($this->container, $task, $result);
            return $result;
        } catch (TaskSkipException $e) {
            return new TaskResult(task: $task, exception: $e);
        } catch (Throwable $e) {
            $result = new TaskResult(task: $task, exception: $e);
            $this->handleFailedTask($this->container, $task, $result);
            return $result;
        }
    }
    
    /**
     * Handle before task.
     *
     * @param ContainerInterface $container
     * @param TaskInterface $task
     * @return void
     * @throws Throwable
     */
    protected function handleBeforeTask(ContainerInterface $container, TaskInterface $task): void
    {
        $autowire = new Autowire($container);
        
        // sorts by priority, highest first.
        foreach($task->parameters()->sort() as $parameter) {
            if ($parameter instanceof Parameter\BeforeTaskHandler) {
                $autowire->call($parameter->getBeforeTaskHandler(), ['task' => $task]);
            }
        }
    }
    
    /**
     * Handle after task.
     *
     * @param ContainerInterface $container
     * @param TaskInterface $task
     * @param TaskResultInterface $result
     * @return void
     * @throws Throwable
     */
    protected function handleAfterTask(ContainerInterface $container, TaskInterface $task, TaskResultInterface $result): void
    {
        $autowire = new Autowire($container);

        // sorts by priority, highest last.
        $callback = fn(ParameterInterface $a, ParameterInterface $b): int
            => $a->getPriority() <=> $b->getPriority();
        
        foreach($task->parameters()->sort($callback) as $parameter) {
            if ($parameter instanceof Parameter\AfterTaskHandler) {
                $autowire->call($parameter->getAfterTaskHandler(), ['result' => $result]);
            }
        }
    }
    
    /**
     * Handle failed task.
     *
     * @param ContainerInterface $container
     * @param TaskInterface $task
     * @param TaskResultInterface $result
     * @return void
     * @throws Throwable
     */
    protected function handleFailedTask(ContainerInterface $container, TaskInterface $task, TaskResultInterface $result): void
    {
        $autowire = new Autowire($container);
        
        // sorts by priority, highest first.
        foreach($task->parameters()->sort() as $parameter) {
            if ($parameter instanceof Parameter\FailedTaskHandler) {
                $autowire->call($parameter->getFailedTaskHandler(), ['result' => $result]);
            }
        }
    }
}