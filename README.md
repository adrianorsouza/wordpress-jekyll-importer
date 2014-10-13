Wordpress to Jekyll Importer
============================

[![Latest Stable Version](https://poser.pugx.org/adrianorsouza/wp-jekyll-importer/v/stable.svg)](https://packagist.org/packages/adrianorsouza/wp-jekyll-importer) [![Total Downloads](https://poser.pugx.org/adrianorsouza/wp-jekyll-importer/downloads.svg)](https://packagist.org/packages/adrianorsouza/wp-jekyll-importer) [![Latest Unstable Version](https://poser.pugx.org/adrianorsouza/wp-jekyll-importer/v/unstable.svg)](https://packagist.org/packages/adrianorsouza/wp-jekyll-importer) [![License](https://poser.pugx.org/adrianorsouza/wp-jekyll-importer/license.svg)](https://packagist.org/packages/adrianorsouza/wp-jekyll-importer)

A simple PHP script to import posts and pages from [Wordpress](https://wordpress.org/) to [Jekyll](http://jekyllrb.com/).

##Installation via composer
This project can be found at [Composer/Packagist](https://packagist.org/packages/adrianorsouza/wp-jekyll-importer).

	$ composer require "adrianorsouza/wp-jekyll-importer:0.1.*"
  
*This command will automatically update or create a `composer.json` file.*

###Manual Installation
Download the latest [release](https://github.com/adrianorsouza/wordpress-jekyll-importer/releases) of this project.


## Get started 

**First export your data from Wordpress installation to a `WXR-data.xml` file by following these steps:**

1. Log in to that Wordpress as an administrator.
2. Go to menu `Tools` in the WordPress admin panel then select `Export`.
3. Choose what to export to the `WXR` file:  `All content`, `Posts` or `Pages`.
4. Click download Export File, save it at the top level of this project.

## Usage 

via command line run the following:

	./ImportRun.php

###License

This software is licensed under the MIT License. Please read LICENSE for information on the software availability and distribution.


