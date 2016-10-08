<?php

namespace App\Controllers;

use Neutron\View;
use Neutron\Database as DB;

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
