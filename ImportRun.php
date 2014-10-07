#!/usr/bin/env php
<?php

require_once 'src/JekyllImporter/autoloader.php';

if(!defined("STDIN")) {
    define('DEBUG', true);
}

date_default_timezone_set('UTC');

use JekyllImporter\WordpressImporter;

$args = isset($argv) ? $argv : [];

$import = new WordpressImporter();
$import->run($args);
