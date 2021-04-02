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
    private $dist_dir;

    public function __construct(string $manifest_file = 'dist/mix-manifest.json', string $dist_dir = 'dist')
    {
        $manifest_file = app_dir_path($manifest_file);

        $this->dist_dir = $dist_dir;

        if (is_readable($manifest_file)) {
            try {
                $manifest = file_get_contents($manifest_file);

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

            return !empty($this->manifest[$path]) ? app_url_path("{$this->dist_dir}{$this->manifest[$path]}") : '';
        }

        return '';
    }

    public function getPath(string $path): string
    {
        if (!empty($this->manifest)) {
            $path = '/'.ltrim($path, '/');

            return !empty($this->manifest[$path]) ? app_dir_path("{$this->dist_dir}{$this->manifest[$path]}") : '';
        }

        return '';
    }
}
