<?php
use App\Middleware\AuthMiddleware;
use App\Middleware\CsrfMiddleware;

$csrfMiddleware = new CsrfMiddleware();
$authMiddleware = new AuthMiddleware();

$router->set404(function() use ($db){
    $controller = new App\Controller\HomeController($db);
    $controller->notFound();
});

// Home routes
$router->get('/', function() use ($db, $authMiddleware) {
    if(!$authMiddleware->handle()){
        return;
    }
    $controller = new App\Controller\HomeController($db);
    $controller->index();
});

$router->get('/register', function() use ($db, $authMiddleware) {
    if(!$authMiddleware->handle()){
        return;
    }
    $controller = new App\Controller\AuthController($db);
    $controller->registerPage();
});

$router->get('/login', function() use ($db, $authMiddleware) {
    if(!$authMiddleware->handle()){
        return;
    }
    $controller = new App\Controller\AuthController($db);
    $controller->loginPage();
});

$router->get('/logout', function() use ($db, $authMiddleware) {
   if(!$authMiddleware->handle()){
        return;
    }
    $controller = new App\Controller\AuthController($db);
    $controller->logout();
});

$router->post('/register', function() use ($db, $csrfMiddleware, $authMiddleware) {
    if(!$csrfMiddleware->handle() && !$authMiddleware->handle()){
        return;
    }
    $controller = new App\Controller\AuthController($db);
    $controller->register();
});


$router->post('/login', function() use ($db, $csrfMiddleware, $authMiddleware) {
    if(!$csrfMiddleware->handle() && !$authMiddleware->handle()){
        return;
    }
    $controller = new App\Controller\AuthController($db);
    $controller->login();
});