#!/usr/bin/env php
<?php

require_once 'src/JekyllImporter/autoloader.php';

if(!defined("STDIN")) {
    define('DEBUG', true);
}

date_default_timezone_set('UTC');

use JekyllImporter\WordpressImporter;

$args = isset($argv) ? $argv : array();

$import = new WordpressImporter();
$import->run($args);
