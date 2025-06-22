<?php

require_once __DIR__ .'/../vendor/autoload.php';

use App\Core\Session;
use Bramus\Router\Router;
use Dotenv\Dotenv;
use App\Core\Database;
use Whoops\Run;
use Whoops\Handler\PrettyPageHandler;
use App\Core\SchemaLoader;
use App\Core\FileLogger;

Session::start();


$dotenv = Dotenv::createImmutable(__DIR__.'/..');
$dotenv->load();

$db = Database::getInstance()->getConnection();

if ($_ENV['APP_ENV'] === 'development') {
    $whoops = new Run();
    $whoops->pushHandler(new PrettyPageHandler());
    $whoops->register();

    $schema = new SchemaLoader($db);
    $schema->runFromFile(__DIR__ . '/../database/schema.sql');

} else {
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', '0');
    error_reporting(0);
}

if ($_ENV['APP_ENV'] === 'production') {
    $errorLogger = new FileLogger(__DIR__ . '/../logs/');

    set_error_handler(function ($severity, $message, $file, $line) use ($errorLogger) {
        $errorLogger->log('ERROR', "PHP Error: $message in $file on line $line");
        if (!headers_sent()) {
            header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '/'));
            exit;
        }
    });

    set_exception_handler(function ($exception) use ($errorLogger) {
        $errorLogger->log('ERROR', 'Uncaught Exception: ' . $exception->getMessage() . "\nTrace: " . $exception->getTraceAsString());
        if (!headers_sent()) {
            header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '/'));
            exit;
        }
    });
}

$router = new Router();

require_once __DIR__.'/../routes/web.php';
$router->run();