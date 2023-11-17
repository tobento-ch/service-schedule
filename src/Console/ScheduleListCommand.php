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
use Tobento\Service\Schedule\ScheduleInterface;
use Psr\Clock\ClockInterface;

class ScheduleListCommand extends AbstractCommand
{
    /**
     * The signature of the console command.
     */
    public const SIGNATURE = '
        schedule:list | List the scheduled tasks
    ';
    
    /**
     * Handle the command.
     *
     * @param InteractorInterface $io
     * @param ScheduleInterface $schedule
     * @param ClockInterface $clock
     * @return int The exit status code: 
     *     0 SUCCESS
     *     1 FAILURE If some error happened during the execution
     *     2 INVALID To indicate incorrect command usage e.g. invalid options
     */
    public function handle(
        InteractorInterface $io,
        ScheduleInterface $schedule,
        ClockInterface $clock,
    ): int {
        if ($schedule->count() === 0) {
            $io->info('No scheduled tasks registered.');
            return 0;
        }
        
        $io->info(sprintf('Schedule Name: %s', $schedule->getName()));
        
        $rows = [];
        
        foreach($schedule->all() as $task) {
            $rows[] = [
                $task->getId(),
                $task->getName(),
                $task->getDescription(),
                $task->getSchedule()->getNextRunDate($clock->now())->format('Y-m-d H:i:s P'),
            ];
        }
        
        $io->table(
            headers: ['ID', 'Name', 'Description', 'Next Due'],
            rows: $rows,
        );
        
        return 0;
    }
}