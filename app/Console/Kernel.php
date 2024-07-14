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
    ];

    protected function schedule(Schedule $schedule)
    {
        $schedule->command('fetch:inventory')->hourly();
        $schedule->command('fetch:inventory-item')->hourly();
    }

}
