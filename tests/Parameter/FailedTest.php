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
use Tobento\Service\Schedule\Parameter\Failed;
use Tobento\Service\Schedule\Parameter\FailedTaskHandler;
use Tobento\Service\Schedule\ParameterInterface;
use Tobento\Service\Schedule\TaskResult;
use Tobento\Service\Schedule\Task;
use Tobento\Service\Container\Container;

class FailedTest extends TestCase
{
    public function testThatImplementsInterfaces()
    {
        $param = new Failed(function() {});
        
        $this->assertInstanceof(ParameterInterface::class, $param);
        $this->assertInstanceof(FailedTaskHandler::class, $param);
    }

    public function testGetNameMethod()
    {
        $param = new Failed(function() {});
        
        $this->assertSame(Failed::class, $param->getName());
    }
    
    public function testGetPriorityMethod()
    {
        $param = new Failed(function() {});
        $this->assertSame(0, $param->getPriority());
        
        $param = new Failed(function() {}, priority: 100);
        $this->assertSame(100, $param->getPriority());
    }
    
    public function testGetPriorityMethodUsesHandlersPriority()
    {
        $handler = new class() implements FailedTaskHandler {
            public function getFailedTaskHandler(): callable
            {
                return [$this, '__invoke'];
            }
            
            public function getPriority(): int
            {
                return 50;
            }
            
            public function __invoke() {}
        };
        
        $param = new Failed($handler, priority: 100);
        $this->assertSame(50, $param->getPriority());
    }    
    
    public function testGetFailedTaskHandlerMethod()
    {
        $param = new Failed(function() {});
        
        $this->assertTrue(is_callable($param->getFailedTaskHandler()));
    }
    
    public function testFailedTaskMethodClosure()
    {
        $container = new Container();
        $result = new TaskResult(task: new Task\CallableTask(function() {}));
        
        $param = new Failed(function(Foo $foo) use ($container) {
            $container->set('isCalled', true);
        });
        
        $param->failedTask($result, $container);
        
        $this->assertTrue($container->has('isCalled'));
    }
    
    public function testFailedTaskMethodWithHandler()
    {
        $container = new Container();
        $result = new TaskResult(task: new Task\CallableTask(function() {}));
        
        $handler = new class() implements FailedTaskHandler {
            public bool $isCalled = false;
            
            public function getFailedTaskHandler(): callable
            {
                return [$this, '__invoke'];
            }
            
            public function getPriority(): int
            {
                return 50;
            }
            
            public function __invoke(Foo $foo)
            {
                $this->isCalled = true;
            }
        };
        
        $param = new Failed($handler);
        
        $param->failedTask($result, $container);
        
        $this->assertTrue($handler->isCalled);
    }
}