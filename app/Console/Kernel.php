<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected $commands = [
        Commands\FetchInventoryData::class,
        Commands\FetchInventoryItemData::class,
        Commands\FetchProducts::class,
    ];

    protected function schedule(Schedule $schedule)
    {
        $schedule->command('fetch:inventory')->everyTenMinutes();
        $schedule->command('fetch:inventory-item')->everyTenMinutes();
        $schedule->command('fetch:products')->dailyAt('00:00');
    }
}
