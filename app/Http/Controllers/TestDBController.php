<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\SubnetCalculatorController;

class TestDBController extends Controller
{
    public function index()
    {
	    //$users = DB::table('tenants')->get();
	    $sub = new SubnetCalculatorController('103.93.128.195', 28);
	    $address_range          = $sub->getIPAddressRange();
	    $network = $sub->getNetworkPortion();
	    $gw = $sub->getMaxHost();
	    print_r ($address_range);
	    echo $gw;
 	//$sub = new IPv4\SubnetCalculator('192.168.112.203', 23);
    }
}
