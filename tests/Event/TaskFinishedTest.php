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
use Tobento\Service\Schedule\Event\TaskFinished;
use Tobento\Service\Schedule\TaskResult;
use Tobento\Service\Schedule\Task;

class TaskFinishedTest extends TestCase
{
    public function testEvent()
    {
        $result = new TaskResult(task: new Task\CallableTask(function() {}));
        $event = new TaskFinished(result: $result);
        
        $this->assertSame($result, $event->result());
    }
}