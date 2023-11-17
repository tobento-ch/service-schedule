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
use Tobento\Service\Schedule\TaskResults;
use Tobento\Service\Schedule\TaskResultsInterface;
use Tobento\Service\Schedule\TaskResult;
use Tobento\Service\Schedule\TaskResultInterface;
use Tobento\Service\Schedule\Task;
use Tobento\Service\Schedule\TaskSkipException;
use Tobento\Service\Schedule\TaskException;

class TaskResultsTest extends TestCase
{
    public function testThatImplementsTaskResultsInterface()
    {
        $this->assertInstanceof(TaskResultsInterface::class, new TaskResults());
    }
    
    public function testAddMethod()
    {
        $result = new TaskResult(new Task\CallableTask(function() {}));
        $results = new TaskResults();
        $results->add($result);
        
        $this->assertSame($result, $results->all()[0] ?? null);
    }
    
    public function testAllMethod()
    {
        $results = new TaskResults();
        
        $this->assertCount(0, $results->all());
        
        $results->add(new TaskResult(new Task\CallableTask(function() {})));
        $results->add(new TaskResult(new Task\CallableTask(function() {})));
        
        $this->assertCount(2, $results->all());
    }
    
    public function testSuccessfulMethod()
    {
        $results = new TaskResults();
        $this->assertFalse($results === $results->successful());
        $this->assertCount(0, $results->successful());
        
        $task = new Task\CallableTask(function() {});
        $results->add(new TaskResult($task));
        $this->assertCount(1, $results->successful());
        
        $results->add(new TaskResult($task));
        $this->assertCount(2, $results->successful());
        
        $results->add(new TaskResult($task, exception: new \Exception()));
        $this->assertCount(2, $results->successful());
        
        $results->add(new TaskResult($task, exception: new TaskSkipException()));
        $this->assertCount(2, $results->successful());
    }
    
    public function testFailedMethod()
    {
        $results = new TaskResults();
        $this->assertFalse($results === $results->failed());
        $this->assertCount(0, $results->failed());
        
        $task = new Task\CallableTask(function() {});
        $results->add(new TaskResult($task));
        $this->assertCount(0, $results->failed());
        
        $results->add(new TaskResult($task, exception: new \Exception()));
        $this->assertCount(1, $results->failed());
        
        $results->add(new TaskResult($task, exception: new TaskSkipException()));
        $this->assertCount(1, $results->failed());
        
        $results->add(new TaskResult($task, exception: new TaskException()));
        $this->assertCount(2, $results->failed());
    }
    
    public function testSkippedMethod()
    {
        $results = new TaskResults();
        $this->assertFalse($results === $results->skipped());
        $this->assertCount(0, $results->skipped());
        
        $task = new Task\CallableTask(function() {});
        $results->add(new TaskResult($task));
        $this->assertCount(0, $results->skipped());
        
        $results->add(new TaskResult($task, exception: new \Exception()));
        $this->assertCount(0, $results->skipped());
        
        $results->add(new TaskResult($task, exception: new TaskSkipException()));
        $this->assertCount(1, $results->skipped());
        
        $results->add(new TaskResult($task, exception: new TaskSkipException()));
        $this->assertCount(2, $results->skipped());
    }
    
    public function testFilterMethod()
    {
        $results = new TaskResults();
        $task = new Task\CallableTask(function() {});
        $results->add(new TaskResult($task));
        $results->add(new TaskResult($task, exception: new \Exception()));
        
        $resultsNew = $results->filter(fn(TaskResultInterface $r): bool => $r->isSuccessful());
        
        $this->assertCount(2, $results->all());
        $this->assertFalse($results === $resultsNew);
        $this->assertCount(1, $resultsNew->all());
    }
    
    public function testGetIteratorMethod()
    {
        $results = new TaskResults();
        $task = new Task\CallableTask(function() {});
        $results->add(new TaskResult($task));
        $results->add(new TaskResult($task));
        
        $this->assertCount(2, $results->getIterator());
    }
    
    public function testCountMethod()
    {
        $results = new TaskResults();
        $task = new Task\CallableTask(function() {});
        
        $this->assertSame(0, $results->count());
        
        $results->add(new TaskResult($task));
        $results->add(new TaskResult($task));
        
        $this->assertSame(2, $results->count());
    }
}