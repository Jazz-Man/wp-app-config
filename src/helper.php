<?php

use JazzMan\AppConfig\Config;
use JazzMan\AppConfig\Manifest;
use JazzMan\ParameterBag\ParameterBag;

if (!function_exists('app_config')) {
    function app_config(): ParameterBag {
        return Config::getInstance()
            ->getConfig()
        ;
    }
}

if (!function_exists('app_dir_path')) {
    function app_dir_path(string $path): string {
        return app_config()->get('root_dir').DIRECTORY_SEPARATOR.trim($path, DIRECTORY_SEPARATOR);
    }
}

if (!function_exists('app_url_patch')) {
    function app_url_path(string $path): string {
        return app_config()->get('root_url').'/'.trim($path, '/');
    }
}

if (!function_exists('app_use_webp')) {
    function app_use_webp(): bool {
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

        if ($isSafari && (preg_match('/Version.(?<v>[\d.]+)?/i', $isSafari, $res) && version_compare($res['v'], '13', '>='))) {
            return true;
        }

        return $isFirefox && (preg_match('/Firefox\/(?<v>[\d.]+)?/i', $isFirefox, $res) && version_compare($res['v'], '65', '>='));
    }
}

if (!function_exists('app_get_request_data')) {
    function app_get_request_data(): ParameterBag {
        $method = filter_input(INPUT_SERVER, 'REQUEST_METHOD', FILTER_VALIDATE_REGEXP, [
            'options' => [
                'regexp' => '/get|post/i',
            ],
        ]);

        $data = $method ? filter_input_array(
            'POST' === $method ? INPUT_POST : INPUT_GET
        ) : (!empty($_REQUEST) ? $_REQUEST : []);

        return new ParameterBag((array) $data);
    }
}

if (!function_exists('app_json_decode')) {
    /**
     * @return mixed
     */
    function app_json_decode(string $json, bool $associative = false, int $depth = 512, int $flags = 0) {
        $data = json_decode($json, $associative, $depth, $flags);

        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new InvalidArgumentException(sprintf('json_decode error: %s', json_last_error_msg()));
        }

        return $data;
    }
}

if (!function_exists('app_files_in_path')) {
    function app_files_in_path(string $folder, string $pattern, int $maxDepth = 1): RegexIterator {
        if (!is_readable($folder)) {
            throw new InvalidArgumentException('folder is not exist');
        }

        $dir = new RecursiveDirectoryIterator($folder, FilesystemIterator::SKIP_DOTS);
        $ite = new RecursiveIteratorIterator($dir, RecursiveIteratorIterator::SELF_FIRST);
        $ite->setMaxDepth($maxDepth);

        return new RegexIterator($ite, $pattern);
    }
}

if (!function_exists('app_get_template')) {
    /**
     * @return false|string
     */
    function app_get_template(string $template, array $attributes = []) {
        $result = '';

        $templateFile = locate_template($template);

        if (!empty($templateFile)) {
            if (!empty($attributes)) {
                extract($attributes, EXTR_OVERWRITE);
            }

            ob_start();

            include $templateFile;
            $result = ob_get_clean();
        }

        return $result;
    }
}

if (!function_exists('app_base64_encode_data')) {
    /**
     * @return bool|string
     */
    function app_base64_encode_data(string $str): string {
        if (base64_encode(base64_decode($str, true)) === $str) {
            $str = base64_decode($str, true);
        }

        return $str;
    }
}

if (!function_exists('app_trim_string')) {
    function app_trim_string(string $string): string {
        return trim(preg_replace('/\s{2,}/siu', ' ', $string));
    }
}

if (!function_exists('app_get_current_relative_url')) {
    function app_get_current_relative_url(): string {
        $requestUri = filter_input(INPUT_SERVER, 'REQUEST_URI', FILTER_SANITIZE_STRING);

        $currentRelative = untrailingslashit($requestUri);

        if (is_customize_preview()) {
            $currentRelative = strtok(untrailingslashit($requestUri), '?');
        }

        return $currentRelative;
    }
}

if (!function_exists('app_get_current_url')) {
    function app_get_current_url(): string {
        $host = filter_input(INPUT_SERVER, 'HTTP_HOST', FILTER_SANITIZE_STRING);

        return set_url_scheme('https://'.$host.app_get_current_relative_url());
    }
}

if (!function_exists('app_is_current_host')) {
    function app_is_current_host(string $url): bool {
        static $currentHost;

        if (null === $currentHost) {
            $currentHost = parse_url(home_url(), PHP_URL_HOST);
        }

        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return false;
        }

        $host = parse_url($url, PHP_URL_HOST);

        return !empty($host) && $currentHost === $host;
    }
}

if (!function_exists('app_error_log')) {
    /**
     * @param \Exception $exception
     */
    function app_error_log(Exception $exception, string $errorCode): WP_Error {
        $error = new WP_Error();

        if (defined('WP_DEBUG_LOG') && WP_DEBUG_LOG) {
            error_log($exception);
        }

        $error->add($errorCode, $exception->getMessage());

        return $error;
    }
}

