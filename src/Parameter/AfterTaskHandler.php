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

/**
 * AfterTaskHandler
 */
interface AfterTaskHandler
{
    /**
     * Returns the after task handler.
     *
     * @return callable
     */
    public function getAfterTaskHandler(): callable;
    
    /**
     * Returns the priority.
     *
     * @return int
     */
    public function getPriority(): int;
}