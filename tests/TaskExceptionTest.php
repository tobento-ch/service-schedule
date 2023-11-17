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
use Tobento\Service\Schedule\TaskException;
use Tobento\Service\Schedule\Task;

class TaskExceptionTest extends TestCase
{
    public function testException()
    {
        $this->assertSame(null, (new TaskException())->task());
    }
    
    public function testExceptionWithTask()
    {
        $task = new Task\CallableTask(function() {});
        
        $this->assertSame($task, (new TaskException(task: $task))->task());
    }
}