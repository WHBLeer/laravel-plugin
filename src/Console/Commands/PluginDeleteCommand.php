<?php

namespace Sanlilin\LaravelPlugin\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;
use Sanlilin\LaravelPlugin\Support\Composer\ComposerRemove;
use Sanlilin\LaravelPlugin\Traits\PluginCommandTrait;

class PluginDeleteCommand extends Command
{
    use PluginCommandTrait;

    protected $name = 'plugin:delete';

    protected $description = 'Delete a plugin from the application';

    public function handle(): int
    {
        try {
            ComposerRemove::make()->appendRemovePluginRequires(
                $this->getPluginName(),
                $this->getPlugin()->getAllComposerRequires()
            )->run();

	        // 删除插件创建的资源软链
	        $linkPath = public_path('assets/plugin/'.$this->getPlugin()->getLowerName());
	        if (file_exists($linkPath) || is_link($linkPath)) {
		        $this->laravel->make('files')->delete($linkPath);
	        }
	        // 删除插件注册
	        $this->laravel['plugins.repository']->delete($this->argument('plugin'));

	        $this->info("Plugin {$this->argument('plugin')} has been deleted.");

            return 0;
        } catch (Exception $exception) {
            $this->error($exception->getMessage());

            return E_ERROR;
        }
    }

    protected function getArguments(): array
    {
        return [
            ['plugin', InputArgument::REQUIRED, 'The name of plugin to delete.'],
        ];
    }
}
