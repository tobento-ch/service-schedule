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
use Tobento\Service\Schedule\Event\TaskStarting;
use Tobento\Service\Schedule\Task;

class TaskStartingTest extends TestCase
{
    public function testEvent()
    {
        $task = new Task\CallableTask(function() {});
        $event = new TaskStarting(task: $task);
        
        $this->assertSame($task, $event->task());
    }
}