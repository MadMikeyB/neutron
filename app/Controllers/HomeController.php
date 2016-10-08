<?php
namespace App\Controllers;

use Neutron\View;

class HomeController {

	public function index()
	{
		$time = new \DateTime('now');
		return View::get('home', ['time' => $time->format('H:i:s')]);
	}

	public function example()
	{
		echo "<h1>Example</h1>";
	}

}