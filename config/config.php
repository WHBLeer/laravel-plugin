<?php

return [

	'namespace' => 'Plugins',

	// 应用市场
	'market' => [
		// 应用市场 api 域名
		'api_base' => 'https://developer.uhaveshop.com/plugin',
		// 应用市场默认调用的 client class
		'default' => \Sanlilin\LaravelPlugin\Support\Client\Market::class,
	],

	'stubs' => [
		'enabled' => false,
		'files'   => [
			'routes/web'      => 'Routes/web.php',
			'routes/api'      => 'Routes/api.php',
			'views/index'     => 'Resources/views/index.blade.php',
			'views/master'    => 'Resources/views/layouts/master.blade.php',
			'scaffold/config' => 'Config/config.php',
			'scaffold/helper' => 'Support/helper.php',
			'assets/js/app'   => 'Resources/assets/js/app.js',
			'assets/sass/app' => 'Resources/assets/sass/app.scss',
			'assets/logo'     => 'Resources/assets/logo.png',
			'readme'          => 'readme.md',
			'webpack'         => 'webpack.mix.js',
			'package'         => 'package.json',
			'gitignore'       => '.gitignore',
		],
		'replacements' => [
			'routes/web'      => ['LOWER_NAME', 'STUDLY_NAME'],
			'routes/api'      => ['LOWER_NAME'],
			'json'            => ['LOWER_NAME', 'STUDLY_NAME', 'PLUGIN_NAMESPACE', 'PROVIDER_NAMESPACE'],
			'readme'          => ['LOWER_NAME', 'STUDLY_NAME'],
			'views/index'     => ['LOWER_NAME'],
			'views/master'    => ['LOWER_NAME', 'STUDLY_NAME'],
			'scaffold/config' => ['STUDLY_NAME'],
			'scaffold/helper' => ['STUDLY_NAME'],
			'webpack'         => ['LOWER_NAME'],
		],
		'gitkeep' => true,
	],
	'paths' => [

		'plugins' => base_path('plugins'),

		// 资源发布目录
		'assets' => public_path('plugins'),

		// 默认应用创建目录结构
		'generator' => [
			'config'     => ['path' => 'Config', 'generate' => true],
			'seeder'     => ['path' => 'Database/Seeders', 'generate' => true],
			'migration'  => ['path' => 'Database/Migrations', 'generate' => true],
			'events'     => ['path' => 'Events', 'generate' => true],
			'controller' => ['path' => 'Http/Controllers', 'generate' => true],
			'model'      => ['path' => 'Models', 'generate' => true],
			'provider'   => ['path' => 'Providers', 'generate' => true],
			'assets'     => ['path' => 'Resources/assets', 'generate' => true],
			'lang'       => ['path' => 'Resources/lang', 'generate' => true],
			'views'      => ['path' => 'Resources/views', 'generate' => true],
			'routes'     => ['path' => 'Routes', 'generate' => true],
			'support'    => ['path' => 'Support', 'generate' => true],
			'traits'     => ['path' => 'Traits', 'generate' => true],
		],
	],
	// 事件监听
	'listen' => [
		// 应用安装以后
		'plugins.installed' => [
			\Sanlilin\LaravelPlugin\Listeners\PluginPublish::class,
			\Sanlilin\LaravelPlugin\Listeners\PluginMigrate::class,
		],
		// 应用禁用之前
		'plugins.disabling' => [],

		// 应用禁用之后
		'plugins.disabled' => [],

		// 应用启用之前
		'plugins.enabling' => [],

		// 应用启用之后
		'plugins.enabled' => [],

		// 应用删除之前
		'plugins.deleting' => [],

		// 应用删除之后
		'plugins.deleted' => [],
	],

	// 自定义命令
	'commands' => [],

	'cache' => [
		'enabled'  => false,
		'key'      => 'laravel-plugin',
		'lifetime' => 60,
	],
	'register' => [
		'translations' => true,
		'files' => 'register',
	],

	'activators' => [
		'file' => [
			'class'          => \Sanlilin\LaravelPlugin\Activators\FileActivator::class,
			'statuses-file'  => base_path('plugin_statuses.json'),
			'cache-key'      => 'activator.installed',
			'cache-lifetime' => 604800,
		],
	],

	'activator' => 'file',

];
