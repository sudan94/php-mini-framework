<?php

require_once __DIR__ .'/../vendor/autoload.php';

use App\Core\Session;
use Bramus\Router\Router;
use Dotenv\Dotenv;
use App\Core\Database;
use Whoops\Run;
use Whoops\Handler\PrettyPageHandler;
use App\Core\SchemaLoader;

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

$router = new Router();

require_once __DIR__.'/../routes/web.php';
$router->run();