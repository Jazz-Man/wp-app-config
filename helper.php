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

if (!\function_exists('app_get_taxonomy_ancestors')) {
    /**
     * @param  int  $term_id
     * @param  string  $taxonomy
     * @param  int  $mode
     * @param  mixed  ...$args
     *
     * @return array
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