if (!function_exists('app_generate_random_string')) {
    /**
     * @throws \Exception
     */
    function app_generate_random_string(string $input, int $strength = 16): string {
        $input_length = strlen($input);
        $random_string = '';

        for ($i = 0; $i < $strength; ++$i) {
            $random_character = $input[random_int(0, $input_length - 1)];
            $random_string .= $random_character;
        }

        return strtoupper($random_string);
    }
}

if (!function_exists('app_add_attr_to_el')) {
    /**
     * @param array<string,null|bool|int|string|string[]> $attr
     */
    function app_add_attr_to_el(array $attr): string {
        $attributes = [];

        $separator = ' ';

        foreach ($attr as $key => $value) {
            if (is_array($value)) {
                $value = implode($separator, array_filter($value));
            }

            if ('class' === $key && '' === $value) {
                continue;
            }

            $is_url = in_array($key, ['src', 'href'], true);

            if (!is_bool($value)) {
                $attributes[] = sprintf(
                    '%s="%s"',
                    esc_attr($key),
                    $is_url ? esc_url($value) : esc_attr($value)
                );
            }

            if (true === $value) {
                $attributes[] = esc_attr($key);
            }
        }

        return $separator.implode($separator, $attributes);
    }
}

if (!function_exists('app_get_dom_content')) {
    function app_get_dom_content(string $content): DOMDocument {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
        $dom->encoding = 'UTF-8';
        libxml_use_internal_errors(true);

        $content = str_replace(PHP_EOL, null, $content);
        $content = mb_convert_encoding($content, 'HTML-ENTITIES', 'UTF-8');

        $dom->loadHTML(
            $content,
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_COMPACT | LIBXML_NOBLANKS | LIBXML_PEDANTIC
        );

        return $dom;
    }
}

if (!function_exists('app_manifest')) {
    function app_manifest(string $manifestFile = 'dist/mix-manifest.json', string $distDir = 'dist'): Manifest {
        /** @var Manifest $manifest */
        static $manifest;

        if (empty($manifest)) {
            $manifest = Manifest::getInstance($manifestFile, $distDir);
        }

        return $manifest;
    }
}

if (!function_exists('app_strtolower')) {
    function app_strtolower(string $string): string {
        if (function_exists('mb_strtolower')) {
            return mb_strtolower($string, 'UTF-8');
        }

        return strtolower($string);
    }
}

if (!function_exists('app_ucwords')) {
    function app_ucwords(string $string): string {
        if (function_exists('mb_convert_case')) {
            return mb_convert_case($string, MB_CASE_TITLE, 'UTF-8');
        }

        return ucwords($string);
    }
}

if (!function_exists('app_get_human_friendly')) {
    function app_get_human_friendly(string $name): string {
        return app_ucwords(app_strtolower(str_replace(['-', '_'], ' ', $name)));
    }
}

if (!function_exists('app_string_slugify')) {
    function app_string_slugify(string $string): string {
        $separator = '-';

        if (class_exists(Normalizer::class)) {
            $string = Normalizer::normalize($string);
        }

        $string = trim(strip_tags($string));

        if (function_exists('transliterator_transliterate')) {
            $string = transliterator_transliterate('Any-Latin; NFD; [:Nonspacing Mark:] Remove; NFC; [:Punctuation:] Remove; Lower()', $string);
        } else {
            $string = preg_replace([
                '/[[:punct:]]/',
                '#[^A-Za-z1-9]#',
            ], $separator, $string);

            $string = app_strtolower($string);
        }

        $string = preg_replace('/[-\s]+/', $separator, $string);

        return trim($string, $separator);
    }
}

if (!function_exists('app_locate_root_dir')) {
    /**
     * @return false|string
     */
    function app_locate_root_dir() {
        static $path;

        if (null === $path) {
            $path = false;

            if (is_file(ABSPATH.'wp-config.php')) {
                $path = ABSPATH;
            } elseif (is_file(dirname(ABSPATH).'/wp-config.php') && !is_file(dirname(ABSPATH).'/wp-settings.php')) {
                $path = dirname(ABSPATH);
            }

            if ($path) {
                $path = realpath($path);
            }
        }

        return $path;
    }
}

if (!function_exists('app_is_rest')) {
    function app_is_rest(?string $prefix = null): bool {
        $wpRestPrefix = '/'.trailingslashit(rest_get_url_prefix());

        if (null !== $prefix) {
            $wpRestPrefix .= ltrim($prefix, '/');
        }

        $regexp = preg_quote("{$wpRestPrefix}", '/');

        return (bool) filter_input(INPUT_SERVER, 'REQUEST_URI', FILTER_VALIDATE_REGEXP, [
            'options' => [
                'regexp' => "/^{$regexp}/",
            ],
        ]);
    }
}
