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
use DateTimeInterface;
use DateTimeZone;

/**
 * Dates: modified every year to run yyyy-02-05 12:34.
 */
final class Dates implements TaskScheduleInterface
{
    /**
     * @var array<int, DateTimeInterface>
     */
    private array $dates;
    
    /**
     * @var null|string
     */
    protected null|string $id = null;
    
    /**
     * Create a new Dates.
     *
     * @param DateTimeInterface ...$dates
     */
    public function __construct(
        DateTimeInterface ...$dates,
    ) {
        $this->dates = $dates;
    }
    
    /**
     * Returns a unique id for the schedule.
     *
     * @return string
     */
    public function getId(): string
    {
        if (is_string($this->id)) {
            return $this->id;
        }
        
        $timestamps = [];
        
        foreach($this->dates as $date) {
            $timestamps[] = $date->getTimestamp();
        }
        
        return $this->id = sha1(implode(':', $timestamps));
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
        $nextRunDates = $this->getNextRunDates(
            now: $now,
            allowCurrentDate: $allowCurrentDate,
            maxNumber: 1,
        );
        
        return $nextRunDates[0] ?? throw new \RuntimeException('Unable to determine next run date');
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
        if ($maxNumber < 1) {
            $maxNumber = 1;
        }
        
        if ($now instanceof \DateTimeImmutable) {
            $now = \DateTime::createFromFormat('U', $now->format('U'));
        } else {
            $now = clone $now;
        }
        
        $dates = [];
        
        foreach($this->dates as $date) {
                        
            if ($date instanceof \DateTimeImmutable) {
                $date = \DateTime::createFromFormat('U', $date->format('U'));
            } else {
                $date = clone $date;
            }
            
            $now->setTimezone($date->getTimeZone());
            
            $date->setDate(
                year: (int)$now->format('Y'),
                month: (int)$date->format('m'),
                day: (int)$date->format('d'),
            );

            if ($allowCurrentDate && $date >= $now) {
                $dates[] = $date;
            } elseif ($date > $now) {
                $dates[] = $date;
            } else {
                $dates[] = $date->modify('+1 year');
            }
        }
        
        usort($dates, fn (DateTimeInterface $a, DateTimeInterface $b): int => $a <=> $b);
        
        return array_slice($dates, 0, $maxNumber);
    }
    
    /**
     * Returns the dates.
     *
     * @return array<int, DateTimeInterface>
     */
    public function getDates(): array
    {
        return $this->dates;
    }
}