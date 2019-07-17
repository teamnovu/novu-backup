<?php

namespace App\Commands;

use Illuminate\Console\Scheduling\Schedule;
use LaravelZero\Framework\Commands\Command;

class Scheduler extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'scheduler';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'This command is used to setup 3rd party scheduled commands';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->error('this command does nothing! Its just a container to setup scheuler for 3rd Party commands!');
        $this->info('please consult https://laravel-zero.com/docs/task-scheduling/ to setup scheduler correctly');
    }

    /**
     * Define the command's schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule $schedule
     * @return void
     */
    public function schedule(Schedule $schedule)
    {
        if(config('app.backup-databases')) {
            $schedule->command('backup:run')->everyMinute();
        } else {
            $schedule->command('backup:run --only-files')->everyMinute();
        }
    }
}
