<?php

namespace App\Console;

use App\Jobs\ExpiredOtpDelete;
use App\Jobs\UpdateExpiredVip;
use Carbon\Carbon;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // $schedule->command('inspire')->hourly();

        //membuat daily schedule untuk update abilites token, pada pukul 12 malam
        $schedule->job(new UpdateExpiredVip())->everyTwoHours();
        $schedule->job(new ExpiredOtpDelete())->twiceDaily(1 , 13);
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
