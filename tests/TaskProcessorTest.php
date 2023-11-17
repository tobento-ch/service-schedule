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
use Tobento\Service\Schedule\Test\Mock;
use Tobento\Service\Schedule\TaskProcessor;
use Tobento\Service\Schedule\TaskProcessorInterface;
use Tobento\Service\Schedule\TaskResultInterface;
use Tobento\Service\Schedule\Task;
use Tobento\Service\Schedule\TaskSkipException;
use Tobento\Service\Container\Container;
use Tobento\Service\Collection\Collection;

class TaskProcessorTest extends TestCase
{
    public function testThatImplementsTaskProcessorInterface()
    {
        $processor = new TaskProcessor(container: new Container());
        
        $this->assertInstanceof(TaskProcessorInterface::class, $processor);
    }
    
    public function testProcessesTask()
    {
        $processor = new TaskProcessor(container: new Container());
        $task = (new Task\CallableTask(function() {}));
        $result = $processor->processTask($task);
        
        $this->assertInstanceof(TaskResultInterface::class, $result);
        $this->assertSame($task, $result->task());
        $this->assertTrue($result->isSuccessful());
    }
    
    public function testProcessesTaskFailing()
    {
        $processor = new TaskProcessor(container: new Container());
        $task = (new Task\CallableTask(function() { throw new \Exception('error'); }));
        $result = $processor->processTask($task);
        
        $this->assertSame($task, $result->task());
        $this->assertTrue($result->isFailure());
    }
    
    public function testDoesNotProcessSkippedTask()
    {
        $container = new Container();
        $processor = new TaskProcessor(container: $container);
        $task = (new Task\CallableTask(function() use ($container) {
            $container->set('processed', true);
        }))
        ->before(function(Mock\Foo $foo) {
            throw new TaskSkipException(message: 'reason');
        });
        
        $result = $processor->processTask($task);
        
        $this->assertSame($task, $result->task());
        $this->assertTrue($result->isSkipped());
        $this->assertFalse($container->has('processed'));
    }
    
    public function testProcessesBeforeHandlersBeingAutowired()
    {
        $container = new Container();
        $processor = new TaskProcessor(container: $container);
        $task = (new Task\CallableTask(function() {}))
            ->before(function(Mock\Foo $foo) use ($container) {
                $container->set('before1', true);
            })
            ->before(function() use ($container) {
                $container->set('before2', true);
            });
        
        $result = $processor->processTask($task);
        
        $this->assertTrue($container->has('before1'));
        $this->assertTrue($container->has('before2'));
    }
    
    public function testProcessesBeforeHandlersHighestPriorityFirst()
    {
        $container = new Container();
        $processor = new TaskProcessor(container: $container);
        $collection = new Collection();
        $task = (new Task\CallableTask(function() {}))
            ->before(new Mock\Param(name: 'C', handler: function() use ($collection) {
                $collection->set('C', true);
            }, priority: 3))
            ->before(new Mock\Param(name: 'A', handler: function() use ($collection) {
                $collection->set('A', true);
            }, priority: 1))
            ->before(new Mock\Param(name: 'B', handler: function() use ($collection) {
                $collection->set('B', true);
            }, priority: 2));
        
        $result = $processor->processTask($task);
        
        $this->assertSame(['C', 'B', 'A'], array_keys($collection->all()));
    }
    
    public function testProcessesAfterHandlersBeingAutowired()
    {
        $container = new Container();
        $processor = new TaskProcessor(container: $container);
        $task = (new Task\CallableTask(function() {}))
            ->after(function(Mock\Foo $foo) use ($container) {
                $container->set('after1', true);
            })
            ->after(function() use ($container) {
                $container->set('after2', true);
            });
        
        $result = $processor->processTask($task);
        
        $this->assertTrue($container->has('after1'));
        $this->assertTrue($container->has('after2'));
    }
    
    public function testProcessesAfterHandlersHighestPriorityLast()
    {
        $container = new Container();
        $processor = new TaskProcessor(container: $container);
        $collection = new Collection();
        $task = (new Task\CallableTask(function() {}))
            ->after(new Mock\Param(name: 'C', handler: function() use ($collection) {
                $collection->set('C', true);
            }, priority: 3))
            ->after(new Mock\Param(name: 'A', handler: function() use ($collection) {
                $collection->set('A', true);
            }, priority: 1))
            ->after(new Mock\Param(name: 'B', handler: function() use ($collection) {
                $collection->set('B', true);
            }, priority: 2));
        
        $result = $processor->processTask($task);
        
        $this->assertSame(['A', 'B', 'C'], array_keys($collection->all()));
    }
    
    public function testProcessesFailedHandlersBeingAutowired()
    {
        $container = new Container();
        $processor = new TaskProcessor(container: $container);
        $task = (new Task\CallableTask(function() { throw new \Exception('error'); }))
            ->failed(function(Mock\Foo $foo) use ($container) {
                $container->set('failed1', true);
            })
            ->failed(function() use ($container) {
                $container->set('failed2', true);
            });
        
        $result = $processor->processTask($task);
        
        $this->assertTrue($container->has('failed1'));
        $this->assertTrue($container->has('failed2'));
    }
    
    public function testProcessesFailedHandlersHighestPriorityFirst()
    {
        $container = new Container();
        $processor = new TaskProcessor(container: $container);
        $collection = new Collection();
        $task = (new Task\CallableTask(function() { throw new \Exception('error'); }))
            ->failed(new Mock\Param(name: 'C', handler: function() use ($collection) {
                $collection->set('C', true);
            }, priority: 3))
            ->failed(new Mock\Param(name: 'A', handler: function() use ($collection) {
                $collection->set('A', true);
            }, priority: 1))
            ->failed(new Mock\Param(name: 'B', handler: function() use ($collection) {
                $collection->set('B', true);
            }, priority: 2));
        
        $result = $processor->processTask($task);
        
        $this->assertSame(['C', 'B', 'A'], array_keys($collection->all()));
    }
}