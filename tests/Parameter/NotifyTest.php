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
use Tobento\Service\Notifier\Channels;
use Tobento\Service\Notifier\Notifier;
use Tobento\Service\Notifier\NotifierInterface;
use Tobento\Service\Notifier\NullChannel;
use Tobento\Service\Notifier\Recipient;
use Tobento\Service\Schedule\Parameter\AfterTaskHandler;
use Tobento\Service\Schedule\Parameter\BeforeTaskHandler;
use Tobento\Service\Schedule\Parameter\FailedTaskHandler;
use Tobento\Service\Schedule\Parameter\Notify;
use Tobento\Service\Schedule\ParameterInterface;
use Tobento\Service\Schedule\Task;
use Tobento\Service\Schedule\TaskResult;

class NotifyTest extends TestCase
{
    protected function createNotifier(): NotifierInterface
    {
        return new Notifier(channels: new Channels(
            new NullChannel(),
        ));
    }
    
    public function testThatImplementsInterfaces()
    {
        $param = new Notify(recipient: new Recipient(email: 'mail@example.com'));
        
        $this->assertInstanceof(ParameterInterface::class, $param);
        $this->assertInstanceof(AfterTaskHandler::class, $param);
        $this->assertInstanceof(BeforeTaskHandler::class, $param);
        $this->assertInstanceof(FailedTaskHandler::class, $param);
    }

    public function testGetNameMethod()
    {
        $param = new Notify(recipient: new Recipient(email: 'mail@example.com'));
        
        $this->assertSame(Notify::class, $param->getName());
    }
    
    public function testGetPriorityMethod()
    {
        $param = new Notify(recipient: new Recipient(email: 'mail@example.com'));
        $this->assertSame(0, $param->getPriority());
    }
    
    public function testSpecificMethods()
    {
        $recipient = new Recipient(email: 'mail@example.com');
        $param = new Notify(recipient: $recipient, subject: 'Subject');
        $this->assertSame([$recipient], $param->getRecipients());
        $this->assertSame('Subject', $param->getSubject());
    }
    
    public function testAfterTaskMethod()
    {
        $param = new Notify(recipient: new Recipient(email: 'mail@example.com'));
        
        $result = new TaskResult(
            task: (new Task\CallableTask(function() {
                return 'task output';
            }))->id('foo')->name('Foo')->description('Lorem'),
            output: 'task output',
        );
        
        $param->getAfterTaskHandler()($result, $this->createNotifier());
        
        $this->assertTrue(true);
    }
    
    public function testBeforeTaskMethod()
    {
        $param = new Notify(recipient: new Recipient(email: 'mail@example.com'));
        
        $task = (new Task\CallableTask(function() {
            return 'task output';
        }))->id('foo')->name('Foo')->description('Lorem');
        
        $param->getBeforeTaskHandler()($task, $this->createNotifier());
        
        $this->assertTrue(true);
    }
    
    public function testFailedTaskMethod()
    {
        $param = new Notify(recipient: new Recipient(email: 'mail@example.com'));
        
        $result = new TaskResult(
            task: (new Task\CallableTask(function() {
                return 'task output';
            }))->id('foo')->name('Foo')->description('Lorem'),
            output: 'task output',
        );
        
        $param->getFailedTaskHandler()($result, $this->createNotifier());
        
        $this->assertTrue(true);
    }
}