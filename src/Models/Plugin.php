<?php

namespace Sanlilin\LaravelPlugin\Models;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Sanlilin\AdminPlugins\Exceptions\PluginException;

class Plugin
{
	public $app;
	public $name;
	public $path;
	public $config;

	public function __construct($app, $name, $path, $config)
	{
		$this->app = $app;
		$this->name = $name;
		$this->path = $path;
		$this->config = $config;
	}

	public function getName()
	{
		return $this->name;
	}

	public function getPath()
	{
		return $this->path;
	}

	public function getConfig($key='')
	{
		if ($key) {
			return $this->config[$key] ?? [];
		}
		return $this->config;
	}

	public function setConfig($config=[])
	{
		// 合并配置
		$newConfig = array_merge($this->config, $config);
		$configPath = $this->path . '/plugin.json';
		File::put($configPath, json_encode($newConfig, JSON_PRETTY_PRINT));
		// 更新当前对象的 config 属性
		$this->config = $newConfig;

		return $this->config;
	}

	public function getLowerName()
	{
		return strtolower($this->name);
	}

	public function getStudlyName()
	{
		return Str::studly($this->name);
	}

	public function getSlugName()
	{
		return Str::slug($this->name);
	}

	public function getTitle()
	{
		return $this->config['title'] ?? $this->name;
	}

	public function getVersion()
	{
		return $this->config['version'] ?? '1.0.0';
	}

	public function getDescription()
	{
		return $this->config['description'] ?? '';
	}

	public function getLogo()
	{
		return $this->config['logo'] ?? plugin_logo($this->name);
	}

	public function getAuthor()
	{
		return $this->config['author'] ?? 'SanLiLin';
	}

	public function getEmail()
	{
		return $this->config['email'] ?? 'wanghongbin816@gmail.com';
	}

	public function isEnabled()
	{
		return $this->config['enabled'] ?? false;
	}

	public function enable()
	{
		$this->config['enabled'] = true;
		$this->saveConfig();
	}

	public function disable()
	{
		$this->config['enabled'] = false;
		$this->saveConfig();
	}

	public function saveConfig()
	{
		$configPath = $this->path . '/plugin.json';
		File::put($configPath, json_encode($this->config, JSON_PRETTY_PRINT));
	}

	public function getProviders()
	{
		$providers = [];

		if (isset($this->config['provider'])) {
			$providers[] = $this->config['provider'];
		}

		if (isset($this->config['providers'])) {
			$providers = array_merge($providers, $this->config['providers']);
		}

		return $providers;
	}

	public function getNamespace()
	{
		return $this->config['namespace'] ?? 'Plugins\\' . $this->getStudlyName();
	}

	public function getAssetPath($file = null)
	{
		$path = public_path('vendor/plugins/' . $this->getLowerName());

		return $file ? $path . '/' . $file : $path;
	}

	public function hasViews()
	{
		return File::exists($this->path . '/Resources/views');
	}

	public function hasMigrations()
	{
		return File::exists($this->path . '/Database/Migrations');
	}

	public function hasTranslations()
	{
		return File::exists($this->path . '/Resources/lang');
	}

	public function hasRoutes()
	{
		return File::exists($this->path . '/Routes/web.php');
	}

	public function toArray()
	{
		return [
			'name' => $this->getName(),
			'title' => $this->getTitle(),
			'version' => $this->getVersion(),
			'description' => $this->getDescription(),
			'enabled' => $this->isEnabled(),
			'path' => $this->getPath(),
		];
	}
}