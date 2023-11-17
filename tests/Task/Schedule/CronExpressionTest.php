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
use Tobento\Service\Schedule\Task\Schedule\CronExpression;
use Tobento\Service\Schedule\TaskScheduleInterface;
use DateTimeInterface;
use DateTime;
use DateTimeZone;

class CronExpressionTest extends TestCase
{
    public function testThatImplementsTaskScheduleInterface()
    {
        $this->assertInstanceof(TaskScheduleInterface::class, new CronExpression('* * * * *'));
    }

    public function testGetIdMethod()
    {
        $schedule = new CronExpression('* * * * *');
        
        $this->assertSame('95d1cb2935ad3954cdab7b91c7480c9dff1483fe', $schedule->getId());
    }
    
    public function testSpecificMethod()
    {
        $schedule = new CronExpression(expression: '* * * * *');
        $this->assertSame('* * * * *', $schedule->getExpression());
        $this->assertSame(null, $schedule->getTimezone());
        
        $schedule = new CronExpression(expression: '* * * * *', timezone: 'Europe/London');
        $this->assertSame('* * * * *', $schedule->getExpression());
        $this->assertSame('Europe/London', $schedule->getTimezone());
        
        $tz = new DateTimeZone('Europe/Berlin');
        $schedule = new CronExpression(expression: '* * * * *', timezone: $tz);
        $this->assertSame($tz, $schedule->getTimezone());
    }
    
    public function testGetNextRunDateMethod()
    {
        $this->assertInstanceOf(
            DateTimeInterface::class,
            (new CronExpression('* * * * *'))->getNextRunDate(now: new DateTime())
        );
    }

    public function testGetNextRunDateMethodWithoutCurrentDate()
    {
        $this->assertSame(
            '2023-11-15 16:16:00 +01:00',
            (new CronExpression('* * * * *'))
                ->getNextRunDate(now: new DateTime('2023-11-15 16:15'), allowCurrentDate: false)
                ->format('Y-m-d H:i:s P')
        );
    }
    
    public function testGetNextRunDateMethodWithDifferentTimezone()
    {
        $this->assertSame(
            '2023-11-15 15:15:00 +00:00',
            (new CronExpression('* * * * *', 'Europe/London'))
                ->getNextRunDate(now: new DateTime('2023-11-15 16:15', new DateTimeZone('Europe/Berlin')))
                ->format('Y-m-d H:i:s P')
        );
    }
    
    public function testGetNextRunDatesMethod()
    {
        $this->assertEquals([
            new DateTime('2023-11-15 16:15:00'),
            new DateTime('2023-11-15 16:16:00'),
            new DateTime('2023-11-15 16:17:00'),
        ], (new CronExpression('* * * * *'))->getNextRunDates(
            now: new DateTime('2023-11-15 16:15'),
            allowCurrentDate: true,
            maxNumber: 3,
        ));
    }
    
    public function testGetNextRunDatesMethodWithoutCurrentDate()
    {
        $this->assertEquals([
            new DateTime('2023-11-15 16:16:00'),
            new DateTime('2023-11-15 16:17:00'),
            new DateTime('2023-11-15 16:18:00'),
        ], (new CronExpression('* * * * *'))->getNextRunDates(
            now: new DateTime('2023-11-15 16:15'),
            allowCurrentDate: false,
            maxNumber: 3,
        ));
    }
    
    public function testGetNextRunDatesMethodWithDifferentTimezone()
    {
        $runDates = (new CronExpression('* * * * *', 'Europe/London'))->getNextRunDates(
            now: new DateTime('2023-11-15 16:15', new DateTimeZone('Europe/Berlin')),
            allowCurrentDate: true,
            maxNumber: 3,
        );
        
        $this->assertEquals([
            '2023-11-15 15:15:00 +00:00',
            '2023-11-15 15:16:00 +00:00',
            '2023-11-15 15:17:00 +00:00',
        ], array_map(function ($date) {
            return $date->format('Y-m-d H:i:s P');
        }, $runDates));
    }
}