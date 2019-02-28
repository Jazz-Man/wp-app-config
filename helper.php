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

if (!function_exists('app_dir_patch')) {
    /**
     * @param string $patch
     *
     * @return string
     */
    function app_dir_patch($patch = '')
    {
        $patch = trim($patch, DIRECTORY_SEPARATOR);

        return app_config()->get('root_dir').DIRECTORY_SEPARATOR.$patch;
    }
}

if (!function_exists('app_url_patch')) {
    /**
     * @param string $patch
     *
     * @return string
     */
    function app_url_patch($patch = '')
    {
        $patch = trim($patch, DIRECTORY_SEPARATOR);

        return app_config()->get('root_url').DIRECTORY_SEPARATOR.$patch;
    }
}

