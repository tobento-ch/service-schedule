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
 * Parameters
 */
class Parameters implements ParametersInterface
{
    /**
     * @var array<array-key, ParameterInterface>
     */
    protected array $parameters = [];
    
    /**
     * Create a new Parameters.
     *
     * @param ParameterInterface ...$parameters
     */
    public function __construct(
        ParameterInterface ...$parameters,
    ) {
        foreach($parameters as $parameter) {
            $this->add($parameter);
        }
    }

    /**
     * Add a new parameter.
     *
     * @param ParameterInterface $parameter
     * @return static $this
     */
    public function add(ParameterInterface $parameter): static
    {
        $this->parameters[] = $parameter;
        
        return $this;
    }
    
    /**
     * Returns a new instance with the filtered parameters.
     *
     * @param callable $callback
     * @return static
     */
    public function filter(callable $callback): static
    {
        $new = clone $this;
        $new->parameters = array_filter($this->parameters, $callback);
        return $new;
    }
    
    /**
     * Returns a new instance with the filtered parameters by name.
     *
     * @param string $name
     * @return static
     */
    public function name(string $name): static
    {
        return $this->filter(fn(ParameterInterface $p): bool => $p->getName() === $name);
    }
    
    /**
     * Returns a new instance with the resources sorted.
     *
     * @param null|callable $callback If null, sorts by priority, highest first.
     * @return static
     */
    public function sort(null|callable $callback = null): static
    {
        if (is_null($callback))
        {
            $callback = fn(ParameterInterface $a, ParameterInterface $b): int
                => $b->getPriority() <=> $a->getPriority();
        }
        
        $new = clone $this;
        uasort($new->parameters, $callback);
        return $new;
    }
    
    /**
     * Returns the first parameter of null if none.
     *
     * @return null|object
     */
    public function first(): null|object
    {
        $key = array_key_first($this->parameters);
        
        if (is_null($key)) {
            return null;
        }
        
        return $this->parameters[$key];    
    }
    
    /**
     * Returns the parameters.
     *
     * @return array<array-key, ParameterInterface>
     */
    public function all(): array
    {
        return $this->parameters;
    }
    
    /**
     * Get the iterator. 
     *
     * @return Traversable
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->all());
    }
}