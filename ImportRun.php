#!/usr/bin/env php
<?php

if ( file_exists('vendor/autoload.php') ) {
    // Use importer whether Composer is present
    require_once 'vendor/autoload.php';
} else {
    // without Composer
    require_once 'src/JekyllImporter/autoloader.php';
}

if(!defined("STDIN")) {
    define('DEBUG', true);
}

date_default_timezone_set('UTC');

use JekyllImporter\WordpressImporter;

$args = isset($argv) ? $argv : [];

$import = new WordpressImporter();
$import->run($args);
