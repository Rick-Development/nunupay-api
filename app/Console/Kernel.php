<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Console\Application as Artisan;
use Illuminate\Console\Command as LaravelCommand;
use Symfony\Component\Console\Command\Command as SymfonyCommand;

class CustomArtisan extends Artisan
{
    public function find(string $name): SymfonyCommand
    {
        $command = parent::find($name);

        if ($command instanceof LaravelCommand) {
            $command->setLaravel($this->laravel);
        }

        return $command;
    }
}

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        $schedule->command('inspire')->hourly();
        $schedule->command('model:prune')->days(2);
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }

    /**
     * Get the Artisan application instance.
     */
    protected function getArtisan()
    {
        if (is_null($this->artisan)) {
            $this->artisan = (new CustomArtisan($this->app, $this->events, $this->app->version()))
                ->resolveCommands($this->commands)
                ->setContainerCommandLoader();

            if ($this->symfonyDispatcher) {
                $this->artisan->setDispatcher($this->symfonyDispatcher);
                $this->artisan->setSignalsToDispatchEvent();
            }
        }

        return $this->artisan;
    }
}