<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('{tenantval}/ordergioprivate', function () {
    return view('ordergioprivate');
});
Route::get('{tenantval}/orderbaremetal', function () {
    return view('orderbaremetal');
});
//Route::get('orderpage/{tenantval}', function () {
 //   return view('order');
//});
Route::get('{tenantval}/order2', function () {
    return view('order2');
});
//Route::get('/sshkey/{tenantval}', function () {
    //return view('sshkey');
//});

//Route::get('/subscribe', function () {
//    return view('subscribe');
//});
Route::get('/subscribe', 'RackNController@SubscribeView')->middleware('auth');
Route::get('/welcome', function () {
    return view('welcome');
});
Route::get('/about-us', function () {
    return view('aboutus');
});

Route::get('/support', function () {
    return view('support');
});
Route::get('/ilo', 'ILOController@CreateILOUser')->middleware('auth');
Route::get('/contact', 'ContactController@contact')->middleware('auth');
Route::post('/contact', 'ContactController@contactPost')->name('contactPost')->middleware('auth');
Route::get('/halo', function () {
	return "Halo, Selamat datang di tutorial laravel www.malasngoding.com";
});

Route::get('/irctest', 'RackNController@TestIRC')->middleware('auth');
//Route::get('/irctest', function () {
//    return view('testirc');
//});
Route::get('/testvpn/', 'RackNController@CreateRackNMachine');
Route::get('/testcluster/', 'RackNController@TestClusterProfile')->middleware('auth');
Route::get('/testvc/', 'RackNController@DeleteVC')->middleware('auth');
Route::get('/listmachines/', 'RackNController@GetListMachines')->middleware('auth');
Route::get('/listmachineskub/', 'RackNController@ListKubMachines')->middleware('auth');
Route::get('/listmachineswin/', 'RackNController@ListWinMachines')->middleware('auth');
Route::get('/listmachineslin/', 'RackNController@ListLinMachines')->middleware('auth');
Route::post('/orderpage/ordergioprivate/process', 'RackNController@ProcessGioPrivateJob')->middleware('auth');
Route::post('/listmachines/action/redeploy', 'RackNController@RedeployMachine')->middleware('auth');
Route::post('/listmachines/action/unsubbare', 'RackNController@UnsubscribeBareMetal')->middleware('auth');
Route::post('/listmachines/action/unsubpriv', 'RackNController@UnsubGioPrivate')->middleware('auth');
Route::post('/listmachines/action/unsubkub', 'RackNController@UnsubscribeKubCluster')->middleware('auth');
//Route::post('/{tenantval}/orderpage/orderbaremetal/process', 'RackNController@ProcessBareMetalOrder')->middleware('auth');
Route::post('/orderpage/orderbaremetal/process', 'RackNController@ProcessBareMetalJob')->middleware('auth');
Route::post('/orderpage/windows/process', 'RackNController@ProcessWindowsJob')->middleware('auth');
Route::post('/orderpage/kubernetes/process', 'RackNController@ProcessKubernetesJob')->middleware('auth');
Route::post('/createtenant', 'BMAASDBController@CreateTenantBMAAS')->middleware('auth');
Route::post('/sshkey/create', 'RackNController@SSHKeyCreate')->middleware('auth');
Route::post('/sshkey/delete', 'RackNController@SSHKeyDelete')->middleware('auth');
Route::get('/sshkey', 'RackNController@ListSSHKey')->middleware('auth');
Route::get('{tenantval}/bcf', 'BCFController@Execute')->middleware('auth');
Route::get('/listtenant', 'BMAASDBController@GetTenantList')->middleware('auth');
Route::get('/orderpage', 'RackNController@OrderPage')->middleware('auth');
Route::post('/subscribeservice', 'RackNController@SubscribeUser')->middleware('auth');
Route::post('/listmachines/action/unsubkubserver', 'RackNController@DeleteKubernetesNode')->middleware('auth');
//Route::get('ordergioprivate', (){
//	 return view('welcome');
//});
Route::post('/testqueue/post', 'TestQueue@testqueue');
Route::get('/testqueue', function () {
    return view('testqueue');
});
Route::get('login', 'Auth\LoginController@showLoginForm')->name('login');

//Auth::routes();
Auth::routes([
    'login'    => true, 
    'logout'   => true, 
    'register' => false, 
    'reset'    => false,   // for resetting passwords
    'confirm'  => false,  // for additional password confirmations
    'verify'   => false,  // for email verification
]);


//------ Billing --------//
Route::resource('billing', 'BillingController');

//----- Activity ---------//
Route::resource('activity', 'ActivityController');


Route::get('/home', 'HomeController@index')->name('home');

// Route::get('/activity', 'RackNController@GetActivityJobs')->name('jobstat');

Route::get('/', 'RackNController@GetListMachines')->middleware('auth');
