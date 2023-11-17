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

namespace Tobento\Service\Schedule\Test;

use PHPUnit\Framework\TestCase;
use Tobento\Service\Schedule\Schedule;
use Tobento\Service\Schedule\ScheduleInterface;
use Tobento\Service\Schedule\Task;

class ScheduleTest extends TestCase
{
    public function testThatImplementsScheduleInterface()
    {
        $this->assertInstanceof(ScheduleInterface::class, new Schedule(name: 'default'));
    }
    
    public function testGetNameMethod()
    {
        $this->assertSame('default', (new Schedule(name: 'default'))->getName());
    }
    
    public function testTaskMethod()
    {
        $schedule = new Schedule(name: 'default');
        
        $this->assertCount(0, $schedule->all());
        
        $schedule->task(new Task\CommandTask('command:name'));
        
        $this->assertCount(1, $schedule->all());
    }
    
    public function testGetTaskMethod()
    {
        $schedule = new Schedule(name: 'default');
        
        $this->assertNull($schedule->getTask(id: 'foo'));
        
        $task = (new Task\CommandTask('command:name'))->id('foo');
        $schedule->task($task);
        
        $this->assertSame($task, $schedule->getTask(id: 'foo'));
    }
    
    public function testAllMethod()
    {
        $schedule = new Schedule(name: 'default');
        
        $this->assertCount(0, $schedule->all());
        
        $schedule->task(new Task\CommandTask('command:foo'));
        $schedule->task(new Task\CommandTask('command:bar'));
        
        $this->assertCount(2, $schedule->all());
    }
    
    public function testCountMethod()
    {
        $schedule = new Schedule(name: 'default');
        
        $this->assertSame(0, $schedule->count());
        
        $schedule->task(new Task\CommandTask('command:foo'));
        $schedule->task(new Task\CommandTask('command:bar'));
        
        $this->assertSame(2, $schedule->count());
    }
}