<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UserController extends Controller
{
    public function test(Request $request)
    {
        return "Action from User Controller";
    }

    public function register(Request $request)
    {
        return "Action to register User";
    }

    public function login(Request $request)
    {
        return "Action to log in User";
    }
}
