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

use ArrayIterator;
use Traversable;

/**
 * TaskResults
 */
final class TaskResults implements TaskResultsInterface
{
    /**
     * @var array<int, TaskResultInterface>
     */
    private array $results = [];
    
    /**
     * Create a new TaskResults.
     *
     * @param TaskResultInterface ...$results
     */
    public function __construct(
        TaskResultInterface ...$results,
    ) {
        $this->results = $results;
    }
    
    /**
     * Add a task result.
     *
     * @param TaskResultInterface $result
     * @return static $this
     */
    public function add(TaskResultInterface $result): static
    {
        $this->results[] = $result;
        
        return $this;
    }
    
    /**
     * Returns all task results.
     *
     * @return iterable<int, TaskResultInterface>
     */
    public function all(): iterable
    {
        return $this->results;
    }
    
    /**
     * Returns a new instance with the successful task results filtered.
     *
     * @return static
     */
    public function successful(): static
    {
        return $this->filter(fn(TaskResultInterface $r): bool => $r->isSuccessful());
    }
    
    /**
     * Returns a new instance with the failed task results filtered.
     *
     * @return static
     */
    public function failed(): static
    {
        return $this->filter(fn(TaskResultInterface $r): bool => $r->isFailure());
    }
    
    /**
     * Returns a new instance with the skipped task results filtered.
     *
     * @return static
     */
    public function skipped(): static
    {
        return $this->filter(fn(TaskResultInterface $r): bool => $r->isSkipped());
    }
    
    /**
     * Returns a new instance with the filtered task results.
     *
     * @param callable $callback
     * @return static
     */
    public function filter(callable $callback): static
    {
        $new = clone $this;
        $new->results = array_filter($this->results, $callback);
        return $new;
    }
    
    /**
     * Returns the number of task results.
     *
     * @return int
     */
    public function count(): int
    {
        return count($this->results);
    }
    
    /**
     * Returns the iterator. 
     *
     * @return Traversable
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->results);
    }
}