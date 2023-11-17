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

use Tobento\Service\Schedule\Parameter;
use Closure;

/**
 * Default parameters methods.
 */
trait InteractsWithParameters
{
    /**
     * Specify a before task handler.
     *
     * @param Closure|Parameter\BeforeTaskHandler $handler
     * @return static $this
     */
    public function before(Closure|Parameter\BeforeTaskHandler $handler): static
    {
        $this->parameters()->add(new Parameter\Before($handler));
        
        return $this;
    }
    
    /**
     * Specify an after task handler.
     *
     * @param Closure|Parameter\AfterTaskHandler $handler
     * @return static $this
     */
    public function after(Closure|Parameter\AfterTaskHandler $handler): static
    {
        $this->parameters()->add(new Parameter\After($handler));
        
        return $this;
    }
    
    /**
     * Specify a failed task handler.
     *
     * @param Closure|Parameter\FailedTaskHandler $handler
     * @return static $this
     */
    public function failed(Closure|Parameter\FailedTaskHandler $handler): static
    {
        $this->parameters()->add(new Parameter\Failed($handler));
        
        return $this;
    }

    /**
     * Specify if to prevent the task from overlapping.
     *
     * @param null|string $id A unique id. If null it uses the task id.
     * @param int $ttl Maximum expected lock duration in seconds.
     * @return static $this
     */
    public function withoutOverlapping(null|string $id = null, int $ttl = 86400): static
    {
        $this->parameters()->add(new Parameter\WithoutOverlapping(id: $id, ttl: $ttl));
        
        return $this;
    }
    
    /**
     * Specify if to monitor the task process.
     *
     * @return static $this
     */
    public function monitor(): static
    {
        $this->parameters()->add(new Parameter\Monitor());
        
        return $this;
    }
    
    /**
     * Specify if to skip the task.
     *
     * @param bool|callable $skip
     * @param string $reason A reason why the task is skipped.
     * @return static $this
     */
    public function skip(bool|callable $skip, string $reason = ''): static
    {
        $this->parameters()->add(new Parameter\Skip(skip: $skip, reason: $reason));
        
        return $this;
    }
}