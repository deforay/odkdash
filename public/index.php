<?php
 if ($_SERVER['APPLICATION_ENV'] == 'development') {
     error_reporting(E_ALL);
     ini_set("display_errors", 1);
 }

/**
 * This makes our life easier when dealing with paths. Everything is relative
 * to the application root now.
 */
chdir(dirname(__DIR__));

defined('CONFIG_PATH') || define('CONFIG_PATH', realpath(__DIR__."/../config"));

// Decline static file requests back to the PHP built-in webserver
if (php_sapi_name() === 'cli-server') {
    $path = realpath(__DIR__ . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
    if (__FILE__ !== $path && is_file($path)) {
        return false;
    }
    unset($path);
}

defined('UPLOAD_PATH')
    || define('UPLOAD_PATH', realpath(dirname(__FILE__) . '/uploads'));
    
defined('TEMP_UPLOAD_PATH')
    || define('TEMP_UPLOAD_PATH', realpath(dirname(__FILE__) . '/temporary'));

defined('font_path')
    || define('font_path', realpath(dirname(__FILE__) . '/assets/fonts/'));


defined('BACKUP_PATH')
    || define('BACKUP_PATH', realpath(dirname(__FILE__) . '/../backup'));
    
defined('APPLICATION_PATH')
    || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/assets'));
// Setup autoloading
require 'init_autoloader.php';

// Run the application!
Zend\Mvc\Application::init(require 'config/application.config.php')->run();
