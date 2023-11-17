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

use IteratorAggregate;
use Countable;

/**
 * TaskResultsInterface
 */
interface TaskResultsInterface extends IteratorAggregate, Countable
{
    /**
     * Add a task result.
     *
     * @param TaskResultInterface $result
     * @return static $this
     */
    public function add(TaskResultInterface $result): static;
    
    /**
     * Returns all task results.
     *
     * @return iterable<int, TaskResultInterface>
     */
    public function all(): iterable;
    
    /**
     * Returns a new instance with the successful task results filtered.
     *
     * @return static
     */
    public function successful(): static;
    
    /**
     * Returns a new instance with the failed task results filtered.
     *
     * @return static
     */
    public function failed(): static;
    
    /**
     * Returns a new instance with the skipped task results filtered.
     *
     * @return static
     */
    public function skipped(): static;
    
    /**
     * Returns a new instance with the filtered task results.
     *
     * @param callable $callback
     * @return static
     */
    public function filter(callable $callback): static;
}