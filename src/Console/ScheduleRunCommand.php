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

namespace Tobento\Service\Schedule\Console;

use Tobento\Service\Console\AbstractCommand;
use Tobento\Service\Console\InteractorInterface;
use Tobento\Service\Schedule\TaskProcessorInterface;
use Tobento\Service\Schedule\ScheduleProcessorInterface;
use Tobento\Service\Schedule\ScheduleProcessor;
use Tobento\Service\Schedule\ScheduleInterface;
use Tobento\Service\Schedule\TaskResultsInterface;
use Tobento\Service\Schedule\TaskResultInterface;
use Tobento\Service\Schedule\Parameter;
use Psr\Clock\ClockInterface;

class ScheduleRunCommand extends AbstractCommand
{
    /**
     * The signature of the console command.
     */
    public const SIGNATURE = '
        schedule:run | Runs scheduled tasks that are due
        {--id[] : The task ids to run instead}
    ';
    
    /**
     * Handle the command.
     *
     * @param InteractorInterface $io
     * @param TaskProcessorInterface $taskProcessor
     * @param ScheduleProcessorInterface $scheduleProcessor
     * @param ScheduleInterface $schedule
     * @param ClockInterface $clock
     * @return int The exit status code: 
     *     0 SUCCESS
     *     1 FAILURE If some error happened during the execution
     *     2 INVALID To indicate incorrect command usage e.g. invalid options
     */
    public function handle(
        InteractorInterface $io,
        TaskProcessorInterface $taskProcessor,
        ScheduleProcessorInterface $scheduleProcessor,
        ScheduleInterface $schedule,
        ClockInterface $clock,
    ): int {
        // Handle task ids:
        if (!empty($io->option(name: 'id'))) {
            
            $failed = false;
            
            foreach($io->option(name: 'id') as $taskId) {
                if (!is_null($task = $schedule->getTask($taskId))) {
                    $result = $taskProcessor->processTask($task);
                    
                    if ($result->isFailure()) {
                        $failed = true;
                    }
                    
                    $this->handleTaskResult($result, $io);
                } else {
                    $failed = true;
                    
                    $io->error(sprintf('Task with the id %s not found', $taskId));
                }
            }
            
            return $failed ? 1 : 0;
        }
        
        // Handle schedule:
        $io->info(sprintf('Schedule %s starting', $schedule->getName()));
        
        $results = $scheduleProcessor->processSchedule(
            schedule: $schedule,
            now: $clock->now(),
        );
        
        $this->handleTaskResults($results, $io);
        
        $io->info(sprintf('Schedule %s finished', $schedule->getName()));
        
        return $results->failed()->count() > 0 ? 1 : 0;
    }

    /**
     * Handle the task results.
     *
     * @param TaskResultsInterface $results
     * @param InteractorInterface $io
     * @return void
     */    
    protected function handleTaskResults(TaskResultsInterface $results, InteractorInterface $io): void
    {
        if ($results->count() === 0) {
            $io->info('No scheduled tasks are ready to run.');
            return;
        }
        
        $io->table(
            headers: ['Successful', 'Failed', 'Skipped'],
            rows: [[
                $results->successful()->count(),
                $results->failed()->count(),
                $results->skipped()->count()
            ]],
        );        
        
        foreach($results as $result) {
            $this->handleTaskResult($result, $io);
        }
    }
    
    /**
     * Handle the task result.
     *
     * @param TaskResultInterface $result
     * @param InteractorInterface $io
     * @return void
     */    
    protected function handleTaskResult(TaskResultInterface $result, InteractorInterface $io): void
    {
        $task = $result->task();
        $monitor = $task->parameters()->name(Parameter\Monitor::class)->first();
        $info = '';

        if ($monitor) {                
            $info = sprintf(
                ', started at: %s, runtime in seconds: %s, memory usage in bytes: %s',
                $monitor->startedAt(),
                $monitor->runtimeInSeconds(),
                $monitor->memoryUsage()
            );
        }
        
        if ($result->isSuccessful()) {
            $io->success(sprintf(
                'Success: task %s with the id %s',
                $task->getName(),
                $task->getId()
            ).$info);
        } elseif ($result->isSkipped()) {
            $io->info(sprintf(
                'Skipped: task %s with the id %s. Exception: %s',
                $task->getName(),
                $task->getId(),
                (string)$result->exception()?->getMessage(),
            ).$info);
        } else {
            $io->error(sprintf(
                'Failed: task %s with the id %s. Exception: %s',
                $task->getName(),
                $task->getId(),
                (string)$result->exception()?->getMessage(),
            ).$info);
        }
        
        if ($io->isVerbose('v')) {
            $io->write('Task Output:');
            $io->newLine();
            $io->write($result->output());
        }
        
        $io->newLine(num: 2);
    }
}