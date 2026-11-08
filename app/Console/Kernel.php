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
        Commands\cron_user_inactive::class,
         \App\Console\Commands\HitApiAndSaveToDatabase::class,
         \App\Console\Commands\CheckBloodDonorAvailability::class,
    ];
    protected function schedule(Schedule $schedule)
    {
         $schedule->command('inactive:user')->daily();
         $schedule->command('donors:check-availability')->daily();

    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
