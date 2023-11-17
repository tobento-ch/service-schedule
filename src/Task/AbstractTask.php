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

namespace Tobento\Service\Schedule\Task;

use Tobento\Service\Schedule\TaskInterface;
use Tobento\Service\Schedule\TaskScheduleInterface;
use Tobento\Service\Schedule\Task\Schedule\CronExpression;
use Tobento\Service\Schedule\TaskResultInterface;
use Tobento\Service\Schedule\HasParameters;
use Tobento\Service\Schedule\InteractsWithParameters;
use Psr\Container\ContainerInterface;
use DateTimeZone;

/**
 * AbstractTask
 */
abstract class AbstractTask implements TaskInterface
{
    use HasParameters;
    use InteractsWithParameters;

    /**
     * @var string
     */
    protected string $id = '';
    
    /**
     * @var string
     */
    protected string $name = '';
    
    /**
     * @var string
     */
    protected string $description = '';
    
    /**
     * @var null|TaskScheduleInterface
     */
    protected null|TaskScheduleInterface $schedule = null;
    
    /**
     * Process the task.
     *
     * @param ContainerInterface $container
     * @return TaskResultInterface
     * @throws \Throwable
     */
    abstract function processTask(ContainerInterface $container): TaskResultInterface;
    
    /**
     * Returns a unique id for the task.
     *
     * @return string
     */
    public function getId(): string
    {
        return !empty($this->id)
            ? $this->id
            : sha1($this->getName().$this->getDescription().$this->getSchedule()->getId());
    }
    
    /**
     * Set a unique id.
     *
     * @param string $id
     * @return static $this
     */
    public function id(string $id): static
    {
        $this->id = $id;
        
        return $this;
    }
    
    /**
     * Returns a task name.
     *
     * @return string
     */
    public function getName(): string
    {
        return !empty($this->name)
            ? $this->name
            : (new \ReflectionClass($this))->getShortName();
    }
    
    /**
     * Set the name.
     *
     * @param string $name
     * @return static $this
     */
    public function name(string $name): static
    {
        $this->name = $name;
        
        return $this;
    }
    
    /**
     * Return a description of the task.
     *
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }
    
    /**
     * Set the description.
     *
     * @param string $description
     * @return static $this
     */
    public function description(string $description): static
    {
        $this->description = $description;
        
        return $this;
    }
    
    /**
     * Returns the schedule.
     *
     * @return TaskScheduleInterface
     */
    public function getSchedule(): TaskScheduleInterface
    {
        if (is_null($this->schedule)) {
            $this->schedule = new CronExpression('* * * * *');
        }
        
        return $this->schedule;
    }
    
    /**
     * Set the schedule.
     *
     * @param string $expression
     * @return static $this
     */
    public function schedule(TaskScheduleInterface $schedule): static
    {
        $this->schedule = $schedule;
        
        return $this;
    }
    
    /**
     * Set the cron expression.
     *
     * @param string $expression
     * @param null|string|DateTimeZone $timezone
     * @return static $this
     */
    public function cron(string $expression, null|string|DateTimeZone $timezone = null): static
    {
        return $this->schedule(new CronExpression($expression, $timezone));
    }
}