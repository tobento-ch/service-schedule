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

namespace Tobento\Service\Schedule\Test\Parameter;

use PHPUnit\Framework\TestCase;
use Tobento\Service\Schedule\Test\Mock\Foo;
use Tobento\Service\Schedule\Parameter\Skip;
use Tobento\Service\Schedule\Parameter\BeforeTaskHandler;
use Tobento\Service\Schedule\ParameterInterface;
use Tobento\Service\Schedule\Task;
use Tobento\Service\Schedule\TaskSkipException;
use Tobento\Service\Container\Container;

class SkipTest extends TestCase
{
    public function testThatImplementsInterfaces()
    {
        $param = new Skip(true);
        
        $this->assertInstanceof(ParameterInterface::class, $param);
        $this->assertInstanceof(BeforeTaskHandler::class, $param);
    }

    public function testGetNameMethod()
    {
        $param = new Skip(true);
        
        $this->assertSame(Skip::class, $param->getName());
    }
    
    public function testGetPriorityMethod()
    {
        $param = new Skip(true);
        $this->assertSame(100000, $param->getPriority());
    }
    
    public function testIsSkipping()
    {
        $this->expectException(TaskSkipException::class);
        $this->expectExceptionMessage('reason');
        
        $param = new Skip(true, reason: 'reason');
        
        $task = (new Task\CallableTask(function() {}));
        
        $param->getBeforeTaskHandler()($task, new Container());
    }
    
    public function testIsNotSkipping()
    {
        $param = new Skip(false);
        
        $task = (new Task\CallableTask(function() {}));
        
        $param->getBeforeTaskHandler()($task, new Container());
        
        $this->assertTrue(true);
    }
    
    public function testIsSkippingWithClosureAutowired()
    {
        $this->expectException(TaskSkipException::class);
        $this->expectExceptionMessage('reason');
        
        $param = new Skip(function (Foo $foo) {
            return true;
        }, reason: 'reason');
        
        $task = (new Task\CallableTask(function() {}));
        
        $param->getBeforeTaskHandler()($task, new Container());
    }
    
    public function testIsNotSkippingWithClosureAutowired()
    {
        $param = new Skip(function (Foo $foo) {
            return false;
        }, reason: 'reason');
        
        $task = (new Task\CallableTask(function() {}));
        
        $param->getBeforeTaskHandler()($task, new Container());
        
        $this->assertTrue(true);
    }    
}