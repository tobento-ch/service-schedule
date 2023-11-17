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
use Tobento\Service\Schedule\TaskResultsInterface;

/**
 * ScheduleFinished
 */
final class ScheduleFinished
{
    /**
     * Create a new ScheduleFinished.
     *
     * @param ScheduleInterface $schedule
     * @param TaskResultsInterface $results
     */
    public function __construct(
        private ScheduleInterface $schedule,
        private TaskResultsInterface $results,
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
    
    /**
     * Returns the results.
     *
     * @return TaskResultsInterface
     */
    public function results(): TaskResultsInterface
    {
        return $this->results;
    }
}