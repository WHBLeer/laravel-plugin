<?php

namespace Sanlilin\LaravelPlugin\Exceptions;

class InvalidActivatorClass extends \Exception
{
    public static function missingConfig(): InvalidActivatorClass
    {
        return new static("You don't have a valid activator configuration class. This might be due to your config being out of date. \n Run: \n php artisan vendor:publish --provider=\"Sanlilin\LaravelPlugin\Providers\PluginServiceProvider\" --force \n to publish the up to date configuration");
    }
}
