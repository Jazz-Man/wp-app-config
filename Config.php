<?php

namespace JazzMan\APP_Config;

use JazzMan\ParameterBag\ParameterBag;
use JazzMan\Traits\SingletonTrait;

/**
 * Class Config.
 */
class Config
{
    use SingletonTrait;

    /**
     * @var \JazzMan\ParameterBag\ParameterBag
     */
    private $config;

    public function __construct()
    {
        $config = apply_filters('wp_app_config', []);

        $this->config = new ParameterBag($config);
    }

    /**
     * @return ParameterBag
     */
    public function getConfig()
    {
        return $this->config;
    }
}
