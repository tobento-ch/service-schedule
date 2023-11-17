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

namespace Tobento\Service\Schedule;

use Psr\Container\ContainerInterface;

/**
 * TaskInterface
 */
interface TaskInterface
{
    /**
     * Returns a unique id for the task.
     *
     * @return string
     */
    public function getId(): string;
    
    /**
     * Returns a task name.
     *
     * @return string
     */
    public function getName(): string;
    
    /**
     * Return a description of the task.
     *
     * @return string
     */
    public function getDescription(): string;    

    /**
     * Process the task.
     *
     * @param ContainerInterface $container
     * @return TaskResultInterface
     * @throws \Throwable
     */
    public function processTask(ContainerInterface $container): TaskResultInterface;
    
    /**
     * Returns the schedule.
     *
     * @return TaskScheduleInterface
     */
    public function getSchedule(): TaskScheduleInterface;
    
    /**
     * Returns the parameters.
     *
     * @return ParametersInterface
     */
    public function parameters(): ParametersInterface;
    
    /**
     * Add a parameter.
     *
     * @param ParameterInterface $parameter
     * @return static $this
     */
    public function parameter(ParameterInterface $parameter): static;
}