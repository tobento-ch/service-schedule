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
use Tobento\Service\Schedule\Parameter\WithoutOverlapping;
use Tobento\Service\Schedule\Parameter\AfterTaskHandler;
use Tobento\Service\Schedule\Parameter\BeforeTaskHandler;
use Tobento\Service\Schedule\Parameter\FailedTaskHandler;
use Tobento\Service\Schedule\ParameterInterface;
use Tobento\Service\Schedule\TaskResult;
use Tobento\Service\Schedule\Task;
use Tobento\Service\Schedule\TaskSkipException;
use Tobento\Service\Cache\Simple\Psr6Cache;
use Tobento\Service\Cache\ArrayCacheItemPool;
use Tobento\Service\Clock\SystemClock;
use Psr\SimpleCache\CacheInterface;

class WithoutOverlappingTest extends TestCase
{
    public function testThatImplementsInterfaces()
    {
        $param = new WithoutOverlapping();
        
        $this->assertInstanceof(ParameterInterface::class, $param);
        $this->assertInstanceof(AfterTaskHandler::class, $param);
        $this->assertInstanceof(BeforeTaskHandler::class, $param);
        $this->assertInstanceof(FailedTaskHandler::class, $param);
    }

    public function testGetNameMethod()
    {
        $param = new WithoutOverlapping();
        
        $this->assertSame(WithoutOverlapping::class, $param->getName());
    }
    
    public function testGetPriorityMethod()
    {
        $param = new WithoutOverlapping();
        $this->assertSame(100000, $param->getPriority());
    }
    
    public function testSpecificMethods()
    {
        $param = new WithoutOverlapping();
        $this->assertSame(null, $param->id());
        
        $param = new WithoutOverlapping(id: 'foo');
        $this->assertSame('foo', $param->id());
    }
    
    public function testThrowsTaskSkipExceptionIfTaskAlreadyRunning()
    {
        $this->expectException(TaskSkipException::class);
        $this->expectExceptionMessage('Task running in another process.');
        
        $cache = $this->createCache();
        $param = new WithoutOverlapping();
        
        $task = (new Task\CallableTask(function() {}))->id('foo');
        $param->getBeforeTaskHandler()($task, $cache);
        
        $task = (new Task\CallableTask(function() {}))->id('foo');
        $param->getBeforeTaskHandler()($task, $cache);
    }
    
    public function testAfterTaskWillDeleteCacheItem()
    {
        $cache = $this->createCache();
        $param = new WithoutOverlapping();
        
        $task = (new Task\CallableTask(function() {}))->id('foo');
        $param->getBeforeTaskHandler()($task, $cache);
        
        $param->getAfterTaskHandler()(new TaskResult(task: $task), $cache);
        
        $task = (new Task\CallableTask(function() {}))->id('foo');
        $param->getBeforeTaskHandler()($task, $cache);
        
        $this->assertTrue(true);
    }
    
    public function testFailedTaskWillDeleteCacheItem()
    {
        $cache = $this->createCache();
        $param = new WithoutOverlapping();
        
        $task = (new Task\CallableTask(function() {}))->id('foo');
        $param->getBeforeTaskHandler()($task, $cache);
        
        $param->getFailedTaskHandler()(new TaskResult(task: $task), $cache);
        
        $task = (new Task\CallableTask(function() {}))->id('foo');
        $param->getBeforeTaskHandler()($task, $cache);
        
        $this->assertTrue(true);
    }
    
    protected function createCache(): CacheInterface
    {
        return new Psr6Cache(
            pool: new ArrayCacheItemPool(
                clock: new SystemClock(),
            ),
            namespace: 'default',
            ttl: null,
        );
    }
}