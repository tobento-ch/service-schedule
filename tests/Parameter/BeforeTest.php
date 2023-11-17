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
use Tobento\Service\Schedule\Parameter\Before;
use Tobento\Service\Schedule\Parameter\BeforeTaskHandler;
use Tobento\Service\Schedule\ParameterInterface;
use Tobento\Service\Schedule\Task;
use Tobento\Service\Container\Container;

class BeforeTest extends TestCase
{
    public function testThatImplementsInterfaces()
    {
        $param = new Before(function() {});
        
        $this->assertInstanceof(ParameterInterface::class, $param);
        $this->assertInstanceof(BeforeTaskHandler::class, $param);
    }

    public function testGetNameMethod()
    {
        $param = new Before(function() {});
        
        $this->assertSame(Before::class, $param->getName());
    }
    
    public function testGetPriorityMethod()
    {
        $param = new Before(function() {});
        $this->assertSame(0, $param->getPriority());
        
        $param = new Before(function() {}, priority: 100);
        $this->assertSame(100, $param->getPriority());
    }
    
    public function testGetPriorityMethodUsesHandlersPriority()
    {
        $handler = new class() implements BeforeTaskHandler {
            public function getBeforeTaskHandler(): callable
            {
                return [$this, '__invoke'];
            }
            
            public function getPriority(): int
            {
                return 50;
            }
            
            public function __invoke() {}
        };
        
        $param = new Before($handler, priority: 100);
        $this->assertSame(50, $param->getPriority());
    }    
    
    public function testGetBeforeTaskHandlerMethod()
    {
        $param = new Before(function() {});
        
        $this->assertTrue(is_callable($param->getBeforeTaskHandler()));
    }
    
    public function testBeforeTaskMethodClosure()
    {
        $container = new Container();
        $task = new Task\CallableTask(function() {});
        
        $param = new Before(function(Foo $foo) use ($container) {
            $container->set('isCalled', true);
        });
        
        $param->beforeTask($task, $container);
        
        $this->assertTrue($container->has('isCalled'));
    }
    
    public function testBeforeTaskMethodWithHandler()
    {
        $container = new Container();
        $task = new Task\CallableTask(function() {});
        
        $handler = new class() implements BeforeTaskHandler {
            public bool $isCalled = false;
            
            public function getBeforeTaskHandler(): callable
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
        
        $param = new Before($handler);
        
        $param->beforeTask($task, $container);
        
        $this->assertTrue($handler->isCalled);
    }
}