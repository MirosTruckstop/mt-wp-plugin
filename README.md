# MT Wordpress Plugin

[![Build Status](https://travis-ci.org/MirosTruckstop/mt-wp-plugin.svg?branch=master)](https://travis-ci.org/MirosTruckstop/mt-wp-plugin)

### Development

Requirements
* PHP and Composer (dependency manager for PHP) are installed

Steps
1. Install the requirements: `composer install`
2. Run the unittests: `./vendor/bin/phpunit --bootstrap vendor/autoload.php src/test/php/`

#### Sync required files

Create the CSS files
```sh
./node_modules/less/bin/lessc src/less/back-end/back-end.less dist/back-end.css
./node_modules/less/bin/lessc src/less/front-end/front-end.less dist/front-end.css
```

Sync the required files
```sh
rsync -r --relative *.php dist/ languages/ vendor/autoload.php vendor/composer vendor/symfony/polyfill-ctype vendor/myclabs/deep-copy src/js src/js src/main/ <host>:<wordpress-dir>/wp-content/plugins/mt-wp-plugin/
```
