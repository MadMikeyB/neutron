<?php

namespace app\Controllers;

use Neutron\View;
use App\Models\User;

class HomeController
{
    public function index()
    {
        $users = User::all();

        return View::get('home', ['users' => $users]);
    }

    public function example()
    {
        echo '<h1>Example</h1>';
    }
}
