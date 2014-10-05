<?php

/**
 *
 * @package JekyllImporter
 * @author Adriano Rosa (http://adrianorosa.com)
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 * @link https://github.com/adrianorsouza/jekyll-wordpress-importer
 */

/**
 * Autoloader for JekyllImporter
 *
 * @param $class Classname
 * @return void
 */
function Autoload($class)
{
    $ns = 'JekyllImporter';

    if (strncmp($ns, $class, strlen($ns)) === 0) {
        $classname = substr($class, strlen($ns));
        $file = __DIR__ . str_replace('\\', DIRECTORY_SEPARATOR, $classname) . '.php';

        if (is_readable($file)) {
            include $file;
        }
    }
}

spl_autoload_register('Autoload');
