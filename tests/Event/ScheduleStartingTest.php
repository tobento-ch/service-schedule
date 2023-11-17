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

namespace Tobento\Service\Schedule\Test\Event;

use PHPUnit\Framework\TestCase;
use Tobento\Service\Schedule\Event\ScheduleStarting;
use Tobento\Service\Schedule\Schedule;

class ScheduleStartingTest extends TestCase
{
    public function testEvent()
    {
        $schedule = new Schedule(name: 'default');
        $event = new ScheduleStarting(schedule: $schedule);
        
        $this->assertSame($schedule, $event->schedule());
    }
}