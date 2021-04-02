<?php

use JazzMan\AppConfig\Config;
use JazzMan\ParameterBag\ParameterBag;

if (!function_exists('app_config')) {
    function app_config(): ParameterBag
    {
        return Config::getInstance()->getConfig();
    }
}

if (!function_exists('app_dir_path')) {
    function app_dir_path(string $path = ''): string
    {
        $path = trim($path, DIRECTORY_SEPARATOR);

        return app_config()->get('root_dir').DIRECTORY_SEPARATOR.$path;
    }
}

if (!function_exists('app_url_patch')) {
    function app_url_path(string $path = ''): string
    {
        $path = trim($path, '/');

        return app_config()->get('root_url').'/'.$path;
    }
}
