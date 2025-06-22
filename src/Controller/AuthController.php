<?php

namespace App\Controller;

use App\Core\Controller;
use PDO;
use Respect\Validation\validator as v;
use App\Models\User;
use App\Core\Session;

class AuthController extends Controller
{
    private User $userModel;

    public function __construct(PDO $db)
    {
        parent::__construct($db);
        $this->userModel = new User($db);
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

        // Validate input using Respect/Validation
        $validator = v::key('email', v::email()->notEmpty())
            ->key('password', v::stringType()->notEmpty());

        try {
            $validator->assert($data);
        } catch (\Respect\Validation\Exceptions\ValidationException $e) {
            return;
        }

        if ($this->verifyPassword($data['email'], $data['password'])) {
            $user = $this->userModel->findByEmail($data['email']);
            Session::login($user['id'], $data["email"]);
            Session::set('success', 'Welcome back, ' . $user['name']);
            $this->redirect("/");
        } else {
            Session::set('error', 'Invalid credentials');
            $this->redirect("/login");
        }
    }


    public function register(): void
    {

        $data = [
            'email' => filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL),
            'password' => $_POST['password'] ?? '',
            'name' => filter_input(INPUT_POST, 'name')
        ];

        // validation using Respect/validation
        $validator = v::key('email', v::email()->notEmpty())
            ->key('password', v::stringType()->length(6, null)->notEmpty())
            ->key('confirm_password', V::equals('password'))
            ->key('name', v::stringType()->notEmpty()
        );

        try {
            $validator->assert($data);
        } catch (\Respect\Validation\Exceptions\ValidationException $e) {
            Session::set('error', $e->getMessage());
           $this->redirect("/register");
        }

        if ($this->userModel->findByEmail($data['email'])) {
            Session::set('error', 'Email already registered');
           $this->redirect("/register");
        }

        $userId = $this->userModel->createUser($data);

        if ($userId) {
            Session::set('success', 'Registration successfull');
            $this->redirect("/login");
        } else {
            Session::set('error', 'Registration Failed');
            $this->redirect("/register");
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
