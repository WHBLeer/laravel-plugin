<?php

namespace Sanlilin\LaravelPlugin\Console\Commands;

use App\Models\Permission;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;
use Sanlilin\LaravelPlugin\Support\Plugin;

class DisableCommand extends Command
{
	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'plugin:disable';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Disable the specified plugin.';

	protected static $source = 'plugin';
	/**
	 * Execute the console command.
	 */
	public function handle(): int
	{
		/**
		 * check if user entred an argument.
		 */
		if ($this->argument('plugin') === null) {
			$this->disableAll();
			return 0;
		}

		/** @var Plugin $plugin */
		$plugin = $this->laravel['plugins.repository']->findOrFail($this->argument('plugin'));
		self::$source = $plugin->config()['permission']['source_by'];
		if ($plugin->isEnabled()) {
			$plugin->disable();

			$this->info("Plugin [{$plugin}] disabled successful.");
			$this->removePermission($plugin);
		} else {
			$this->comment("Plugin [{$plugin}] has already disabled.");
		}

		return 0;
	}

	/**
	 * disableAll.
	 *
	 * @return void
	 */
	public function disableAll(): void
	{
		$plugins = $this->laravel['plugins.repository']->all();
		/** @var Plugin $plugin */
		foreach ($plugins as $plugin) {
			self::$source = $plugin->config()['permission']['source_by'];
			if ($plugin->isEnabled()) {
				$plugin->disable();

				$this->info("Plugin [{$plugin}] disabled successful.");
				$this->removePermission($plugin);
			} else {
				$this->comment("Plugin [{$plugin}] has already disabled.");
			}
		}
	}

	/**
	 * remove Permission
	 * @param $plugin
	 *
	 * @author: hongbinwang
	 * @time  : 2023/11/2 14:32
	 */
	public function removePermission($plugin)
	{
		Permission::where('source',self::$source)->delete();
		// Repair tree structure
		Permission::fixTree();
	}

	/**
	 * Get the console command arguments.
	 *
	 * @return array
	 */
	protected function getArguments(): array
	{
		return [
			['plugin', InputArgument::OPTIONAL, 'Plugin name.'],
		];
	}
}
