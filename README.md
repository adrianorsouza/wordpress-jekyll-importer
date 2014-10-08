Wordpress to Jekyll Importer
============================

A simple PHP script to import posts and pages from Wordpress to Jekyll.

##Installation via composer
This project can be found at [Composer/Packagist](https://packagist.org/packages/adrianorsouza/wp-jekyll-importer).

	$ composer require "adrianorsouza/wp-jekyll-importer:0.1.*"
  
*This command will automatically update or create a `composer.json` file.*

###Manual Installation
Download the latest [release](https://github.com/adrianorsouza/wordpress-jekyll-importer/releases) of this project.


## Get started 

**First dump a `WXR-data.xml` file from Wordpress installation by following these steps:**

1. Log in to that Wordpress as an administrator.
2. Go to menu `Tools` in the WordPress admin panel then select `Export`.
3. Choose what to export to the `WXR` file:  `All content`, `Posts` or `Pages`.
4. Click download Export File, save it at the root of this directory.


Usage via command line run the following

	./ImportRun.php

###License

This software is licensed under the MIT License. Please read LICENSE for information on the software availability and distribution.


