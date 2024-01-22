<?php

if ( ! function_exists( 'app_dir_path' ) ) {
    function app_dir_path( string $path, string $scheme = 'stylesheet' ): string {
        static $_scheme;

        if ( null === $_scheme ) {
            $upload = wp_upload_dir();

            $_scheme['parent'] = get_template_directory();
            $_scheme['stylesheet'] = get_stylesheet_directory();
            $_scheme['upload'] = $upload['basedir'];
        }

        if ( empty( $_scheme[$scheme] ) ) {
            return '';
        }

        $separator = DIRECTORY_SEPARATOR;

        $path = trim( $path, $separator );

        return "{$_scheme[$scheme]}{$separator}{$path}";
    }
}

if ( ! function_exists( 'app_get_url' ) ) {
    function app_get_url( string $path, string $scheme = 'stylesheet' ): string {
        static $_scheme;

        if ( null === $_scheme ) {
            $upload = wp_upload_dir();

            $_scheme['parent'] = get_template_directory_uri();
            $_scheme['stylesheet'] = get_stylesheet_directory_uri();
            $_scheme['upload'] = $upload['baseurl'];
        }

        if ( empty( $_scheme[$scheme] ) ) {
            return '';
        }

        $path = trim( $path, '/' );

        return "{$_scheme[$scheme]}/{$path}";
    }
}

if ( ! function_exists( 'app_url_patch' ) ) {
    function app_url_path( string $path, string $scheme = 'stylesheet' ): string {
        return app_get_url( $path, $scheme );
    }
}

if ( ! function_exists( 'app_upload_url' ) ) {
    function app_upload_url( string $path ): string {
        return app_get_url( $path, 'upload' );
    }
}

if ( ! function_exists( 'app_use_webp' ) ) {
    function app_use_webp(): bool {
        /** @var string|null $acceptWebp */
        $acceptWebp = app_get_server_data( 'HTTP_ACCEPT', FILTER_VALIDATE_REGEXP, [
            'options' => [
                'regexp' => '/image\\/webp/',
            ],
        ] );

        if ( ! empty( $acceptWebp ) ) {
            return true;
        }

        /** @var string|null $isGoogle */
        $isGoogle = app_get_server_data( 'HTTP_USER_AGENT', FILTER_VALIDATE_REGEXP, [
            'options' => [
                'regexp' => '/\s+(Chrome\/|Googlebot\/)/i',
            ],
        ] );

        if ( ! empty( $isGoogle ) ) {
            return true;
        }

        /** @var string|null $isSafari */
        $isSafari = app_get_server_data( 'HTTP_USER_AGENT', FILTER_VALIDATE_REGEXP, [
            'options' => [
                'regexp' => '/Version.[\d\.]*\s+Safari.[\d\.]*/i',
            ],
        ] );

        /** @var string|null $isFirefox */
        $isFirefox = app_get_server_data( 'HTTP_USER_AGENT', FILTER_VALIDATE_REGEXP, [
            'options' => [
                'regexp' => '/\s+Firefox.[\d\.]*/i',
            ],
        ] );

        if ( ! empty( $isSafari ) && ( preg_match( '/Version.(?<v>[\d.]+)?/i', $isSafari, $res ) && version_compare( $res['v'], '13', '>=' ) ) ) {
            return true;
        }

        return ! empty( $isFirefox ) && ( preg_match( '/Firefox\/(?<v>[\d.]+)?/i', $isFirefox, $res ) && version_compare( $res['v'], '65', '>=' ) );
    }
}

if ( ! function_exists( 'app_json_decode' ) ) {
    /**
     * @psalm-param int<1,max> $depth
     */
    function app_json_decode( string $json, bool $associative = false, int $depth = 512, int $flags = 0 ): mixed {
        /** @var mixed $data */
        $data = json_decode( $json, $associative, $depth, $flags );

        if ( JSON_ERROR_NONE !== json_last_error() ) {
            throw new InvalidArgumentException( sprintf( 'json_decode error: %s', json_last_error_msg() ) );
        }

        return $data;
    }
}

if ( ! function_exists( 'app_files_in_path' ) ) {
    /**
     * @psalm-return RegexIterator<RecursiveDirectoryIterator, mixed, RecursiveIteratorIterator<RecursiveDirectoryIterator>>
     */
    function app_files_in_path( string $folder, string $pattern, int $maxDepth = 1 ): RegexIterator {
        if ( ! is_readable( $folder ) ) {
            throw new InvalidArgumentException( 'folder is not exist' );
        }

        $dir = new RecursiveDirectoryIterator( $folder, FilesystemIterator::SKIP_DOTS );
        $ite = new RecursiveIteratorIterator( $dir, RecursiveIteratorIterator::SELF_FIRST );
        $ite->setMaxDepth( $maxDepth );

        return new RegexIterator( $ite, $pattern );
    }
}

