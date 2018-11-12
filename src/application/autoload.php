<?php
error_reporting(E_ALL);

ini_set('display_errors', 'on');

function exception_error_handler($errno, $errstr, $errfile, $errline )
{
    throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
}
set_error_handler("exception_error_handler");

//date_default_timezone_set('Europe/Berlin');
date_default_timezone_set("GMT");


spl_autoload_register(function ($class) {

    // project-specific namespace prefix
    $prefix = 'Zeitfaden\\';

    // base directory for the namespace prefix
    $base_dir = __DIR__ . '/';

    // does the class use the namespace prefix?
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        // no, move to the next registered autoloader
        return;
    }

    // get the relative class name
    $relative_class = substr($class, $len);

    // replace the namespace prefix with the base directory, replace namespace
    // separators with directory separators in the relative class name, append
    // with .php
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

    // if the file exists, require it
    if (file_exists($file)) {
        require $file;
    }
});


require_once(dirname(__FILE__).'/../vendor/autoload.php');




