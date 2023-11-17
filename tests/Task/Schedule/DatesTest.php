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

namespace Tobento\Service\Schedule\Test\Task\Schedule;

use PHPUnit\Framework\TestCase;
use Tobento\Service\Schedule\Task\Schedule\Dates;
use Tobento\Service\Schedule\TaskScheduleInterface;
use DateTimeInterface;
use DateTimeImmutable;
use DateTime;
use DateTimeZone;

class DatesTest extends TestCase
{
    public function testThatImplementsTaskScheduleInterface()
    {
        $this->assertInstanceof(TaskScheduleInterface::class, new Dates());
    }

    public function testGetIdMethod()
    {
        $schedule = new Dates();
        $this->assertSame('da39a3ee5e6b4b0d3255bfef95601890afd80709', $schedule->getId());
        
        $schedule = new Dates(new DateTime('2023-05-12 15:38:45'));
        $this->assertSame('dccf6e71e51da7919f4fad3dca44f3be5cf60e7d', $schedule->getId());
    }
    
    public function testSpecificMethod()
    {
        $schedule = new Dates(new DateTime('2023-05-12 15:38:45'));
        $this->assertEquals([new DateTime('2023-05-12 15:38:45')], $schedule->getDates());
    }
    
    public function testGetNextRunDateMethod()
    {
        $this->assertInstanceOf(
            DateTimeInterface::class,
            (new Dates(new DateTime('2023-05-12 15:38:45')))->getNextRunDate(now: new DateTime())
        );
    }
    
    public function testGetNextRunDateMethodWithAllowCurrentDate()
    {
        $this->assertSame(
            '2023-05-12 15:38:45 +02:00',
            (new Dates(new DateTime('2023-05-12 15:38:45', new DateTimeZone('Europe/Berlin'))))
                ->getNextRunDate(
                    now: new DateTime('2023-05-12 15:38:45', new DateTimeZone('Europe/Berlin')),
                    allowCurrentDate: true,
                )
                ->format('Y-m-d H:i:s P')
        );
    }
    
    public function testGetNextRunDateMethodWithoutAllowCurrentDate()
    {
        $this->assertSame(
            '2024-05-12 15:38:45 +02:00',
            (new Dates(new DateTime('2023-05-12 15:38:45', new DateTimeZone('Europe/Berlin'))))
                ->getNextRunDate(
                    now: new DateTime('2023-05-12 15:38:45', new DateTimeZone('Europe/Berlin')),
                    allowCurrentDate: false,
                )
                ->format('Y-m-d H:i:s P')
        );
    }
    
    public function testGetNextRunDateMethodWithPastDateModifiesToNextYear()
    {
        $this->assertSame(
            '2024-05-12 15:38:45 +02:00',
            (new Dates(new DateTime('2023-05-12 15:38:45', new DateTimeZone('Europe/Berlin'))))
                ->getNextRunDate(now: new DateTime('2023-05-12 15:39:45', new DateTimeZone('Europe/Berlin')))
                ->format('Y-m-d H:i:s P')
        );
    }
    
    public function testGetNextRunDateMethodWithDifferentTimezone()
    {
        $this->assertSame(
            '2023-05-12 15:38:45 +01:00',
            (new Dates(new DateTime('2023-05-12 15:38:45', new DateTimeZone('Europe/London'))))
                ->getNextRunDate(now: new DateTime('2023-05-12 16:38:45', new DateTimeZone('Europe/Berlin')))
                ->format('Y-m-d H:i:s P')
        );
        
        $this->assertSame(
            '2024-05-12 15:38:45 +01:00',
            (new Dates(new DateTime('2023-05-12 15:38:45', new DateTimeZone('Europe/London'))))
                ->getNextRunDate(now: new DateTime('2023-05-12 16:39:45', new DateTimeZone('Europe/Berlin')))
                ->format('Y-m-d H:i:s P')
        );
    }
    
    public function testGetNextRunDateMethodWithMultipleDatesGetsEarliest()
    {
        $this->assertSame(
            '2023-05-12 15:38:45 +02:00',
            (new Dates(
                new DateTime('2023-05-15 15:38:45', new DateTimeZone('Europe/Berlin')),
                new DateTime('2024-05-12 15:38:45', new DateTimeZone('Europe/Berlin')),
                new DateTimeImmutable('2023-05-14 15:38:45', new DateTimeZone('Europe/Berlin')),
            ))
            ->getNextRunDate(now: new DateTime('2023-05-12 15:38:45', new DateTimeZone('Europe/Berlin')))
            ->format('Y-m-d H:i:s P')
        );
    }
    
    public function testGetNextRunDateMethodThrowsRuntimeExceptionIfNoneSet()
    {
        $this->expectException(\RuntimeException::class);
        
        (new Dates())->getNextRunDate(now: new DateTime());
    }
    
    public function testGetNextRunDatesMethod()
    {
        $runDates = (new Dates(
            new DateTime('2023-05-15 15:38:45'),
            new DateTime('2024-05-12 15:38:45'),
            new DateTimeImmutable('2023-05-14 15:38:45'),
        ))->getNextRunDates(
            now: new DateTime('2023-05-12 15:38:45'),
            allowCurrentDate: true,
            maxNumber: 2,
        );
        
        $this->assertEquals([
            new DateTime('2023-05-12 15:38:45'),
            new DateTime('2023-05-14 15:38:45'),
        ], $runDates);
    }
    
    public function testGetNextRunDatesMethodWithoutCurrentDate()
    {
        $runDates = (new Dates(
            new DateTime('2023-05-15 15:38:45'),
            new DateTime('2024-05-12 15:38:45'),
            new DateTimeImmutable('2023-05-14 15:38:45'),
        ))->getNextRunDates(
            now: new DateTime('2023-05-12 15:38:45'),
            allowCurrentDate: false,
            maxNumber: 2,
        );
        
        $this->assertEquals([
            new DateTime('2023-05-14 15:38:45'),
            new DateTime('2023-05-15 15:38:45'),
        ], $runDates);
    }
    
    public function testGetNextRunDatesMethodWithDifferentTimezone()
    {
        $runDates = (new Dates(
            new DateTime('2023-05-12 15:37:45', new DateTimeZone('Europe/London')),
            new DateTime('2024-05-12 15:38:45', new DateTimeZone('Europe/London')),
            new DateTime('2024-05-12 15:39:45', new DateTimeZone('Europe/London')),
        ))->getNextRunDates(
            now: new DateTime('2023-05-12 16:38:45', new DateTimeZone('Europe/Berlin')),
            allowCurrentDate: false,
            maxNumber: 2,
        );
        
        $this->assertEquals([
            new DateTime('2023-05-12 15:39:45', new DateTimeZone('Europe/London')),
            new DateTime('2024-05-12 15:37:45', new DateTimeZone('Europe/London')),
        ], $runDates);
    }
}