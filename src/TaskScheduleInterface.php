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
 * TaskScheduleInterface
 */
interface TaskScheduleInterface
{
    /**
     * Returns a unique id for the schedule.
     *
     * @return string
     */
    public function getId(): string;
    
    /**
     * Returns the next run date.
     *
     * @param DateTimeInterface $now
     * @param bool $allowCurrentDate
     * @return DateTimeInterface
     */
    public function getNextRunDate(DateTimeInterface $now, bool $allowCurrentDate = true): DateTimeInterface;
    
    /**
     * Returns the next run dates.
     *
     * @param DateTimeInterface $now
     * @param bool $allowCurrentDate
     * @param int $maxNumber The maximal numbers of dates.
     * @return array<int, DateTimeInterface>
     */
    public function getNextRunDates(DateTimeInterface $now, bool $allowCurrentDate = true, int $maxNumber = 5): array;
}