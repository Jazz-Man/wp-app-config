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
