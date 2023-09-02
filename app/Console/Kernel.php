<?php

namespace App\Console;

use App\Console\Commands\AutoUpdateDistrictCommand;
use App\Console\Commands\MomoCommand;
use App\Console\Commands\MomoWalletBalanceCommand;
use Carbon\Carbon;
use Illuminate\Console\KeyGenerateCommand;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Artisan;
use Laravel\Lumen\Console\Kernel as ConsoleKernel;
use App\Console\Commands\DeleteExpiredSession;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        DeleteExpiredSession::class,
    ];

    /**
     * @param Schedule $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // command:auto-delete-expired-session
        $schedule->command('auto:Delete-Expired-Session')->daily();
    }
    
}
