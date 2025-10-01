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

use Tobento\Service\Notifier\Notification;
use Tobento\Service\Notifier\NotifierInterface;
use Tobento\Service\Notifier\Parameter\Queue;
use Tobento\Service\Notifier\RecipientInterface;
use Tobento\Service\Schedule\Parameter\AfterTaskHandler;
use Tobento\Service\Schedule\Parameter\BeforeTaskHandler;
use Tobento\Service\Schedule\Parameter\FailedTaskHandler;
use Tobento\Service\Schedule\Parameter\Monitor;
use Tobento\Service\Schedule\Parameter\Parameter;
use Tobento\Service\Schedule\TaskInterface;
use Tobento\Service\Schedule\TaskResultInterface;

/**
 * Notifies the specified recipient(s).
 */
class Notify extends Parameter implements BeforeTaskHandler, AfterTaskHandler, FailedTaskHandler
{
    /**
     * @var array<array-key, RecipientInterface> $recipients
     */
    protected array $recipients;
    
    /**
     * Create a new Notify instance.
     *
     * @param array<array-key, RecipientInterface>|RecipientInterface $recipient
     * @param string $subject
     * @param null|string $queueName
     * @param array $handle
     */
    public function __construct(
        array|RecipientInterface $recipient,
        protected string $subject = 'Task :status: :name',
        protected null|string $queueName = null,
        protected array $handle = ['before', 'after', 'failed'],
    ) {
        if (!is_array($recipient)) {
            $recipient = [$recipient];
        }
        
        $this->recipients = $recipient;
    }
    
    /**
     * Returns the recipients.
     *
     * @return array<array-key, RecipientInterface>
     */
    public function getRecipients(): array
    {
        return $this->recipients;
    }
    
    /**
     * Returns the subject.
     *
     * @return string
     */
    public function getSubject(): string
    {
        return $this->subject;
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
     * @param NotifierInterface $notifier
     * @return void
     */
    public function beforeTask(TaskInterface $task, NotifierInterface $notifier): void
    {
        if (!in_array('before', $this->handle)) {
            return;
        }
        
        $subject = trim(strtr($this->subject, [
            ':status' => 'Starting',
            ':id' => $task->getId(),
            ':name' => $task->getName(),
            ':description' => $task->getDescription(),
        ]));
        
        $notification = (new Notification(
            subject: $subject,
            content: sprintf(
                "Task Status: %s\n\nTask ID: %s\n\nTask Name: %s\n\nTask Description: %s",
                'Starting',
                $task->getId(),
                $task->getName(),
                $task->getDescription(),
            ),
        ));
        
        if ($this->queueName) {
            $notification->parameter(new Queue(name: $this->queueName));
        }
        
        foreach($this->recipients as $recipient) {
            $notifier->send($notification, $recipient);
        }
    }
    
    /**
     * After task.
     *
     * @param TaskResultInterface $result
     * @param NotifierInterface $notifier
     * @return void
     */
    public function afterTask(TaskResultInterface $result, NotifierInterface $notifier): void
    {
        if (!in_array('after', $this->handle)) {
            return;
        }

        $subject = trim(strtr($this->subject, [
            ':status' => 'Success',
            ':id' => $result->task()->getId(),
            ':name' => $result->task()->getName(),
            ':description' => $result->task()->getDescription(),
        ]));

        $notification = (new Notification(
            subject: $subject,
            content: sprintf(
                "Task Status: %s\n\nTask ID: %s\n\nTask Name: %s\n\nTask Description: %s\n\nTask Output: %s",
                'Success',
                $result->task()->getId(),
                $result->task()->getName(),
                $result->task()->getDescription(),
                $result->output(),
            ),
        ));
        
        if ($this->queueName) {
            $notification->parameter(new Queue(name: $this->queueName));
        }
        
        foreach($this->recipients as $recipient) {
            $notifier->send($notification, $recipient);
        }
    }
    
    /**
     * Failed task.
     *
     * @param TaskResultInterface $result
     * @param NotifierInterface $notifier
     * @return void
     */
    public function failedTask(TaskResultInterface $result, NotifierInterface $notifier): void
    {
        if (!in_array('failed', $this->handle)) {
            return;
        }
        
        $subject = trim(strtr($this->subject, [
            ':status' => 'Failed',
            ':id' => $result->task()->getId(),
            ':name' => $result->task()->getName(),
            ':description' => $result->task()->getDescription(),
        ]));
        
        $notification = (new Notification(
            subject: $subject,
            content: sprintf(
                "Task Status: %s\n\nTask ID: %s\n\nTask Name: %s\n\nTask Description: %s\n\nTask Output: %s\n\nTask Exception: %s",
                'Failed',
                $result->task()->getId(),
                $result->task()->getName(),
                $result->task()->getDescription(),
                $result->output(),
                (string)$result->exception()?->__toString(),
            ),
        ));
        
        if ($this->queueName) {
            $notification->parameter(new Queue(name: $this->queueName));
        }
        
        foreach($this->recipients as $recipient) {
            $notifier->send($notification, $recipient);
        }
    }
}