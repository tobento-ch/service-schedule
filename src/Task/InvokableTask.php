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
 * InvokableTask
 */
abstract class InvokableTask extends AbstractTask
{
    /**
     * Process the task.
     *
     * @param ContainerInterface $container
     * @return TaskResultInterface
     * @throws \Throwable
     */
    public function processTask(ContainerInterface $container): TaskResultInterface
    {
        $output = (new Autowire($container))->call($this);
        
        if ($output instanceof TaskResultInterface) {
            return $output;
        }
        
        if (is_string($output)) {
            return new TaskResult(task: $this, output: $output);
        }
        
        return new TaskResult(task: $this);
    }
}