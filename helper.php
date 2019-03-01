<?php

if (!function_exists('app_config')) {
    /**
     * @return \JazzMan\ParameterBag\ParameterBag
     */
    function app_config()
    {
        return JazzMan\APP_Config\Config::getInstance()->getConfig();
    }
}

if (!function_exists('app_dir_path')) {
    /**
     * @param string $path
     *
     * @return string
     */
    function app_dir_path($path = '')
    {
        $path = trim($path, DIRECTORY_SEPARATOR);

        return app_config()->get('root_dir').DIRECTORY_SEPARATOR.$path;
    }
}

if (!function_exists('app_url_patch')) {
    /**
     * @param string $path
     *
     * @return string
     */
    function app_url_path($path = '')
    {
        $path = trim($path, '/');

        return app_config()->get('root_url').'/'.$path;
    }
}
