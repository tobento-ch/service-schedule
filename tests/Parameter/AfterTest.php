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
use Tobento\Service\Schedule\Parameter\After;
use Tobento\Service\Schedule\Parameter\AfterTaskHandler;
use Tobento\Service\Schedule\ParameterInterface;
use Tobento\Service\Schedule\TaskResult;
use Tobento\Service\Schedule\Task;
use Tobento\Service\Container\Container;

class AfterTest extends TestCase
{
    public function testThatImplementsInterfaces()
    {
        $param = new After(function() {});
        
        $this->assertInstanceof(ParameterInterface::class, $param);
        $this->assertInstanceof(AfterTaskHandler::class, $param);
    }

    public function testGetNameMethod()
    {
        $param = new After(function() {});
        
        $this->assertSame(After::class, $param->getName());
    }
    
    public function testGetPriorityMethod()
    {
        $param = new After(function() {});
        $this->assertSame(0, $param->getPriority());
        
        $param = new After(function() {}, priority: 100);
        $this->assertSame(100, $param->getPriority());
    }
    
    public function testGetPriorityMethodUsesHandlersPriority()
    {
        $handler = new class() implements AfterTaskHandler {
            public function getAfterTaskHandler(): callable
            {
                return [$this, '__invoke'];
            }
            
            public function getPriority(): int
            {
                return 50;
            }
            
            public function __invoke() {}
        };
        
        $param = new After($handler, priority: 100);
        $this->assertSame(50, $param->getPriority());
    }    
    
    public function testGetAfterTaskHandlerMethod()
    {
        $param = new After(function() {});
        
        $this->assertTrue(is_callable($param->getAfterTaskHandler()));
    }
    
    public function testAfterTaskMethodClosure()
    {
        $container = new Container();
        $result = new TaskResult(task: new Task\CallableTask(function() {}));
        
        $param = new After(function(Foo $foo) use ($container) {
            $container->set('isCalled', true);
        });
        
        $param->afterTask($result, $container);
        
        $this->assertTrue($container->has('isCalled'));
    }
    
    public function testAfterTaskMethodWithHandler()
    {
        $container = new Container();
        $result = new TaskResult(task: new Task\CallableTask(function() {}));
        
        $handler = new class() implements AfterTaskHandler {
            public bool $isCalled = false;
            
            public function getAfterTaskHandler(): callable
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
        
        $param = new After($handler);
        
        $param->afterTask($result, $container);
        
        $this->assertTrue($handler->isCalled);
    }
}