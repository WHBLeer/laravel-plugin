<?php

namespace Sanlilin\LaravelPlugin\Providers;

use Illuminate\Support\ServiceProvider;
use Sanlilin\LaravelPlugin\Contracts\ActivatorInterface;
use Sanlilin\LaravelPlugin\Contracts\ClientInterface;
use Sanlilin\LaravelPlugin\Contracts\RepositoryInterface;
use Sanlilin\LaravelPlugin\Exceptions\InvalidActivatorClass;
use Sanlilin\LaravelPlugin\Support\Repositories\FileRepository;
use Sanlilin\LaravelPlugin\Support\Stub;

class PluginServiceProvider extends ServiceProvider
{
	/**
	 * Booting the package.
	 */
	public function boot()
	{
		$this->registerPlugins();
		$this->registerPublishing();
	}

	/**
	 * Register the service provider.
	 */
	public function register()
	{
		$this->mergeConfigFrom(__DIR__.'/../../config/config.php', 'plugins');
		$this->setPsr4();
		$this->registerServices();
		$this->setupStubPath();
		$this->registerProviders();
		$this->app->register(RouteServiceProvider::class);

	}

	/**
	 * Register all plugins.
	 */
	protected function registerPlugins(): void
	{
		$this->app->register(BootstrapServiceProvider::class);
	}

	protected function setPsr4(): void
	{
		if (file_exists(base_path('/vendor/autoload.php'))) {
			$loader = require base_path('/vendor/autoload.php');
			$namespace = $this->app['config']->get('plugins.namespace');
			$path = $this->app['config']->get('plugins.paths.plugins');
			$loader->setPsr4("{$namespace}\\", ["{$path}/"]);
		}
	}

	/**
	 * Setup stub path.
	 */
	public function setupStubPath(): void
	{
		$path = $this->app['config']->get('plugin.stubs.path') ?? __DIR__.'/../../stubs';
		Stub::setBasePath($path);

		$this->app->booted(function ($app) {
			/** @var RepositoryInterface $pluginRepository */
			$pluginRepository = $app[RepositoryInterface::class];
			if ($pluginRepository->config('stubs.enabled') === true) {
				Stub::setBasePath($pluginRepository->config('stubs.path'));
			}
		});
	}

	protected function registerServices(): void
	{
		$this->app->singleton(RepositoryInterface::class, function ($app) {
			$path = $app['config']->get('plugins.paths.plugins');

			return new FileRepository($app, $path);
		});
		$this->app->singleton(ActivatorInterface::class, function ($app) {
			$activator = $app['config']->get('plugins.activator');
			$class = $app['config']->get('plugins.activators.'.$activator)['class'];

			if ($class === null) {
				throw InvalidActivatorClass::missingConfig();
			}

			return new $class($app);
		});
		$this->app->singleton(ClientInterface::class, function ($app) {
			$class = $app['config']->get('plugins.market.default');
			if ($class === null) {
				throw InvalidActivatorClass::missingConfig();
			}

			return new $class();
		});
		$this->app->alias(RepositoryInterface::class, 'plugins.repository');
		$this->app->alias(ActivatorInterface::class, 'plugins.activator');
		$this->app->alias(ClientInterface::class, 'plugins.client');
	}

	/**
	 * Register providers.
	 */
	protected function registerProviders(): void
	{
		$this->app->register(ConsoleServiceProvider::class);
		$this->app->register(ContractsServiceProvider::class);
		$this->app->register(EventServiceProvider::class);
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return [RepositoryInterface::class, 'plugins.repository'];
	}

	private function registerPublishing(): void
	{
		$sourceConfigPath = __DIR__.'/../../config/config.php';
		$sourceMigrationsPath = __DIR__.'/../../database/migrations';
		$sourceViewsPath = __DIR__.'/../../resources/views';
		$sourceAssetsPath = __DIR__.'/../../resources/assets';
		$sourceLangPath = __DIR__.'/../../resources/lang';
		$configPath = config_path('plugins.php');
		$migrationPath = base_path('database/migrations');
		$viewPath = resource_path('views/vendor/plugins');
		$assetPath = public_path('assets/vendor/plugins');
		$this->loadViewsFrom($sourceViewsPath,'plugins');
		$this->loadJsonTranslationsFrom($sourceLangPath);
		$this->loadMigrationsFrom($sourceMigrationsPath);

		// 资源文件发布到应用
		if ($this->app->runningInConsole()) {
			$this->publishes([$sourceConfigPath => $configPath], 'plugins-config');
			$this->publishes([$sourceMigrationsPath => $migrationPath], 'plugins-migrations');
			$this->publishes([$sourceViewsPath => $viewPath,], 'plugins-views');
			$this->publishes([$sourceAssetsPath => $assetPath,], 'plugins-assets');

			// 注册发布后的回调
			$this->afterPublishing(function() use ($viewPath, $assetPath) {
				$this->setDirectoryPermissions($viewPath);
				$this->setDirectoryPermissions($assetPath);
			});
		}
	}

	protected function setDirectoryPermissions(string $path, string $user = 'www', int $mode = 0755): void
	{
		try {
			if (!file_exists($path)) {
				return;
			}

			// 设置权限
			chmod($path, $mode);

			// 递归设置子目录权限
			$iterator = new \RecursiveIteratorIterator(
				new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS),
				\RecursiveIteratorIterator::SELF_FIRST
			);

			foreach ($iterator as $item) {
				chmod($item->getPathname(), $mode);
			}

			// 设置所有者(Linux/macOS系统)
			if (function_exists('chown') && posix_getuid() === 0) {
				chown($path, $user);
				foreach ($iterator as $item) {
					chown($item->getPathname(), $user);
				}
			}

		} catch (\Exception $e) {
			throw InvalidActivatorClass::errorPermission($path,$e->getMessage());
		}
	}

	/**
	 * 注册发布后回调
	 * @param callable $callback
	 */
	protected function afterPublishing(callable $callback): void
	{
		$this->app->booted(function() use ($callback) {
			if ($this->app->runningInConsole()) {
				$callback();
			}
		});
	}
}
