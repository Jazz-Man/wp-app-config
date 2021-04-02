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

if (!\function_exists('app_json_decode')) {
    /**
     * @param mixed $json
     *
     * @return mixed
     */
    function app_json_decode($json, bool $assoc = false, int $depth = 512, int $options = 0)
    {
        $data = \json_decode($json, $assoc, $depth, $options);
        if (JSON_ERROR_NONE !== \json_last_error()) {
            throw new InvalidArgumentException('json_decode error: '.\json_last_error_msg());
        }

        return $data;
    }
}

if (!\function_exists('app_files_in_path')) {
    /**
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

if (!\function_exists('app_get_taxonomy_ancestors')) {
    /**
     * @param int   $mode
     * @param mixed ...$args
     */
    function app_get_taxonomy_ancestors(int $term_id, string $taxonomy, $mode = PDO::FETCH_COLUMN, ...$args): array
    {
        global $wpdb;

        $pdo = app_db_pdo();

        $sql = $pdo->prepare(
            <<<SQL
                with recursive ancestors as (
                  select
                    cat_1.term_id,
                    cat_1.taxonomy,
                    cat_1.parent
                  from {$wpdb->term_taxonomy} as cat_1
                  where
                    cat_1.term_id = :term_id
                  union all
                  select
                    a.term_id,
                    cat_2.taxonomy,
                    cat_2.parent
                  from ancestors a
                  inner join {$wpdb->term_taxonomy} cat_2 on cat_2.term_id = a.parent
                  where
                      cat_2.parent > 0
                  and cat_2.taxonomy = :taxonomy
                )
                select
                  a.parent as term_id,
                  a.taxonomy as taxonomy,
                  term.name as term_name,
                  term.slug as term_slug
                from ancestors a
                left join {$wpdb->terms} as term on term.term_id = a.parent
                SQL
        );

        $sql->execute(\compact('term_id', 'taxonomy'));

        return $sql->fetchAll($mode, ...$args);
    }
}

if (!\function_exists('app_get_template')) {
    /**
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
     * @return bool|string
     */
    function app_base64_encode_data(string $str)
    {
        if (\base64_encode(\base64_decode($str, true)) === $str) {
            $str = \base64_decode($str);
        }

        return $str;
    }
}

if (!\function_exists('app_trim_string')) {
    function app_trim_string(string $string): string
    {
        return \trim(\preg_replace('/\s{2,}/siu', ' ', $string));
    }
}

if (!\function_exists('app_get_current_relative_url')) {
    function app_get_current_relative_url(): string
    {
        $_root_relative_current = untrailingslashit($_SERVER['REQUEST_URI']);

        if (is_customize_preview()) {
            $_root_relative_current = \strtok(untrailingslashit($_SERVER['REQUEST_URI']), '?');
        }

        return $_root_relative_current;
    }
}

if (!\function_exists('app_get_current_url')) {
    function app_get_current_url(): string
    {
        return set_url_scheme('https://'.$_SERVER['HTTP_HOST'].app_get_current_relative_url());
    }
}

if (!\function_exists('app_read_csv_file')) {
    /**
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
