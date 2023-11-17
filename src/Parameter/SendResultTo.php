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
use Tobento\Service\FileCreator\FileCreator;
use Tobento\Service\FileCreator\FileCreatorException;

/**
 * Sends the task result to the specified file.
 */
class SendResultTo extends Parameter implements AfterTaskHandler, FailedTaskHandler
{
    /**
     * Create a new SendResultTo.
     *
     * @param string $file
     * @param bool $onlyOutput
     * @param bool $append
     * @param array $handle
     */
    public function __construct(
        protected string $file,
        protected bool $onlyOutput = false,
        protected bool $append = true,
        protected array $handle = ['after', 'failed'],
    ) {}
    
    /**
     * Returns the file.
     *
     * @return string
     */
    public function getFile(): string
    {
        return $this->file;
    }
    
    /**
     * Returns the after task handler.
     *
     * @return callable
     */
    public function getAfterTaskHandler(): callable
    {
        return [$this, 'sendResult'];
    }
    
    /**
     * Returns the failed task handler.
     *
     * @return callable
     */
    public function getFailedTaskHandler(): callable
    {
        return [$this, 'sendResult'];
    }
    
    /**
     * Send task result.
     *
     * @param TaskInterface $task
     * @return void
     */
    public function sendResult(TaskResultInterface $result): void
    {
        $handling = $this->append ? FileCreator::CONTENT_APPEND : FileCreator::CONTENT_NEW;
        
        if ($this->onlyOutput) {
            (new FileCreator())
                ->content($result->output())
                ->create($this->file, $handling);
            
            return;
        }
        
        $creator = (new FileCreator())
            ->content('Task Time:')->newline()->content(date('c'))->newline(num: 2)
            ->content('Task ID:')->newline()->content($result->task()->getId())->newline(num: 2)
            ->content('Task Name:')->newline()->content($result->task()->getName())->newline(num: 2)
            ->content('Task Description:')->newline()->content($result->task()->getDescription())->newline(num: 2);
        
        if ($result->exception()) {
            $creator->content('Task Exception:')->newline()->content((string)$result->exception()?->__toString())->newline(num: 2);
        }
        
        $creator->content('Task Output:')->newline()->content($result->output())->newline(num: 2);
        $creator->create($this->file, $handling);
    }
}