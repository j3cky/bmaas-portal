<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Mail\VPNMailController;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;



class VPNController extends Controller
{
	public function CreateGroup($groupname){
		//echo shell_exec ("ssh 172.16.10.59 '/usr/local/vpnserver/vpncmd localhost /SERVER /ADMINHUB:VPN-GioPrivate /PASSWORD:4dy0 /CMD:GroupCreate Test4 /REALNAME:Test /NOTE:none'");
		$connection = ssh2_connect('172.16.10.59', 22);
		ssh2_auth_password($connection, 'root', 'q{9uH#(#m;C6whk2');
		//$auth_methods = ssh2_auth_none($connection, 'root');

		$stream = ssh2_exec($connection, "/usr/local/vpnserver/vpncmd localhost /SERVER /ADMINHUB:VPN-GioPrivate /PASSWORD:4dy0@pmR /CMD:GroupCreate $groupname /REALNAME:$groupname /NOTE:none");
	}
	public function CreateUser($username,$password,$groupname,$user){
		//$user = Auth::User();
                $connection = ssh2_connect('172.16.10.59', 22);
                ssh2_auth_password($connection, 'root', 'q{9uH#(#m;C6whk2');

		$stream = ssh2_exec($connection, "/usr/local/vpnserver/vpncmd localhost /SERVER /ADMINHUB:VPN-GioPrivate /PASSWORD:4dy0@pmR /CMD:UserCreate $username /GROUP:$groupname /REALNAME:$username /NOTE:none");
		$stream = ssh2_exec($connection, "/usr/local/vpnserver/vpncmd localhost /SERVER /ADMINHUB:VPN-GioPrivate /PASSWORD:4dy0@pmR /CMD:UserPasswordSet $username /PASSWORD:$password");
		$postparam = array($username,$password);
                Mail::to($user->email)->send(new VPNMailController($postparam));


	}
	
	public function CreateACL($groupname,$username,$destip){
                $connection = ssh2_connect('172.16.10.59', 22);
                ssh2_auth_password($connection, 'root', 'q{9uH#(#m;C6whk2');

                $stream = ssh2_exec($connection, "/usr/local/vpnserver/vpncmd localhost /SERVER /ADMINHUB:VPN-GioPrivate /PASSWORD:4dy0@pmR /CMD:AccessAdd pass /MEMO:$groupname /PRIORITY:1000 /SRCUSERNAME:$username /SRCIP:0.0.0.0/0 /DESTIP:$destip  /DESTUSERNAME /SRCMAC /DESTMAC /PROTOCOL:ip /SRCPORT /DESTPORT /TCPSTATE");
	}

}
