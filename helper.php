<?php

use JazzMan\AppConfig\Config;
use JazzMan\AppConfig\Manifest;
use JazzMan\ParameterBag\ParameterBag;

if (!function_exists('app_config')) {
    function app_config(): ParameterBag
    {
        return Config::getInstance()
            ->getConfig()
        ;
    }
}

if (!function_exists('app_dir_path')) {
    /**
     * @param  string  $path
     *
     * @return string
     */
    function app_dir_path(string $path): string
    {
        return app_config()->get('root_dir').DIRECTORY_SEPARATOR.trim($path, DIRECTORY_SEPARATOR);
    }
}

if (!function_exists('app_url_patch')) {
    /**
     * @param  string  $path
     *
     * @return string
     */
    function app_url_path(string $path): string
    {
        return app_config()->get('root_url').'/'.trim($path, '/');
    }
}

if (!\function_exists('app_use_webp')) {
    /**
     * @return bool
     */
    function app_use_webp(): bool
    {
        $acceptWebp = filter_input(INPUT_SERVER, 'HTTP_ACCEPT', FILTER_VALIDATE_REGEXP, [
            'options' => [
                'regexp' => '/image\\/webp/',
            ],
        ]);

        if (!empty($acceptWebp)) {
            return true;
        }

        $isGoogle = filter_input(INPUT_SERVER, 'HTTP_USER_AGENT', FILTER_VALIDATE_REGEXP, [
            'options' => [
                'regexp' => '/\s+(Chrome\/|Googlebot\/)/i',
            ],
        ]);

        if (!empty($isGoogle)) {
            return true;
        }

        $isSafari = filter_input(INPUT_SERVER, 'HTTP_USER_AGENT', FILTER_VALIDATE_REGEXP, [
            'options' => [
                'regexp' => '/Version.[\d\.]*\s+Safari.[\d\.]*/i',
            ],
        ]);

        $isFirefox = filter_input(INPUT_SERVER, 'HTTP_USER_AGENT', FILTER_VALIDATE_REGEXP, [
            'options' => [
                'regexp' => '/\s+Firefox.[\d\.]*/i',
            ],
        ]);

        if ($isSafari && (\preg_match('/Version.(?<v>[\d.]+)?/i', $isSafari, $res) && version_compare($res['v'], '13', '>='))) {
            return true;
        }

        if ($isFirefox && (\preg_match('/Firefox\/(?<v>[\d.]+)?/i', $isFirefox, $res) && version_compare($res['v'], '65', '>='))) {
            return true;
        }

        return false;
    }
}

if (!\function_exists('app_get_request_data')) {
    /**
     * @return \JazzMan\ParameterBag\ParameterBag
     */
    function app_get_request_data(): ParameterBag
    {
        $method = filter_input(INPUT_SERVER, 'REQUEST_METHOD', FILTER_VALIDATE_REGEXP, [
            'options' => [
                'regexp' => '/get|post/i',
            ],
        ]);

        if ($method) {
            $_data = filter_input_array('POST' === $method ? INPUT_POST : INPUT_GET);
        } elseif (!empty($_REQUEST)) {
            $_data = $_REQUEST;
        } else {
            $_data = [];
        }

        return new ParameterBag($_data);
    }
}

if (!\function_exists('app_json_decode')) {
    /**
     * @param  string  $json
     * @param  bool  $associative
     * @param  int  $depth
     * @param  int  $flags
     *
     * @return mixed
     */
    function app_json_decode(string $json, bool $associative = false, int $depth = 512, int $flags = 0)
    {
        $data = \json_decode($json, $associative, $depth, $flags);
        if (JSON_ERROR_NONE !== \json_last_error()) {
            throw new InvalidArgumentException('json_decode error: '.\json_last_error_msg());
        }

        return $data;
    }
}

if (!\function_exists('app_files_in_path')) {
    /**
     * @param  string  $folder
     * @param  string  $pattern
     * @param  int  $max_depth
     * @return \RegexIterator
     */
    function app_files_in_path(string $folder, string $pattern, int $max_depth = 1): RegexIterator
    {
        if (!is_readable($folder)) {
            throw new InvalidArgumentException('folder is not exist');
        }

        $dir = new RecursiveDirectoryIterator($folder, FilesystemIterator::SKIP_DOTS);
        $ite = new RecursiveIteratorIterator($dir, RecursiveIteratorIterator::SELF_FIRST);
        $ite->setMaxDepth($max_depth);

        return new RegexIterator($ite, $pattern);
    }
}

if (!\function_exists('app_get_template')) {
    /**
     * @param  string  $template
     * @param  array  $attributes
     * @return false|string
     */
    function app_get_template(string $template, array $attributes = [])
    {
        $result = '';

        $template_file = locate_template($template);

        if (!empty($template_file)) {
            if (!empty($attributes)) {
                \extract($attributes, EXTR_OVERWRITE);
            }

            \ob_start();
            @include $template_file;
            $result = \ob_get_clean();
        }

        return $result;
    }
}

if (!\function_exists('app_base64_encode_data')) {
    /**
     * @param  string  $str
     * @return bool|string
     */
    function app_base64_encode_data(string $str): string
    {
        if (\base64_encode(\base64_decode($str, true)) === $str) {
            $str = \base64_decode($str);
        }

        return $str;
    }
}

if (!\function_exists('app_trim_string')) {
    /**
     * @param  string  $string
     *
     * @return string
     */
    function app_trim_string(string $string): string
    {
        return \trim(\preg_replace('/\s{2,}/siu', ' ', $string));
    }
}

