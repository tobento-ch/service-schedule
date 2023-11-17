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

namespace Tobento\Service\Schedule\Task\Schedule;

use Tobento\Service\Schedule\TaskScheduleInterface;
use Cron\CronExpression as CronExpr;
use DateTimeInterface;
use DateTimeZone;

/**
 * CronExpression
 */
final class CronExpression implements TaskScheduleInterface
{
    /**
     * Create a new CronExpression.
     *
     * @param string $expression
     * @param null|string|DateTimeZone $timezone
     */
    public function __construct(
        private string $expression,
        private null|string|DateTimeZone $timezone = null,
    ) {}
    
    /**
     * Returns a unique id for the schedule.
     *
     * @return string
     */
    public function getId(): string
    {
        return sha1($this->expression);
    }

    /**
     * Returns the next run date.
     *
     * @param DateTimeInterface $now
     * @param bool $allowCurrentDate
     * @return DateTimeInterface
     */
    public function getNextRunDate(DateTimeInterface $now, bool $allowCurrentDate = true): DateTimeInterface
    {
        $expression = new CronExpr($this->expression);
        
        return $expression->getNextRunDate(
            currentTime: $now,
            allowCurrentDate: $allowCurrentDate,
            timeZone: $this->getTimezoneName(),
        );
    }
    
    /**
     * Returns the next run dates.
     *
     * @param DateTimeInterface $now
     * @param bool $allowCurrentDate
     * @param int $maxNumber The maximal numbers of dates.
     * @return array<int, DateTimeInterface>
     */
    public function getNextRunDates(DateTimeInterface $now, bool $allowCurrentDate = true, int $maxNumber = 5): array
    {
        $expression = new CronExpr($this->expression);
        
        return $expression->getMultipleRunDates(
            total: $maxNumber,
            currentTime: $now,
            allowCurrentDate: $allowCurrentDate,
            timeZone: $this->getTimezoneName(),
        );
    }
    
    /**
     * Returns the expression.
     *
     * @return string
     */
    public function getExpression(): string
    {
        return $this->expression;
    }
    
    /**
     * Returns the timezone.
     *
     * @return null|string|DateTimeZone
     */
    public function getTimezone(): null|string|DateTimeZone
    {
        return $this->timezone;
    }
    
    /**
     * Returns the timezone name or null.
     *
     * @return null|string
     */
    private function getTimezoneName(): null|string
    {
        if ($this->timezone instanceof DateTimeZone) {
            return $this->timezone->getName();
        }
        
        return $this->timezone;
    }
}