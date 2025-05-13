<?php

namespace Sanlilin\LaravelPlugin\Console\Commands;

use App\Models\Role;
use App\Models\Permission;
use Illuminate\Console\Command;

class InstallPluginsSystemCommand extends Command
{
	protected $signature = 'plugins-system:install';
	protected $description = 'Install the plugins system';

	public function handle()
	{
		// 发布资源
		$this->call('vendor:publish', [
			'--provider' => 'Sanlilin\LaravelPlugin\Providers\PluginServiceProvider',
			'--tag' => 'plugins-config'
		]);
		$this->info('All permissions assigned to admin role.');
		$this->call('vendor:publish', [
			'--provider' => 'Sanlilin\LaravelPlugin\Providers\PluginServiceProvider',
			'--tag' => 'plugins-views'
		]);
		$this->info('All permissions assigned to admin role.');
		$this->call('vendor:publish', [
			'--provider' => 'Sanlilin\LaravelPlugin\Providers\PluginServiceProvider',
			'--tag' => 'plugins-assets'
		]);
		$this->info('All permissions assigned to admin role.');
		$this->call('vendor:publish', [
			'--provider' => 'Sanlilin\LaravelPlugin\Providers\PluginServiceProvider',
			'--tag' => 'plugins-migrations'
		]);

		$this->info('Plugins system installed successfully!');
	}
}