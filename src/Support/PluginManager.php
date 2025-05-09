<?php

namespace Sanlilin\LaravelPlugin\Support;

use Illuminate\Support\Facades\File;
use Sanlilin\LaravelPlugin\Models\Plugin;
use Illuminate\Contracts\Foundation\Application;
use ZipArchive;

class PluginManager
{
	protected $app;
	protected $pluginsPath;
	protected $plugins = [];

	public function __construct(Application $app)
	{
		$this->app = $app;
		$this->pluginsPath = base_path('plugins');
	}

	public function all()
	{
		return $this->getPlugins();
	}

	public function getPlugins()
	{
		if (!empty($this->plugins)) {
			return $this->plugins;
		}

		if (!File::exists($this->pluginsPath)) {
			File::makeDirectory($this->pluginsPath);
			return [];
		}

		$plugins = [];
		foreach (File::directories($this->pluginsPath) as $pluginPath) {
			$pluginName = basename($pluginPath);
			$plugin = $this->find($pluginName);
			if ($plugin) {
				$plugins[$pluginName] = $plugin;
			}
		}

		$this->plugins = $plugins;

		return $plugins;
	}

	public function find($name)
	{
		$path = $this->getPluginPath($name);

		if (!File::exists($path)) {
			return null;
		}

		$configPath = $path . '/plugin.json';

		if (!File::exists($configPath)) {
			return null;
		}

		$config = json_decode(File::get($configPath), true);

		return new Plugin($this->app, $name, $path, $config);
	}

	public function getPluginPath($name)
	{
		return $this->pluginsPath . '/' . $name;
	}

	public function getPluginsPath()
	{
		return $this->pluginsPath;
	}
}