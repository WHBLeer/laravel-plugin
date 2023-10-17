<?php

namespace Sanlilin\LaravelPlugin\Providers;

use Carbon\Laravel\ServiceProvider;
use Illuminate\Support\Str;
use Sanlilin\LaravelPlugin\Console\Commands\ComposerInstallCommand;
use Sanlilin\LaravelPlugin\Console\Commands\ComposerRemoveCommand;
use Sanlilin\LaravelPlugin\Console\Commands\ComposerRequireCommand;
use Sanlilin\LaravelPlugin\Console\Commands\ControllerMakeCommand;
use Sanlilin\LaravelPlugin\Console\Commands\DisableCommand;
use Sanlilin\LaravelPlugin\Console\Commands\DownLoadCommand;
use Sanlilin\LaravelPlugin\Console\Commands\EnableCommand;
use Sanlilin\LaravelPlugin\Console\Commands\InstallCommand;
use Sanlilin\LaravelPlugin\Console\Commands\ListCommand;
use Sanlilin\LaravelPlugin\Console\Commands\LoginCommand;
use Sanlilin\LaravelPlugin\Console\Commands\MigrateCommand;
use Sanlilin\LaravelPlugin\Console\Commands\MigrationMakeCommand;
use Sanlilin\LaravelPlugin\Console\Commands\ModelMakeCommand;
use Sanlilin\LaravelPlugin\Console\Commands\PluginCommand;
use Sanlilin\LaravelPlugin\Console\Commands\PluginDeleteCommand;
use Sanlilin\LaravelPlugin\Console\Commands\PluginMakeCommand;
use Sanlilin\LaravelPlugin\Console\Commands\ProviderMakeCommand;
use Sanlilin\LaravelPlugin\Console\Commands\PublishCommand;
use Sanlilin\LaravelPlugin\Console\Commands\RegisterCommand;
use Sanlilin\LaravelPlugin\Console\Commands\RouteProviderMakeCommand;
use Sanlilin\LaravelPlugin\Console\Commands\SeedMakeCommand;
use Sanlilin\LaravelPlugin\Console\Commands\UploadCommand;

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
        PluginCommand::class,
        PluginMakeCommand::class,
        ProviderMakeCommand::class,
        RouteProviderMakeCommand::class,
        ControllerMakeCommand::class,
        ModelMakeCommand::class,
        MigrationMakeCommand::class,
        MigrateCommand::class,
        SeedMakeCommand::class,
        ComposerRequireCommand::class,
        ComposerRemoveCommand::class,
        ComposerInstallCommand::class,
        ListCommand::class,
        DisableCommand::class,
        EnableCommand::class,
        PluginDeleteCommand::class,
        InstallCommand::class,
        PublishCommand::class,
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
