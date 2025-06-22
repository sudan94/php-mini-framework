<?php

namespace App\Controller;

use App\Core\Controller;
use App\Core\Session;
use Respect\Validation\Exceptions\NestedValidationException;
use Respect\Validation\validator as v;
use PDO;
use App\Models\User;

class UserController extends Controller {

    private User $userModel;

    public function __construct(PDO $db)
    {
        parent::__construct();
        $this->userModel = new User($db);
    }

    public function profilePage() : void{
        echo $this->render('users/profile.twig',[
            "user" => Session::user()
        ]);
    }

    public function editProfilePage() : void {
        echo $this->render('users/editProfile.twig',[
            "user" => Session::user(),
            'csrf_token' => $this->generateCsrfToken()
        ]);
    }

    public function updateUser() : void {
         $data = [
            'name' => filter_input(INPUT_POST, 'name'),
            'email' => filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL)
        ];

        $validator = v::attribute('name', v::stringType()->notEmpty())
            ->attribute('email', v::email()->notEmpty());

        try {
            $validator->assert((object)$data);
        } catch (NestedValidationException $e) {
            echo $this->render('users/editProfile.twig', [
                'errors' => $this->getValidationErrors($e),
                'old' => $data,
                'csrf_token' => $this->generateCsrfToken()
            ]);
        }

         $row = $this->userModel->UpdateUser($data);

        if ($row) {
            Session::set('success', 'User data updated sucessfully');
            Session::setUserUpdates($data["email"], $data["name"]);
            $this->redirect("/users/profile");
        } else {
            echo $this->render('users/editProfile.twig', [
                'errors' => ['general' => 'Registration Failed'],
                'old' => $data,
                'csrf_token' => $this->generateCsrfToken()
            ]);
        }
    }

    public function deleteUser(){
          $row = $this->userModel->deleteUser();
        if ($row) {
            Session::set('success', 'User removed sucessfully');
            Session::logout();
            $this->redirect("/register");
        } else {
            echo $this->render('users/profile.twig', [
                'errors' => ['general' => 'Delete Failed'],
                'csrf_token' => $this->generateCsrfToken()
            ]);
        }
    }
}