<?php

namespace App\Controllers;

use Neutron\View;
use Neutron\Database as DB;

use App\Models\User;

class HomeController
{
    public function index()
    {
        $time = new \DateTime('now');
		$users = User::all();

		print_r($users); exit;
        return View::get('home', ['time' => $time->format('H:i:s'), 'users' => $users]);
    }

    public function example()
    {
        echo '<h1>Example</h1>';
    }
}
