<?php

namespace JazzMan\AppConfig;

use JazzMan\Traits\SingletonTrait;

class Manifest
{
    use SingletonTrait;

    /**
     * @var array
     */
    private $manifest = [];
    /**
     * @var string
     */
    private $distDir;

    public function __construct(string $manifestFile = 'dist/mix-manifest.json', string $distDir = 'dist')
    {
        $manifestFile = app_dir_path($manifestFile);

        $this->distDir = $distDir;

        if (is_readable($manifestFile)) {
            try {
                $manifest = file_get_contents($manifestFile);

                $this->manifest = app_json_decode($manifest, true);
            } catch (\Exception $exception) {
                app_error_log($exception, 'get_manifest_json');
                $this->manifest = [];
            }
        }
    }

    public function getUrl(string $path): string
    {
        if (!empty($this->manifest)) {
            $path = '/'.ltrim($path, '/');

            return !empty($this->manifest[$path]) ? app_url_path("{$this->distDir}{$this->manifest[$path]}") : '';
        }

        return '';
    }

    public function getPath(string $path): string
    {
        if (!empty($this->manifest)) {
            $path = '/'.ltrim($path, '/');

            return !empty($this->manifest[$path]) ? app_dir_path("{$this->distDir}{$this->manifest[$path]}") : '';
        }

        return '';
    }
}
