<?php

namespace App\Controller;

use App\Core\Controller;
use PDO;

class HomeController extends Controller {
    public function __construct(PDO $db)
    {
        parent::__construct();
    }

    public function index() : void {
         echo $this->render('home/index.twig', [
            'title' => 'Welcome to the Technical Challenge',
            'session' => $_SESSION
        ]);

    }

    public function notFound(): void {
        echo $this->render('layouts/404error.twig');
    }
}