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
use Tobento\Service\Schedule\TaskSkipException;
use Tobento\Service\Schedule\TaskException;
use Tobento\Service\Schedule\Task;

class TaskSkipExceptionTest extends TestCase
{
    public function testException()
    {
        $this->assertInstanceof(TaskException::class, new TaskSkipException());
    }
}