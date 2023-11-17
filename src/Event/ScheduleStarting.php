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

namespace Tobento\Service\Schedule\Event;

use Tobento\Service\Schedule\ScheduleInterface;

/**
 * ScheduleStarting
 */
final class ScheduleStarting
{
    /**
     * Create a new ScheduleStarting.
     *
     * @param ScheduleInterface $schedule
     */
    public function __construct(
        private ScheduleInterface $schedule,
    ) {}
    
    /**
     * Returns the schedule.
     *
     * @return ScheduleInterface
     */
    public function schedule(): ScheduleInterface
    {
        return $this->schedule;
    }
}