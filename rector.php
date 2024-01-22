<?php

use Rector\Config\RectorConfig;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Set\ValueObject\SetList;

return static function ( RectorConfig $config ): void {
    $config->sets( [
        SetList::CODE_QUALITY,
        SetList::CODING_STYLE,
        SetList::TYPE_DECLARATION,
        SetList::EARLY_RETURN,
        SetList::NAMING,
        SetList::PRIVATIZATION,
        SetList::INSTANCEOF,
        SetList::DEAD_CODE,
        SetList::STRICT_BOOLEANS,
        LevelSetList::UP_TO_PHP_82,
    ] );

    $config->fileExtensions( ['php'] );
    $config->importNames();
    $config->removeUnusedImports();
    $config->importShortClasses( false );
    $config->parallel();
    $config->phpstanConfig( __DIR__.'/phpstan-rector.neon' );

    $config->paths( [
        __DIR__.'/src',
    ] );

    $config->skip( [
        __DIR__.'/vendor',
    ] );
};
