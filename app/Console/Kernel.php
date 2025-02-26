<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Foundation\Inspiring;
use Symfony\Component\Console\Output\ConsoleOutput;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     *
     * @noinspection PhpParamsInspection
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')->hourly()->withoutOverlapping(60);
        // $schedule->command('backup:clean')->daily()->at('01:00');
        // $schedule->command('backup:run')->daily()->at('02:00');
        // $schedule->command('backup:monitor')->daily()->at('03:00');
        $schedule->command('disposable:update')->weekly()->at('04:00');

        // $schedule->job(function (ConsoleOutput $consoleOutput) {
        //     $consoleOutput->writeln(Inspiring::quote());
        // })->everyMinute();
        //
        // $schedule->call(function (ConsoleOutput $consoleOutput) {
        //     $consoleOutput->writeln(Inspiring::quote());
        // })->everyMinute();

        // $schedule->exec('php', ['-v'])->everyMinute();
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
