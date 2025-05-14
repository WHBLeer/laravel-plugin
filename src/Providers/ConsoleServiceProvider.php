<?php

namespace Sanlilin\LaravelPlugin\Providers;

use Carbon\Laravel\ServiceProvider;
use Illuminate\Support\Str;
use Sanlilin\LaravelPlugin\Console\Commands\ComposerInstallCommand;
use Sanlilin\LaravelPlugin\Console\Commands\ComposerRemoveCommand;
use Sanlilin\LaravelPlugin\Console\Commands\ComposerRequireCommand;
use Sanlilin\LaravelPlugin\Console\Commands\MakeControllerCommand;
use Sanlilin\LaravelPlugin\Console\Commands\DisableCommand;
use Sanlilin\LaravelPlugin\Console\Commands\DownLoadCommand;
use Sanlilin\LaravelPlugin\Console\Commands\EnableCommand;
use Sanlilin\LaravelPlugin\Console\Commands\InstallCommand;
use Sanlilin\LaravelPlugin\Console\Commands\ListCommand;
use Sanlilin\LaravelPlugin\Console\Commands\LoginCommand;
use Sanlilin\LaravelPlugin\Console\Commands\MigrateCommand;
use Sanlilin\LaravelPlugin\Console\Commands\MakeMigrationCommand;
use Sanlilin\LaravelPlugin\Console\Commands\MakeModelCommand;
use Sanlilin\LaravelPlugin\Console\Commands\PluginCommand;
use Sanlilin\LaravelPlugin\Console\Commands\PluginDeleteCommand;
use Sanlilin\LaravelPlugin\Console\Commands\MakePluginCommand;
use Sanlilin\LaravelPlugin\Console\Commands\MakeProviderCommand;
use Sanlilin\LaravelPlugin\Console\Commands\PublishCommand;
use Sanlilin\LaravelPlugin\Console\Commands\RestartCommand;
use Sanlilin\LaravelPlugin\Console\Commands\RegisterCommand;
use Sanlilin\LaravelPlugin\Console\Commands\MakeRouteProviderCommand;
use Sanlilin\LaravelPlugin\Console\Commands\MakeSeedCommand;
use Sanlilin\LaravelPlugin\Console\Commands\UploadCommand;
use Sanlilin\LaravelPlugin\Console\Commands\InstallPluginsSystemCommand;

class ConsoleServiceProvider extends ServiceProvider
{
    /**
     * Namespace of the console commands.
     *
     * @var string
     */
    protected string $consoleNamespace = 'Sanlilin\\LaravelPlugin\\Console\\Commands';

    /**
     * The available commands.
     *
     * @var array
     */
    protected array $commands = [
	    InstallPluginsSystemCommand::class,
        PluginCommand::class,
        MakePluginCommand::class,
        MakeProviderCommand::class,
        MakeRouteProviderCommand::class,
        MakeControllerCommand::class,
        MakeModelCommand::class,
        MakeMigrationCommand::class,
        MigrateCommand::class,
        MakeSeedCommand::class,
        ComposerRequireCommand::class,
        ComposerRemoveCommand::class,
        ComposerInstallCommand::class,
        ListCommand::class,
        DisableCommand::class,
        EnableCommand::class,
        PluginDeleteCommand::class,
        InstallCommand::class,
        PublishCommand::class,
	    RestartCommand::class,
	    RegisterCommand::class,
        LoginCommand::class,
        UploadCommand::class,
        DownLoadCommand::class,

    ];

    /**
     * @return array
     */
    private function resolveCommands(): array
    {
        $commands = [];

        foreach ((config('plugins.commands') ?: $this->commands) as $command) {
            $commands[] = Str::contains($command, $this->consoleNamespace) ?
                $command :
                $this->consoleNamespace.'\\'.$command;
        }

        return $commands;
    }

    public function register(): void
    {
        $this->commands($this->resolveCommands());
    }

    /**
     * @return array
     */
    public function provides(): array
    {
        return $this->commands;
    }
}
