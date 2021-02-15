<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\BMAASDBController;
class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = 'home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }
    protected function credentials(Request $request)
    {
    	return [
            'uid' => $request->get('username'),
	    'password' => $request->get('password'),
	   
    	];
	//echo $request->get('username');
	/*if (Auth::attempt($credentials)) {
	    $user = Auth::user();

	    return redirect('/dashboard')->with([
        	'message' => "Welcome back, {$user->name}"
	]);
    }*/

    }
    protected function authenticated(Request $request, $user)
    {	
	//echo $user;
	    //exit;
	$BMAASDBController = new BMAASDBController();
	$usercheck = $BMAASDBController->CheckUserSubscribe($user);
	//print_r($usercheck);
	//exit;
	if (empty($usercheck->tenant_id)){
		return redirect("/subscribe")->with('InfoSubscribtion', 'User '.$user->name. ' is not Not Subscribed');
	}else{
		return redirect("/listmachines");
	}
	    //return redirect("/listmachines");
	//return redirect()->action('RackNController@GetListMachines')->with('errorMessageDuration', 'Kubernetes Cluster Deployment in Progress');
    }    
    public function username()
    {
        return 'username';
    }
}
