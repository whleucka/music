<?php

namespace App\Http\Controllers\Auth;

use App\Providers\Auth\RegisterService;
use Echo\Framework\Http\Controller;
use Echo\Framework\Routing\Route\{Get, Post};
use Echo\Framework\Session\Flash;

class RegisterController extends Controller
{
    public function __construct(private RegisterService $provider)
    {
    }

    #[Get("/register", "auth.register.index")]
    public function index(): string
    {
        return $this->render("auth/register/index.html.twig");
    }

    #[Post("/register", "auth.register.post", ["max_requests" => 20])]
    public function post()
    {
        $this->setValidationMessage("password.min_length", "Must be at least 10 characters");
        $this->setValidationMessage("password.regex", "Must contain 1 upper case, 1 digit, 1 symbol");
        $this->setValidationMessage("password_match.match", "Password does not match");
        $valid = $this->validate([
            "first_name" => ["required"],
            "surname" => ["required"],
            "email" => ["required", "email"],
            "password" => ["required", "min_length:10", "regex:^(?=.*[A-Z])(?=.*\W)(?=.*\d).+$"],
            "password_match" => ["required", "match:password"],
        ]);
        if ($valid) {
            $success = $this->provider->register($valid->first_name, $valid->surname, $valid->email, $valid->password);
            if ($success) {
                redirect("/dashboard");
            } else {
                Flash::add("warning", "Failed to register new account");
            }
        }
        return $this->index();
    }
}
