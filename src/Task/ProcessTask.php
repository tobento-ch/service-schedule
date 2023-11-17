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
use Tobento\Service\Schedule\TaskException;
use Symfony\Component\Process\Process;
use Psr\Container\ContainerInterface;

/**
 * ProcessTask
 */
final class ProcessTask extends AbstractTask
{
    /**
     * @var Process
     */
    private Process $process;
    
    /**
     * Create a new ProcessTask.
     *
     * @param string|Process $process
     */
    public function __construct(
        string|Process $process,
    ) {
        if (!$process instanceof Process) {
            $process = Process::fromShellCommandline($process);
        }

        $this->process = $process;
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
        $this->process->run();

        if ($this->process->isSuccessful()) {
            return new TaskResult(task: $this, output: $this->process->getOutput());
        }
        
        return new TaskResult(
            task: $this,
            output: $this->process->getOutput().$this->process->getErrorOutput(),
            exception: new TaskException(
                task: $this,
                message: sprintf(
                    'Exit %s: %s',
                    (string)$this->process->getExitCode(),
                    (string)$this->process->getExitCodeText()
                )
            ),
        );
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
        
        return $this->getProcess()->getCommandLine();
    }
    
    /**
     * Returns the process.
     *
     * @return Process
     */
    public function getProcess(): Process
    {
        return $this->process;
    }
}