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

/**
 * ParametersInterface
 */
interface ParametersInterface extends IteratorAggregate
{
    /**
     * Add a new parameter.
     *
     * @param ParameterInterface $parameter
     * @return static $this
     */
    public function add(ParameterInterface $parameter): static;
    
    /**
     * Returns a new instance with the filtered parameters.
     *
     * @param callable $callback
     * @return static
     */
    public function filter(callable $callback): static;
    
    /**
     * Returns a new instance with the filtered parameters by name.
     *
     * @param string $name
     * @return static
     */
    public function name(string $name): static;
    
    /**
     * Returns a new instance with the resources sorted.
     *
     * @param null|callable $callback If null, sorts by priority, highest first.
     * @return static
     */
    public function sort(null|callable $callback = null): static;
    
    /**
     * Returns the first parameter of null if none.
     *
     * @return null|object
     */
    public function first(): null|object;
    
    /**
     * Returns the parameters.
     *
     * @return array<array-key, ParameterInterface>
     */
    public function all(): array;
}