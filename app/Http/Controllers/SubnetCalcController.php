<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TestDBController extends Controller
{
    public function index()
    {
	    //$users = DB::table('tenants')->get();

 	$sub = new IPv4\SubnetCalculator('192.168.112.203', 23);
    }
}