if (!\function_exists('app_get_current_relative_url')) {
    /**
     * @return string
     */
    function app_get_current_relative_url(): string
    {
        $requestUri = filter_input(INPUT_SERVER, 'REQUEST_URI', FILTER_SANITIZE_STRING);

        $currentRelative = untrailingslashit($requestUri);

        if (is_customize_preview()) {
            $currentRelative = \strtok(untrailingslashit($requestUri), '?');
        }

        return $currentRelative;
    }
}

if (!\function_exists('app_get_current_url')) {
    /**
     * @return string
     */
    function app_get_current_url(): string
    {
        $host = filter_input(INPUT_SERVER, 'HTTP_HOST', FILTER_SANITIZE_STRING);

        return set_url_scheme('https://'.$host.app_get_current_relative_url());
    }
}

if (!\function_exists('app_is_current_host')) {
    /**
     * @param  string  $url
     *
     * @return bool
     */
    function app_is_current_host(string $url): bool
    {
        static $current_host;

        if (null === $current_host) {
            $current_host = parse_url(home_url(), PHP_URL_HOST);
        }

        if (!\filter_var($url, FILTER_VALIDATE_URL)) {
            return false;
        }

        $url_host = \parse_url($url, PHP_URL_HOST);

        return !empty($url_host) && $current_host === $url_host;
    }
}

if (!\function_exists('app_read_csv_file')) {
    /**
     * @param  string  $path
     * @return \Generator
     */
    function app_read_csv_file(string $path): Generator
    {
        if (\is_file($path)) {
            $handle = \fopen($path, 'rb');
            while (!\feof($handle)) {
                yield \fgetcsv($handle);
            }
            \fclose($handle);
        }
    }
}

if (!\function_exists('app_get_csv_data')) {
    /**
     * @param  string  $csv_file
     *
     * @return \Iterator
     */
    function app_get_csv_data(string $csv_file): Iterator
    {
        $iterator = app_read_csv_file($csv_file);

        $first_line = $iterator->current();
        $first_line_count = \count($first_line);

        return iter\map(
            static function ($value) use ($first_line, $first_line_count) {
                if (!\is_array($value)) {
                    return false;
                }
                $value_count = \count($value);
                if ($value !== $first_line && $first_line_count === $value_count) {
                    return \array_combine($first_line, $value);
                }

                return false;
            },
            $iterator
        );
    }
}

if (!\function_exists('app_error_log')) {
    /**
     * @param  \Exception  $exception
     * @param  string  $error_code
     * @return \WP_Error
     */
    function app_error_log(Exception $exception, string $error_code): WP_Error
    {
        $error = new WP_Error();
        if (\defined('WP_DEBUG_LOG') && WP_DEBUG_LOG) {
            \error_log($exception);
        }

        $error->add($error_code, $exception->getMessage());

        return $error;
    }
}

if (!\function_exists('app_generate_random_string')) {
    /**
     * @param  string  $input
     * @param  int  $strength
     * @return string
     * @throws \Exception
     */
    function app_generate_random_string(string $input, int $strength = 16): string
    {
        $input_length = \strlen($input);
        $random_string = '';
        for ($i = 0; $i < $strength; ++$i) {
            $random_character = $input[\random_int(0, $input_length - 1)];
            $random_string .= $random_character;
        }

        return \strtoupper($random_string);
    }
}

if (!\function_exists('app_add_attr_to_el')) {
    /**
     * @param  array<string,string|string[]>  $attr
     *
     * @return string
     */
    function app_add_attr_to_el(array $attr): string
    {
        $attributes = [];
        foreach ($attr as $key => $value) {
            if (\is_array($value)) {
                $value = \implode(' ', \array_filter($value));
            }
            if ('class' === $key && '' === $value) {
                continue;
            }

            if (!\is_bool($value)) {
                $attributes[] = \sprintf('%s="%s"', esc_attr($key), esc_attr($value));
            }

            if (true === $value) {
                $attributes[] = esc_attr($key);
            }
        }

        return ' '.\implode(' ', $attributes);
    }
}

if (!\function_exists('app_get_dom_content')) {
    /**
     * @param  string  $content
     * @return \DOMDocument
     */
    function app_get_dom_content(string $content): DOMDocument
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
        $dom->encoding = 'UTF-8';
        \libxml_use_internal_errors(true);

        $content = \str_replace(PHP_EOL, null, $content);
        $content = \mb_convert_encoding($content, 'HTML-ENTITIES', 'UTF-8');

        $dom->loadHTML(
            $content,
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_COMPACT | LIBXML_NOBLANKS | LIBXML_PEDANTIC
        );

        return $dom;
    }
}

if (!\function_exists('app_manifest')) {
    /**
     * @param  string  $manifest_file
     * @param  string  $dist_dir
     *
     * @return \JazzMan\AppConfig\Manifest
     */
    function app_manifest(string $manifest_file = 'dist/mix-manifest.json', string $dist_dir = 'dist'): Manifest
    {
        /** @var Manifest $manifest */
        static $manifest;
        if (empty($manifest)) {
            $manifest = Manifest::getInstance($manifest_file, $dist_dir);
        }

        return $manifest;
    }
}

if (!function_exists('app_get_human_friendly')) {
    /**
     * @param  string  $name
     *
     * @return string
     */
    function app_get_human_friendly(string $name = ''): string
    {
        return ucwords(strtolower(str_replace(['-', '_'], ' ', $name)));
    }
}
