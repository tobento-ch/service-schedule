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

namespace Tobento\Service\Schedule\Parameter;

use Tobento\Service\Schedule\TaskInterface;
use Tobento\Service\Schedule\TaskResultInterface;
use Tobento\Service\Mail\MailerInterface;
use Tobento\Service\Mail\MessageInterface;
use Tobento\Service\Mail\Message;

/**
 * Mail.
 */
class Mail extends Parameter implements BeforeTaskHandler, AfterTaskHandler, FailedTaskHandler
{
    /**
     * Create a new Email.
     *
     * @param Message $message
     * @param array $handle
     */
    public function __construct(
        protected Message $message,
        protected array $handle = ['before', 'after', 'failed'],
    ) {}

    /**
     * Returns the message.
     *
     * @return Message
     */
    public function getMessage(): Message
    {
        return $this->message;
    }
    
    /**
     * Returns the before task handler.
     *
     * @return callable
     */
    public function getBeforeTaskHandler(): callable
    {
        return [$this, 'beforeTask'];
    }
    
    /**
     * Returns the after task handler.
     *
     * @return callable
     */
    public function getAfterTaskHandler(): callable
    {
        return [$this, 'afterTask'];
    }
    
    /**
     * Returns the failed task handler.
     *
     * @return callable
     */
    public function getFailedTaskHandler(): callable
    {
        return [$this, 'failedTask'];
    }
    
    /**
     * Before task.
     *
     * @param TaskInterface $task
     * @param MailerInterface $mailer
     * @return void
     */
    public function beforeTask(TaskInterface $task, MailerInterface $mailer): void
    {
        if (!in_array('before', $this->handle)) {
            return;
        }
        
        if (empty($this->message->getSubject())) {
            $this->message->subject($task->getName());
        }
        
        $this->message->subject('Task Starting: '.$this->message->getSubject());
        
        $this->message->text(sprintf(
            "Task Status: %s\n\nTask ID: %s\n\nTask Name: %s\n\nTask Description: %s",
            'Starting',
            $task->getId(),
            $task->getName(),
            $task->getDescription(),
        ));
        
        $mailer->send($this->message);
    }
    
    /**
     * After task.
     *
     * @param TaskInterface $task
     * @param MailerInterface $mailer
     * @return void
     */
    public function afterTask(TaskResultInterface $result, MailerInterface $mailer): void
    {
        if (!in_array('after', $this->handle)) {
            return;
        }
        
        if (empty($this->message->getSubject())) {
            $this->message->subject($result->task()->getName());
        }
        
        $this->message->subject('Task Success: '.$this->message->getSubject());
        
        $this->message->text(sprintf(
            "Task Status: %s\n\nTask ID: %s\n\nTask Name: %s\n\nTask Description: %s\n\nTask Output: %s",
            'Success',
            $result->task()->getId(),
            $result->task()->getName(),
            $result->task()->getDescription(),
            $result->output(),
        ));
        
        $mailer->send($this->message);
    }
    
    /**
     * Failed task.
     *
     * @param TaskInterface $task
     * @param MailerInterface $mailer
     * @return void
     */
    public function failedTask(TaskResultInterface $result, MailerInterface $mailer): void
    {
        if (!in_array('failed', $this->handle)) {
            return;
        }
        
        if (empty($this->message->getSubject())) {
            $this->message->subject($result->task()->getName());
        }
        
        $this->message->subject('Task Failed: '.$this->message->getSubject());
        
        $this->message->text(sprintf(
            "Task Status: %s\n\nTask ID: %s\n\nTask Name: %s\n\nTask Description: %s\n\nTask Output: %s\n\nTask Exception: %s",
            'Failed',
            $result->task()->getId(),
            $result->task()->getName(),
            $result->task()->getDescription(),
            $result->output(),
            (string)$result->exception()?->__toString(),
        ));
        
        $mailer->send($this->message);
    }
}