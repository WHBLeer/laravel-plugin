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

	'menusshow' => false,
	'stubs' => [
		'enabled' => false,
		'files'   => [
			'routes/web'      => 'Routes/web.php',
			'routes/api'      => 'Routes/api.php',
			'routes/admin'    => 'Routes/admin.php',
			'views/index'     => 'Resources/views/index.blade.php',
			'views/create'    => 'Resources/views/create.blade.php',
			'views/edit'      => 'Resources/views/edit.blade.php',
			'views/show'      => 'Resources/views/show.blade.php',
			'scaffold/config' => 'Config/config.php',
			'scaffold/helper' => 'Support/helper.php',
			'assets/js/app'   => 'Resources/assets/js/app.js',
			'assets/css/app'  => 'Resources/assets/css/app.css',
			'assets/logo'     => 'Resources/assets/logo.png',
			'lang/en'         => 'Resources/lang/en.json',
			'lang/zh'         => 'Resources/lang/zh.json',
			'readme'          => 'readme.md',
			'permission'      => 'permission.json',
			'gitignore'       => '.gitignore',
		],
		'replacements' => [
			'routes/web'      => ['LOWER_NAME', 'STUDLY_NAME','CLASS_NAMESPACE'],
			'routes/api'      => ['LOWER_NAME', 'STUDLY_NAME','CLASS_NAMESPACE'],
			'json'            => ['LOWER_NAME', 'STUDLY_NAME', 'PLUGIN_NAMESPACE', 'PROVIDER_NAMESPACE'],
			'menu'            => ['LOWER_NAME', 'STUDLY_NAME', 'UPPER_NAME'],
			'readme'          => ['LOWER_NAME', 'STUDLY_NAME', 'PLUGIN_NAMESPACE', 'PROVIDER_NAMESPACE'],
			'assets/lang'     => ['LOWER_NAME', 'STUDLY_NAME', 'PLUGIN_NAMESPACE', 'PROVIDER_NAMESPACE'],
			'views/index'     => ['LOWER_NAME'],
			'views/create'     => ['LOWER_NAME'],
			'views/edit'     => ['LOWER_NAME'],
			'views/show'    => ['LOWER_NAME', 'STUDLY_NAME'],
			'scaffold/config'  => ['LOWER_NAME', 'STUDLY_NAME'],
			'scaffold/helper' => ['STUDLY_NAME'],
		],
		'gitkeep' => true,
	],
	'paths' => [

		'plugins' => base_path('plugins'),

		// 资源发布目录
		'assets' => public_path('assets/vendor/plugins'),

		// 默认应用创建目录结构
		'generator' => [
			'config'     => ['path' => 'Config', 'generate' => true],
			'seeder'     => ['path' => 'Database/Seeders', 'generate' => true],
			'migration'  => ['path' => 'Database/Migrations', 'generate' => true],
			'events'     => ['path' => 'Events', 'generate' => true],
			'controller' => ['path' => 'Http/Controllers', 'generate' => true],
			'model'      => ['path' => 'Models', 'generate' => true],
			'provider'   => ['path' => 'Providers', 'generate' => true],
			'services'   => ['path' => 'Services', 'generate' => true],
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

	'cache' => [
		'enabled'  => false,
		'key'      => 'plugins',
		'lifetime' => 3600,
	],
	'register' => [
		'translations' => true,
		'files' => 'register',
	],

	'activators' => [
		'file' => [
			'class'          => \Sanlilin\LaravelPlugin\Activators\FileActivator::class,
			'statuses-file'  => storage_path('plugins.json'),
			'cache-key'      => 'activator.installed',
			'cache-lifetime' => 604800,
		],
	],

	'activator' => 'file',

];
