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
        $config = apply_filters('wp_app_config', [
            'root_dir' => get_stylesheet_directory(),
            'root_url' => get_stylesheet_directory_uri(),
        ]);

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
