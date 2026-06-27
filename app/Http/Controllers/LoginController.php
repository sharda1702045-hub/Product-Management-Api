<?php

namespace App\Http\Controllers;

class LoginController extends Controller
{
    /**
     * Show the application login form.
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }
}
