<?php

if (! function_exists('plugin_path')) {
    function plugin_path(string $name, string $path = ''): string
    {
        $plugin = app('plugins.repository')->find($name);

        return $plugin->getPath().($path ? DIRECTORY_SEPARATOR.$path : $path);
    }
}

if (! function_exists('plugin_native')) {
	function plugin_native(string $name): bool
	{
		$plugin = app('plugins.repository')->find($name);

		return $plugin ? true : false;
	}
}

if (! function_exists('plugin_enabled')) {
	function plugin_enabled(string $name): string
	{
		$plugin = app('plugins.repository')->find($name);

		return $plugin && $plugin->isEnabled();
	}
}