<?php

function autoload_api_classes_80202082016($class)
{
    $classes = [
        'BatchFileReader' => __DIR__ . '/reader.php',
        'BatchCSVProcessor' => __DIR__ . '/csv_processor.php',
        'BatchMagentoImporter' => __DIR__ . '/importer.php'
    ];
    //
    if (!empty($classes[$class])) {
        include $classes[$class];
    }
}

spl_autoload_register('autoload_api_classes_80202082016');

// Do nothing.
{

}