if ( ! function_exists( 'app_get_template' ) ) {
    /**
     * @param array<string,mixed> $attributes
     *
     * @return false|string
     */
    function app_get_template( string $template, array $attributes = [] ): bool|string {
        $result = '';

        $templateFile = locate_template( $template );

        if ( ! empty( $templateFile ) ) {
            if ( ! empty( $attributes ) ) {
                extract( $attributes, EXTR_OVERWRITE );
            }

            ob_start();

            include $templateFile;
            $result = ob_get_clean();
        }

        return $result;
    }
}

if ( ! function_exists( 'app_base64_encode_data' ) ) {
    function app_base64_encode_data( string $str ): string {
        $decode = base64_decode( $str, true );

        if ( empty( $decode ) ) {
            return $str;
        }

        if ( base64_encode( $decode ) === $str ) {
            return $decode;
        }

        return $str;
    }
}

if ( ! function_exists( 'app_trim_string' ) ) {
    function app_trim_string( string $string ): string {
        $string = preg_replace( '/\s{2,}/siu', ' ', $string );

        return trim( (string) $string );
    }
}

if ( ! function_exists( 'app_get_current_relative_url' ) ) {
    function app_get_current_relative_url(): string {
        /** @var string|null $request */
        $request = app_get_server_data( 'REQUEST_URI', FILTER_SANITIZE_STRING );

        if ( empty( $request ) ) {
            $request = '/';
        }

        $relative = untrailingslashit( $request );

        if ( is_customize_preview() ) {
            $relative = (string) strtok( untrailingslashit( $request ), '?' );
        }

        return $relative;
    }
}

if ( ! function_exists( 'app_get_current_url' ) ) {
    function app_get_current_url(): string {
        /** @var string $host */
        $host = app_get_server_data( 'HTTP_HOST', FILTER_SANITIZE_STRING );

        return set_url_scheme( 'https://'.$host.app_get_current_relative_url() );
    }
}

if ( ! function_exists( 'app_is_current_host' ) ) {
    function app_is_current_host( string $url ): bool {
        static $currentHost;

        if ( null === $currentHost ) {
            $currentHost = parse_url( home_url(), PHP_URL_HOST );
        }

        if ( ! filter_var( $url, FILTER_VALIDATE_URL ) ) {
            return false;
        }

        $host = parse_url( $url, PHP_URL_HOST );

        return ! empty( $host ) && $currentHost === $host;
    }
}

if ( ! function_exists( 'app_error_log' ) ) {

    function app_error_log( Exception $exception, string $errorCode ): WP_Error {
        $error = new WP_Error();

        if ( defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
            error_log( (string) $exception );
        }

        $error->add( $errorCode, $exception->getMessage() );

        return $error;
    }
}

if ( ! function_exists( 'app_generate_random_string' ) ) {
    /**
     * @throws Exception
     */
    function app_generate_random_string( string $input, int $strength = 16 ): string {
        $string = '';

        if ( empty( $input ) ) {
            return $string;
        }

        $input_length = strlen( $input );

        for ( $i = 0; $i < $strength; ++$i ) {
            try {
                $character = $input[random_int( 0, $input_length - 1 )];
                $string .= $character;
            } catch ( Exception $e ) {
            }
        }

        return strtoupper( $string );
    }
}

if ( ! function_exists( 'app_add_attr_to_el' ) ) {
    /**
     * @param array<string,bool|int|string|string[]|null> $attr
     */
    function app_add_attr_to_el( array $attr ): string {
        $attributes = [];

        $separator = ' ';

        foreach ( $attr as $key => $value ) {
            if ( is_array( $value ) ) {
                $value = implode( $separator, array_filter( $value ) );
            }

            if ( 'class' === $key && '' === $value ) {
                continue;
            }

            $is_url = in_array( $key, ['src', 'href'], true );

            if ( ! is_bool( $value ) ) {
                $attributes[] = sprintf(
                    '%s="%s"',
                    esc_attr( $key ),
                    $is_url ? esc_url( (string) $value ) : esc_attr( (string) $value )
                );
            }

            if ( true === $value ) {
                $attributes[] = esc_attr( $key );
            }
        }

        return $separator.implode( $separator, $attributes );
    }
}

if ( ! function_exists( 'app_get_dom_content' ) ) {
    function app_get_dom_content( string $content ): DOMDocument {
        $dom = new DOMDocument( '1.0', 'UTF-8' );
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
        $dom->encoding = 'UTF-8';
        libxml_use_internal_errors( true );

        $content = str_replace( PHP_EOL, '', $content );

        if ( function_exists( 'mb_encode_numericentity' ) ) {
            $content = mb_encode_numericentity( $content, [0x80, 0x10FFFF, 0, ~0], 'UTF-8' );
        }

        $dom->loadHTML(
            $content,
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_COMPACT | LIBXML_NOBLANKS | LIBXML_PEDANTIC
        );

        return $dom;
    }
}

