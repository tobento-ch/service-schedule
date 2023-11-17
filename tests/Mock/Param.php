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

namespace Tobento\Service\Schedule\Test\Mock;

use Tobento\Service\Schedule\Parameter\Parameter;
use Tobento\Service\Schedule\Parameter\BeforeTaskHandler;
use Tobento\Service\Schedule\Parameter\AfterTaskHandler;
use Tobento\Service\Schedule\Parameter\FailedTaskHandler;
use Tobento\Service\Schedule\TaskInterface;
use Tobento\Service\Schedule\TaskResultInterface;
use Closure;

class Param extends Parameter implements BeforeTaskHandler, AfterTaskHandler, FailedTaskHandler
{
    protected array $processed = [];
    
    public function __construct(
        protected null|string $name = null,
        protected null|Closure $handler = null,
        protected int $priority = 0,
    ) {}
    
    public function getName(): string
    {
        return $this->name ?: static::class;
    }

    public function getPriority(): int
    {
        return $this->priority;
    }
    
    public function processed(): array
    {
        return $this->processed;
    }
    
    public function getBeforeTaskHandler(): callable
    {
        return [$this, 'beforeTask'];
    }

    public function getAfterTaskHandler(): callable
    {
        return [$this, 'afterTask'];
    }

    public function getFailedTaskHandler(): callable
    {
        return [$this, 'afterTask'];
    }
    
    public function beforeTask(TaskInterface $task): void
    {
        if (!is_null($this->handler)) {
            ($this->handler)();
        }
        
        $this->processed[] = 'before:'.$this->getName();
    }

    public function afterTask(TaskResultInterface $result): void
    {
        if (!is_null($this->handler)) {
            ($this->handler)();
        }
        
        $this->processed[] = 'after:'.$this->getName();
    }
    
    public function failedTask(TaskResultInterface $result): void
    {
        if (!is_null($this->handler)) {
            ($this->handler)();
        }
        
        $this->processed[] = 'failed:'.$this->getName();
    }    
}