<?php

declare(strict_types=1);

namespace Manifesto\Controllers;

use Manifesto\Core\Auth;
use Manifesto\Core\Request;
use Manifesto\Core\Response;
use Manifesto\Core\Session;

final class AuthController
{
    public function showLogin(Request $request): void
    {
        if (Auth::check()) {
            Response::redirect('/');
        }
        Response::view('auth/login', [], 'auth');
    }

    public function login(Request $request): void
    {
        $username = $request->input('username', '');
        $password = $request->input('password', '');

        if ($username === '' || $password === '' || !Auth::attempt($username, $password)) {
            Session::flash('error', 'Invalid username or password.');
            Session::flashOldInput(['username' => $username]);
            Response::redirect('/login');
        }

        Session::flash('success', 'Welcome back, ' . (Auth::user()['display_name'] ?? $username) . '!');
        Response::redirect('/');
    }

    public function logout(Request $request): void
    {
        Auth::logout();
        Response::redirect('/login');
    }
}
