<?php

namespace App\Controller;

use App\Core\Controller;
use PDO;
use Respect\Validation\Exceptions\NestedValidationException;
use Respect\Validation\validator as v;
use App\Models\User;
use App\Core\Session;
use App\Core\FileLogger;

class AuthController extends Controller
{
    private User $userModel;
    private FileLogger $fileLogger;

    public function __construct(PDO $db)
    {
        parent::__construct($db);
        $this->userModel = new User($db);
        $this->fileLogger = new FileLogger();
    }

    public function registerPage(): void
    {
        echo $this->render('auth/register.twig', ['csrf_token' => $this->generateCsrfToken()]);
    }

    public function loginPage(): void
    {
        echo $this->render('auth/login.twig', ['csrf_token' => $this->generateCsrfToken()]);
    }
    public function login(): void
    {

        $data = [
            'email' => filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL),
            'password' => $_POST['password'] ?? ''
        ];

        $validator = v::attribute('email', v::email()->notEmpty())
            ->attribute('password', v::stringType()->notEmpty());

        try {
            $validator->assert((object)$data);
        } catch (NestedValidationException $e) {
            echo $this->render('auth/login.twig', [
                'errors' => $this->getValidationErrors($e),
                'old' => $data,
                'csrf_token' => $this->generateCsrfToken()
            ]);
            return;
        }

        if ($this->verifyPassword($data['email'], $data['password'])) {
            $user = $this->userModel->findByEmail($data['email']);
            Session::login($user['id'], $user["email"], $user["name"]);
            Session::set('success', 'Welcome back, ' . $user['name']);
             // add to login history
            $this->fileLogger->log("INFO", "logged in with email: " . $data['email']);

            $this->redirect("/");
        } else {
            echo $this->render('auth/login.twig', [
                'errors' => ['general' => 'Invalid credentials'],
                'old' => $data,
                'csrf_token' => $this->generateCsrfToken()
            ]);
        }
    }


    public function register(): void
    {

        $data = [
            'name' => filter_input(INPUT_POST, 'name'),
            'email' => filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL),
            'password' => $_POST['password'] ?? '',
            'confirm_password' => $_POST['confirm_password'] ?? ''
        ];

        $validator = v::attribute('name', v::stringType()->notEmpty())
            ->attribute('email', v::email()->notEmpty())
            ->attribute('password', v::stringType()->length(6, null)->notEmpty())
            ->attribute('confirm_password', v::equals($data['password']));

        try {
            $validator->assert((object)$data);
        } catch (NestedValidationException $e) {
            echo $this->render('auth/register.twig', [
                'errors' => $this->getValidationErrors($e),
                'old' => $data,
                'csrf_token' => $this->generateCsrfToken()
            ]);
            return;
        }

        if ($this->userModel->findByEmail($data['email'])) {
            echo $this->render('auth/register.twig', [
                'errors' => ['email' => 'Email already registered'],
                'old' => $data,
                'csrf_token' => $this->generateCsrfToken()
            ]);
            return;
        }

        $userId = $this->userModel->createUser($data);

        if ($userId) {
            Session::set('success', 'Registration successful. Please log in.');
            $this->fileLogger->log("INFO", "User registered with email: " . $data['email']);
            $this->redirect("/login");
        } else {
            echo $this->render('auth/register.twig', [
                'errors' => ['general' => 'Registration Failed'],
                'old' => $data,
                'csrf_token' => $this->generateCsrfToken()
            ]);
        }
    }

    public function verifyPassword(string $email, string $password): bool
    {
        $user = $this->userModel->findByEmail($email);

        if (!$user) {
            return false;
        }

        return password_verify($password, $user['password']);
    }

    public function logout(): void
    {
        Session::logout();
        $this->redirect("/login");
    }


}
