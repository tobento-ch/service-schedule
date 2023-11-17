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
use Tobento\Service\Schedule\Parameter\Mail;
use Tobento\Service\Schedule\Parameter\AfterTaskHandler;
use Tobento\Service\Schedule\Parameter\BeforeTaskHandler;
use Tobento\Service\Schedule\Parameter\FailedTaskHandler;
use Tobento\Service\Schedule\ParameterInterface;
use Tobento\Service\Schedule\TaskResult;
use Tobento\Service\Schedule\Task;
use Tobento\Service\Mail\Message;
use Tobento\Service\Mail\Symfony\EmailFactory;
use Tobento\Service\Mail\Symfony\Mailer;
use Symfony\Component\Mailer\Transport\NullTransport;
use Tobento\Service\Mail\MailerInterface;
use Tobento\Service\Mail\ViewRenderer;
use Tobento\Service\View;
use Tobento\Service\Dir;

class MailTest extends TestCase
{
    public function testThatImplementsInterfaces()
    {
        $param = new Mail(message: (new Message())->to('admin@example.com'));
        
        $this->assertInstanceof(ParameterInterface::class, $param);
        $this->assertInstanceof(AfterTaskHandler::class, $param);
        $this->assertInstanceof(BeforeTaskHandler::class, $param);
        $this->assertInstanceof(FailedTaskHandler::class, $param);
    }

    public function testGetNameMethod()
    {
        $param = new Mail(message: (new Message())->to('admin@example.com'));
        
        $this->assertSame(Mail::class, $param->getName());
    }
    
    public function testGetPriorityMethod()
    {
        $param = new Mail(message: (new Message())->to('admin@example.com'));
        $this->assertSame(0, $param->getPriority());
    }
    
    public function testSpecificMethods()
    {
        $message = (new Message())->to('admin@example.com');
        $param = new Mail(message: $message);
        $this->assertSame($message, $param->getMessage());
    }
    
    public function testAfterTaskMethod()
    {
        $message = (new Message())->to('admin@example.com');
        
        $param = new Mail(message: $message);
        
        $result = new TaskResult(
            task: (new Task\CallableTask(function() {
                return 'task output';
            }))->id('foo')->name('Foo')->description('Lorem'),
            output: 'task output',
        );
        
        $param->getAfterTaskHandler()($result, $this->createMailer());
        
        $this->assertSame('Task Success: Foo', $message->getSubject());
        $this->assertStringContainsString('Task Status: Success', $message->getText());
        $this->assertStringContainsString('Task ID: foo', $message->getText());
        $this->assertStringContainsString('Task Name: Foo', $message->getText());
        $this->assertStringContainsString('Task Description: Lorem', $message->getText());
        $this->assertStringContainsString('Task Output: task output', $message->getText());
    }
    
    public function testBeforeTaskMethod()
    {
        $message = (new Message())->to('admin@example.com');
        
        $param = new Mail(message: $message);
        
        $task = (new Task\CallableTask(function() {
            return 'task output';
        }))->id('foo')->name('Foo')->description('Lorem');
        
        $param->getBeforeTaskHandler()($task, $this->createMailer());
        
        $this->assertSame('Task Starting: Foo', $message->getSubject());
        $this->assertStringContainsString('Task Status: Starting', $message->getText());
        $this->assertStringContainsString('Task ID: foo', $message->getText());
        $this->assertStringContainsString('Task Name: Foo', $message->getText());
        $this->assertStringContainsString('Task Description: Lorem', $message->getText());
    }
    
    public function testFailedTaskMethod()
    {
        $message = (new Message())->to('admin@example.com');
        
        $param = new Mail(message: $message);
        
        $result = new TaskResult(
            task: (new Task\CallableTask(function() {
                return 'task output';
            }))->id('foo')->name('Foo')->description('Lorem'),
            output: 'task output',
        );
        
        $param->getFailedTaskHandler()($result, $this->createMailer());
        
        $this->assertSame('Task Failed: Foo', $message->getSubject());
        $this->assertStringContainsString('Task Status: Failed', $message->getText());
        $this->assertStringContainsString('Task ID: foo', $message->getText());
        $this->assertStringContainsString('Task Name: Foo', $message->getText());
        $this->assertStringContainsString('Task Description: Lorem', $message->getText());
        $this->assertStringContainsString('Task Output: task output', $message->getText());
    }
    
    protected function createMailer(): MailerInterface
    {
        // create the renderer:
        $renderer = new ViewRenderer(
            new View\View(
                new View\PhpRenderer(
                    new Dir\Dirs(
                        new Dir\Dir(__DIR__.'/../views/'),
                    )
                ),
                new View\Data(),
                new View\Assets(__DIR__.'/../src/', 'https://example.com/src/')
            )
        );
        
        // create email factory:
        $emailFactory = new EmailFactory(renderer: $renderer, config: [
            'from' => 'from@example.com',
        ]);

        // create the mailer:
        return new Mailer(
            name: 'default',
            emailFactory: $emailFactory,
            transport: new NullTransport(),
        );
    }
}