<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Jobs\DeployKubernetesCluster;
use App\Http\Controllers\BMAASDBController;

class TestQueue extends Controller
{	
	public function testqueue(Request $request){
  		$objectvalue = (object) array(
                	'name' => $request->username,
                	'pass' => $request->password
            	);
		           //$BMAASDBController = new BMAASDBController;
            //$BMAASDBController->BMAASQueue($objectvalue);
		dispatch(new DeployKubernetesCluster($objectvalue));

	}
}
