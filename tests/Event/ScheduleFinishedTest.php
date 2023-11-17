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
use Tobento\Service\Schedule\Event\ScheduleFinished;
use Tobento\Service\Schedule\Schedule;
use Tobento\Service\Schedule\TaskResults;

class ScheduleFinishedTest extends TestCase
{
    public function testEvent()
    {
        $schedule = new Schedule(name: 'default');
        $results = new TaskResults();
        $event = new ScheduleFinished(schedule: $schedule, results: $results);
        
        $this->assertSame($schedule, $event->schedule());
        $this->assertSame($results, $event->results());
    }
}