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

use DateTimeInterface;

/**
 * ScheduleProcessorInterface
 */
interface ScheduleProcessorInterface
{
    /**
     * Process the schedule.
     *
     * @param ScheduleInterface $schedule
     * @param DateTimeInterface $now
     * @return TaskResultsInterface
     */
    public function processSchedule(ScheduleInterface $schedule, DateTimeInterface $now): TaskResultsInterface;
}