if ( ! function_exists( 'app_manifest' ) ) {
    /**
     * @return array<string,string>
     */
    function app_manifest(): array {
        /** @var array<string,string> $manifest */
        static $manifest = [];

        if ( empty( $manifest ) ) {
            $file = app_dir_path( 'dist/mix-manifest.json' );

            if ( is_readable( $file ) ) {
                try {
                    $content = file_get_contents( $file );

                    if ( ! empty( $content ) ) {
                        /** @var array<string,string> $manifest */
                        $manifest = app_json_decode( $content, true );
                    }
                } catch ( Exception $exception ) {
                    trigger_error( (string) $exception );
                    $manifest = [];
                }
            }
        }

        return $manifest;
    }
}

if ( ! function_exists( 'app_manifest_url' ) ) {
    function app_manifest_url( string $path ): string {
        $manifest = app_manifest();

        if ( ! empty( $manifest ) && ! empty( $manifest[$path] ) ) {
            $path = '/'.ltrim( $path, '/' );

            return app_url_path( "dist{$manifest[$path]}" );
        }

        return '';
    }
}

if ( ! function_exists( 'app_manifest_path' ) ) {
    function app_manifest_path( string $path ): string {
        $manifest = app_manifest();

        if ( ! empty( $manifest ) && ! empty( $manifest[$path] ) ) {
            $separator = DIRECTORY_SEPARATOR;

            $path = $separator.ltrim( $path, $separator );

            return app_dir_path( "dist{$manifest[$path]}" );
        }

        return '';
    }
}

if ( ! function_exists( 'app_strtolower' ) ) {
    function app_strtolower( string $string ): string {
        if ( function_exists( 'mb_strtolower' ) ) {
            return mb_strtolower( $string, 'UTF-8' );
        }

        return strtolower( $string );
    }
}

if ( ! function_exists( 'app_ucwords' ) ) {
    function app_ucwords( string $string ): string {
        if ( function_exists( 'mb_convert_case' ) ) {
            return mb_convert_case( $string, MB_CASE_TITLE, 'UTF-8' );
        }

        return ucwords( $string );
    }
}

if ( ! function_exists( 'app_get_human_friendly' ) ) {
    function app_get_human_friendly( string $name ): string {
        return app_ucwords( app_strtolower( str_replace( ['-', '_'], ' ', $name ) ) );
    }
}

if ( ! function_exists( 'app_string_slugify' ) ) {
    function app_string_slugify( string $string ): string {
        $separator = '-';

        if ( class_exists( Normalizer::class ) ) {
            $normalized = Normalizer::normalize( $string );

            if ( ! empty( $normalized ) ) {
                $string = $normalized;
            }
        }

        $string = wp_strip_all_tags( $string, true );

        if ( function_exists( 'transliterator_transliterate' ) ) {
            $string = transliterator_transliterate( 'Any-Latin; Latin-ASCII; NFD; [:Nonspacing Mark:] Remove; NFC; Lower();', $string );
        } else {
            $string = preg_replace( [
                '/[[:punct:]]/',
                '#[^A-Za-z1-9]#',
            ], $separator, $string );

            $string = app_strtolower( (string) $string );
        }

        $string = preg_replace( '/[^A-Za-z0-9_-]/', $separator, (string) $string );

        $string = preg_replace( '/[-\s]+/', $separator, (string) $string );

        return trim( (string) $string, $separator );
    }
}

if ( ! function_exists( 'app_locate_root_dir' ) ) {
    /**
     * @return false|string
     */
    function app_locate_root_dir(): bool|string {
        /** @var false|string|null $path */
        static $path;

        if ( null === $path ) {
            $path = false;

            if ( is_file( ABSPATH.'wp-config.php' ) ) {
                $path = ABSPATH;
            } elseif ( is_file( dirname( ABSPATH ).'/wp-config.php' ) && ! is_file( dirname( ABSPATH ).'/wp-settings.php' ) ) {
                $path = dirname( ABSPATH );
            }

            if ( $path ) {
                $path = realpath( $path );
            }
        }

        return $path;
    }
}

if ( ! function_exists( 'app_is_rest' ) ) {
    function app_is_rest( ?string $prefix = null ): bool {
        $wpRestPrefix = '/'.trailingslashit( rest_get_url_prefix() );

        if ( null !== $prefix ) {
            $wpRestPrefix .= ltrim( $prefix, '/' );
        }

        $regexp = preg_quote( "{$wpRestPrefix}", '/' );

        return (bool) app_get_server_data( 'REQUEST_URI', FILTER_VALIDATE_REGEXP, [
            'options' => [
                'regexp' => "/^{$regexp}/",
            ],
        ] );
    }
}
