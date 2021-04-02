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

if (!\function_exists('app_use_webp')) {
    function app_use_webp(): bool
    {
        if (!empty($_SERVER['HTTP_ACCEPT']) && false !== \strpos($_SERVER['HTTP_ACCEPT'], 'image/webp')) {
            return true;
        }

        $UA = !empty($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : false;

        if ($UA) {
            if (false !== \strpos($UA, ' Chrome/')) {
                return true;
            }

            if (false !== \strpos($UA, 'Safari') && ($result = \stristr($UA, 'Version'))) {
                \preg_match('/(?:version\/(?<version>[\d.]+))?/i', $result, $matches);
                if (!empty($matches['version']) && \version_compare($matches['version'], '13', '>=')) {
                    return true;
                }
            }

            if (false !== \strpos($UA, 'Firefox')) {
                $result = \explode('/', \stristr($UA, 'Firefox'));
                if (!empty($result[1]) && \version_compare($result[1], '65', '>=')) {
                    return true;
                }
            }
        }

        return false;
    }
}

if (!\function_exists('app_get_request_data')) {
    function app_get_request_data(): ParameterBag
    {
        if (!empty($_SERVER['REQUEST_METHOD'])) {
            $request_method = $_SERVER['REQUEST_METHOD'];
            $_data = 'POST' === $request_method ? $_POST : $_GET;
        } elseif (!empty($_REQUEST)) {
            $_data = $_REQUEST;
        } else {
            $_data = [];
        }

        return new ParameterBag($_data);
    }
}
