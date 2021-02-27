<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\BMAASDBController;
use App\Mail\ILOMailController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
class ILOController extends Controller
{

	public function RemoveILOUser($ipmiaddr,$ipmiuserid){
                $curl = curl_init();

                curl_setopt_array($curl, array(
                        CURLOPT_URL => "https://$ipmiaddr/redfish/v1/AccountService/Accounts/$ipmiuserid",
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_ENCODING => "",
                        CURLOPT_MAXREDIRS => 10,
                        CURLOPT_TIMEOUT => 30,
                        CURLOPT_SSL_VERIFYHOST => 0,
                        CURLOPT_SSL_VERIFYPEER => 0,
                        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                        CURLOPT_CUSTOMREQUEST => "DELETE",
                        CURLOPT_HTTPHEADER => array(
                                "Authorization: Basic YWRtaW5pc3RyYXRvcjphZG1pbmlzdHJhdG9y",
                                "Content-Type: application/json"
                        ),
                ));

                $response = curl_exec($curl);
                $err = curl_error($curl);

                curl_close($curl);

                if ($err) {
                        echo "cURL Error #:" . $err;
		} else {

		}
	}
	public function CreateILOUser($ipmiaddr,$hostname){
		$user = Auth::User();
		$BMAASDBController = new BMAASDBController();
		//echo $user->email;
		//exit;
		$ContactController = new ContactController();
		$username = $this->password_generate(6);
		$password = $this->password_generate(12);
		$userjson='{
		"UserName": "'.$username.'",
    		"Password": "'.$password.'",
    		"Oem": {
        		"Hpe": {
            			"LoginName": "'.$username.'",
            			"Privileges": {
                			"HostBIOSConfigPriv": false,
                			"HostNICConfigPriv": false,
                			"HostStorageConfigPriv": false,
                			"LoginPriv": true,
                			"RemoteConsolePriv": true,
                			"SystemRecoveryConfigPriv": false,
                			"UserConfigPriv": false,
                			"VirtualMediaPriv":false,
                			"VirtualPowerAndResetPriv": false,
                			"iLOConfigPriv": false
            			}
        		}
    		}
		}';
                $curl = curl_init();

                curl_setopt_array($curl, array(
                        CURLOPT_URL => "https://$ipmiaddr/redfish/v1/AccountService/Accounts/",
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_ENCODING => "",
                        CURLOPT_MAXREDIRS => 10,
                        CURLOPT_TIMEOUT => 30,
                        CURLOPT_SSL_VERIFYHOST => 0,
                        CURLOPT_SSL_VERIFYPEER => 0,
                        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                        CURLOPT_CUSTOMREQUEST => "POST",
                        CURLOPT_POSTFIELDS => $userjson,
                        CURLOPT_HTTPHEADER => array(
                                "Authorization: Basic YWRtaW5pc3RyYXRvcjphZG1pbmlzdHJhdG9y",
                                "Content-Type: application/json"
                        ),
                ));

                $response = curl_exec($curl);
                $err = curl_error($curl);

                curl_close($curl);

                if ($err) {
                        echo "cURL Error #:" . $err;
		} else {
			$val = json_decode($response);
			$ilo_user_id = $val->Id;
			$ipmiparam = array($ipmiaddr,$ilo_user_id);
			$BMAASDBController->AddIPMIUsers($ipmiparam);
			$postparam = array($username,$password,$hostname);
			Mail::to($user->email)->send(new ILOMailController($postparam));
                        //$ContactController->ProvisioningILOPost($postparam);

		}

	}
	public function password_generate($chars) 
	{
  		$data = '1234567890ABCDEFGHIJKLMNOPQRSTUVWXYZabcefghijklmnopqrstuvwxyz';
  		return substr(str_shuffle($data), 0, $chars);
	}	
}
