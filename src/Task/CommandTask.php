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
use Tobento\Service\Console\ConsoleInterface;
use Tobento\Service\Console\ExecutedInterface;
use Psr\Container\ContainerInterface;

/**
 * CommandTask
 */
final class CommandTask extends AbstractTask
{
    /**
     * @var null|ConsoleInterface
     */
    protected null|ConsoleInterface $console = null;
    
    /**
     * @var null|ExecutedInterface
     */
    protected null|ExecutedInterface $executed = null;
    
    /**
     * Create a new CommandTask.
     *
     * @param string $command
     * @param array $input
     */
    public function __construct(
        private string $command,
        private array $input = [],
    ) {}
    
    /**
     * Returns a task name.
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name ?: $this->getCommand();
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
        $this->console = $container->get(ConsoleInterface::class);
        
        $this->executed = $this->console->execute(
            command: $this->getCommand(),
            input: $this->getInput(),
        );
        
        if ($this->executed->code() === 0) {
            return new TaskResult(task: $this, output: $this->executed->output());
        }
        
        return new TaskResult(
            task: $this,
            output: $this->executed->output(),
            exception: new TaskException(
                task: $this,
                message: sprintf('Command task failed with code %d', $this->executed->code())
            ),
        );
    }
    
    /**
     * Return the command.
     *
     * @return string
     */
    public function getCommand(): string
    {
        return $this->command;
    }
    
    /**
     * Return the input.
     *
     * @return array
     */
    public function getInput(): array
    {
        return $this->input;
    }
    
    /**
     * Returns the console.
     *
     * @return ConsoleInterface
     */
    public function getConsole(): null|ConsoleInterface
    {
        return $this->console;
    }
    
    /**
     * Returns the executed.
     *
     * @return null|ExecutedInterface
     */
    public function getExecuted(): null|ExecutedInterface
    {
        return $this->executed;
    }
}