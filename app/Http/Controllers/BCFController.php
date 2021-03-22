<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Http\Controllers\NetBoxController;
use App\Http\Controllers\BMAASDBController;
use Illuminate\Support\Facades\Auth;

class BCFController extends Controller
{
	public function ExecuteBaremetal(){//To prepare network om BCF invironment
                $NetBoxController = new NetBoxController;
                $ipexplode=explode(".",$privateip);
                $strcount=strlen($ipexplode[3]);
                $privategw=substr_replace($privateip,"254",-$strcount);
		$tenant = request()->segment(1);		
		$tenantstatus= $this->CreateTenant();
		if($tenantstatus == 204 or $tenantstatus == 100){
			$systemtenantint = $this->SystemTenantInterface();
		}
		if($systemtenantint = 204 or $systemtenantint == 100){
			$segment = $this->CreateSegment($segmentname);
		}
		if($segment == 204 or $segment == 100){
                      	$this->AddInterfaceGroup($vlan);
                        $segmentint = $this->CreateSegmentInterface();			
		}
		if($segmentint == 204 or $segmentint == 100){
			$segmentintip = $this->CreateSegmentInterfaceIP($privategw);
			$this->ConfigureStaticRoute($privateip);
		}

		$NetBoxController->CreateNBTenant();		
	}
	public function Execute($privateip,$vlan){
		//$privateip="172.16.12.0";
                $user = Auth::User();
                $BMAASDBController = new BMAASDBController;
                $tenantget = $BMAASDBController->GetTenantbyUser($user->id);
                $tenant =  $tenantget->tenant_name;

		$NetBoxController = new NetBoxController;
		$NetBoxController->CreateNBTenant();
                $ipexplode=explode(".",$privateip);
                $strcount=strlen($ipexplode[3]);
                $privategw=substr_replace($privateip,"254",-$strcount);

		$tenantstatus = $this->CreateTenant();
		if($tenantstatus == 204 or $tenantstatus == 100){
			$systemtenantint = $this->SystemTenantInterface();
			if($systemtenantint = 204 or $systemtenantint == 100){
				$segment = $this->CreateSegment($tenant."-kub");
				if($segment == 204 or $segment == 100){
					$this->AddInterfaceGroup($tenant."-kub",$vlan,"CVC001");
					$segmentint = $this->CreateSegmentInterface();
					if($segmentint == 204 or $segmentint == 100){
						$segmentintip = $this->CreateSegmentInterfaceIP($privategw);
						//echo "If IP";
						//exit;
						$this->ConfigureStaticRoute($privateip);
						//echo "Static Route";
						//exit;
						if($segmentintip == 204){
							echo "Success Configure BCF Network";
						}else{
							echo "fail Create Segment Interface IP";
						}
					}else{
						echo "Fail Create Segment Interface";
					}
				}else{
					echo "Fail Create Subnet";
				}
			}else{
				echo "Fail Enable System Tenant Interface";
			}
		}else{
			echo "Fail"; 
		}
	}
	public function DeleteInterfaceGroup($segmentname,$ifgroup,$user){
                $BMAASDBController = new BMAASDBController;
                $tenantget = $BMAASDBController->GetTenantbyUser($user->id);
		$tenant =  $tenantget->tenant_name;
		$cookie = $this->Login();
		$curl = curl_init();

                curl_setopt_array($curl, array(
                        CURLOPT_URL => "https://172.16.0.2:8443/api/v1/data/controller/applications/bcf/tenant%5Bname%3D%22$tenant%22%5D/segment%5Bname%3D%22$segmentname%22%5D/interface-group-membership-rule%5Binterface-group%3D%22$ifgroup%22%5D",
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_ENCODING => "",
                        CURLOPT_HEADER => true,
                        CURLOPT_MAXREDIRS => 10,
                        CURLOPT_SSL_VERIFYPEER => 0,
                        CURLOPT_SSL_VERIFYHOST => 0,
                        CURLOPT_TIMEOUT => 30,
                        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                        CURLOPT_CUSTOMREQUEST => "DELETE",
                        CURLOPT_HTTPHEADER => array(
                                "Content-Type: application/json",
                                "Cookie: session_cookie=$cookie",
                                ),
                ));

		$response = curl_exec($curl);
		$err = curl_error($curl);

		curl_close($curl);

		if ($err) {
  			echo "cURL Error #:" . $err;
		} else {
  			//echo $response;
		}
	}
	public function AddInterfaceGroup($segmentname,$vlan,$ifgroup,$user){
		//$tenant = request()->segment(1);
                //$user = Auth::User();
                $BMAASDBController = new BMAASDBController;
                $tenantget = $BMAASDBController->GetTenantbyUser($user->id);
                $tenant =  $tenantget->tenant_name;
		
                $cookie = $this->Login();
                $curl = curl_init();
		$jsonparam = '{
                		"interface-group": "'.$ifgroup.'",
                		"virtual": false,
                		"vlan": '.$vlan.'
			     }'; 
           	curl_setopt_array($curl, array(
                	CURLOPT_URL => "https://172.16.0.2:8443/api/v1/data/controller/applications/bcf/tenant%5Bname%3D%22$tenant%22%5D/segment%5Bname%3D%22$segmentname%22%5D/interface-group-membership-rule",
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_ENCODING => "",
                        CURLOPT_HEADER => true,
                        CURLOPT_MAXREDIRS => 10,
                        CURLOPT_SSL_VERIFYPEER => 0,
                        CURLOPT_SSL_VERIFYHOST => 0,
                        CURLOPT_TIMEOUT => 30,
                        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                        CURLOPT_CUSTOMREQUEST => "POST",
                        CURLOPT_POSTFIELDS => $jsonparam,
                        CURLOPT_HTTPHEADER => array(
                                "Content-Type: application/json",
                                "Cookie: session_cookie=$cookie",
                                ),
           	));

                $response = curl_exec($curl);
                $err = curl_error($curl);
                $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
                curl_close($curl);
		//echo $response;
		//exit;
                if ($err) {
                  	echo "cURL Error #:" . $err;
		} else {
			echo $response;
                     	return $httpcode;
             	}
	}

	public Function SystemTenantInterface($user){
		//$user = Auth::User();
                $BMAASDBController = new BMAASDBController;
		$tenantget = $BMAASDBController->GetTenantbyUser($user->id);
		$tenant =  $tenantget->tenant_name;
                $cookie = $this->Login();
                $curl = curl_init();
		$exist = $this->GetTenant($user);
		$tenantif[0] = "system";
		$tenantif[1] = $tenant;
		$jsonparam[0] = '{"import-route" : false,"remote-tenant" : "'.$tenant.'","shutdown" : false}';
		$jsonparam[1] = '{"import-route" : false,"remote-tenant" : "system","shutdown" : false}';
                if($exist == 0){
                        echo "Tenant not exist";
		}elseif($exist == 1 ){
			for($i=0;$i<2;$i++){
                        curl_setopt_array($curl, array(
                        CURLOPT_URL => "https://172.16.0.2:8443/api/v1/data/controller/applications/bcf/tenant%5Bname%3D%22".$tenantif[$i]."%22%5D/logical-router/tenant-interface",
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_ENCODING => "",
                        CURLOPT_MAXREDIRS => 10,
                        CURLOPT_SSL_VERIFYPEER => 0,
                        CURLOPT_SSL_VERIFYHOST => 0,
                        CURLOPT_TIMEOUT => 30,
                        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                        CURLOPT_CUSTOMREQUEST => "POST",
                        CURLOPT_POSTFIELDS => $jsonparam[$i],
                        CURLOPT_HTTPHEADER => array(
                                "Content-Type: application/json",
                                "Cookie: session_cookie=$cookie",
                                ),
                        ));

                        $response = curl_exec($curl);
                        $err = curl_error($curl);
			$httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
			}
                        curl_close($curl);

                        if ($err) {
                                echo "cURL Error #:" . $err;
			} else {
				echo $response;
                                return $httpcode;
                        }
                }
	}	
	public function ConfigureStaticRoute($privateip,$user){
                //$user = Auth::User();
                $BMAASDBController = new BMAASDBController;
                $tenantget = $BMAASDBController->GetTenantbyUser($user->id);
                $tenant =  $tenantget->tenant_name;
                
                $cookie = $this->Login();
		$curl = curl_init();
                $tenantif[0] = $tenant;
		$tenantif[1] = "external";
		$tenantif[2] = "system";
		$jsonparam[0]='{"dst-ip-subnet" : "0.0.0.0/0","next-hop" : {"tenant" : "system"},"preference" : 1}';
		$jsonparam[1]='{"dst-ip-subnet" : "'.$privateip.'","next-hop" : {"tenant" : "system"},"preference" : 1}';
		$jsonparam[2]='{"dst-ip-subnet" : "'.$privateip.'","next-hop" : {"tenant" : "'.$tenant.'"},"preference" : 1}';
		for($i=0;$i<3;$i++){
                	curl_setopt_array($curl, array(
                        CURLOPT_URL => "https://172.16.0.2:8443/api/v1/data/controller/applications/bcf/tenant%5Bname%3D%22".$tenantif[$i]."%22%5D/logical-router/static-route",
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_ENCODING => "",
                        CURLOPT_HEADER => true,
                        CURLOPT_MAXREDIRS => 10,
                        CURLOPT_SSL_VERIFYPEER => 0,
                        CURLOPT_SSL_VERIFYHOST => 0,
                        CURLOPT_TIMEOUT => 30,
                        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                        CURLOPT_CUSTOMREQUEST => "POST",
                        CURLOPT_POSTFIELDS => $jsonparam[$i],
                        CURLOPT_HTTPHEADER => array(
                                "Content-Type: application/json",
                                "Cookie: session_cookie=$cookie",
                                ),
             		));

                	$response = curl_exec($curl);
                	$err = curl_error($curl);
			$httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
			
		}
                curl_close($curl);
		
                if ($err) {
                    	echo "cURL Error #:" . $err;
              	} else {
                       	return $httpcode;
		}
		
	}
	public function GetSegmentInterface($user){
                //$user = Auth::User();
                $BMAASDBController = new BMAASDBController;
                $tenantget = $BMAASDBController->GetTenantbyUser($user->id);
                $tenant =  $tenantget->tenant_name;
                
                $cookie = $this->Login();
                $curl = curl_init();

                curl_setopt_array($curl, array(
                        #CURLOPT_PORT => "8443",
                        CURLOPT_URL => "https://172.16.0.2:8443/api/v1/data/controller/applications/bcf/tenant%5Bname%3D%22$tenant%22%5D/logical-router/segment-interface%5Bsegment%3D%22$tenant-kub%22%5D",
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_ENCODING => "",
                        CURLOPT_MAXREDIRS => 10,
                        CURLOPT_SSL_VERIFYPEER => 0,
                        CURLOPT_SSL_VERIFYHOST => 0,
                        CURLOPT_TIMEOUT => 30,
                        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                        CURLOPT_CUSTOMREQUEST => "GET",
                        CURLOPT_HTTPHEADER => array(
                                "Content-Type: application/json",
                                "Cookie: session_cookie=$cookie",
                        ),
                ));

                $response = curl_exec($curl);
                $err = curl_error($curl);

                curl_close($curl);

                if ($err) {
                        echo "cURL Error #:" . $err;
                } else {
                        //echo $response;
                        $val = json_decode($response);
                        return count($val);
                }
	}
	public function CreateSegmentInterface($segmentname,$user){
                //$user = Auth::User();
                $BMAASDBController = new BMAASDBController;
                $tenantget = $BMAASDBController->GetTenantbyUser($user->id);
                $tenant =  $tenantget->tenant_name;
                
                $cookie = $this->Login();
                $curl = curl_init();
                $exist = $this->GetSegmentInterface($user);
                if($exist == 1){
                        return 100;
                }elseif($exist == 0 ){
                        curl_setopt_array($curl, array(
                        CURLOPT_URL => "https://172.16.0.2:8443/api/v1/data/controller/applications/bcf/tenant%5Bname%3D%22$tenant%22%5D/logical-router/segment-interface%5Bsegment%3D%22$segmentname%22%5D",
                        CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => "",
			CURLOPT_HEADER => true,
                        CURLOPT_MAXREDIRS => 10,
                        CURLOPT_SSL_VERIFYPEER => 0,
                        CURLOPT_SSL_VERIFYHOST => 0,
                        CURLOPT_TIMEOUT => 30,
                        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                        CURLOPT_CUSTOMREQUEST => "POST",
                        CURLOPT_POSTFIELDS => '{"segment" : "'.$segmentname.'"}',
                        CURLOPT_HTTPHEADER => array(
                                "Content-Type: application/json",
                                "Cookie: session_cookie=$cookie",
                                ),
                        ));

                        $response = curl_exec($curl);
                        $err = curl_error($curl);
			$httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
                        curl_close($curl);

                        if ($err) {
                                echo "cURL Error #:" . $err;
			} else {
				echo $response;
				return $httpcode;
				echo "HTTPCODE : $httpcode";
				//exit;
			}

                }
	}
	
	public function ChangeInterfaceGroupMode($ifgroup,$mode){
		$cookie = $this->Login();
		$curl = curl_init();
		$ifgroupget = $this->GetInterfaceGroupMode($ifgroup);
		if($mode == "static"){
			$ifgroupmode = str_replace("lacp-fallback-individual","static",$ifgroupget);
		}else{
			$ifgroupmode = str_replace("static","lacp-fallback-individual",$ifgroupget);
		}
		//echo $ifgroupmode
		curl_setopt_array($curl, array(
		  CURLOPT_PORT => "8443",
		  CURLOPT_URL => "https://172.16.0.2:8443/api/v1/data/controller/applications/bcf/interface-group%5Bname%3D%22$ifgroup%22%5D",
		  CURLOPT_RETURNTRANSFER => true,
		  CURLOPT_ENCODING => "",
		  CURLOPT_MAXREDIRS => 10,
		  CURLOPT_TIMEOUT => 30,
                  CURLOPT_SSL_VERIFYPEER => 0,
                  CURLOPT_SSL_VERIFYHOST => 0,
		  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		  CURLOPT_CUSTOMREQUEST => "PUT",
		  CURLOPT_POSTFIELDS => $ifgroupmode,
		  CURLOPT_HTTPHEADER => array(
		    "Content-Type: application/json",
		    "Cookie: session_cookie=$cookie",

		  ),
		));

		$response = curl_exec($curl);
		$err = curl_error($curl);

		curl_close($curl);

		if ($err) {
		  echo "cURL Error #:" . $err;
		} else {
		  //echo $response;
		}
	}

	public function GetInterfaceGroupMode($ifgroup){
                $cookie = $this->Login();
                $curl = curl_init();

                curl_setopt_array($curl, array(
                  CURLOPT_PORT => "8443",
                  CURLOPT_URL => "https://172.16.0.2:8443/api/v1/data/controller/applications/bcf/interface-group%5Bname%3D%22$ifgroup%22%5D",
                  CURLOPT_RETURNTRANSFER => true,
                  CURLOPT_ENCODING => "",
                  CURLOPT_MAXREDIRS => 10,
                  CURLOPT_TIMEOUT => 30,
                  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		  CURLOPT_CUSTOMREQUEST => "GET",
                  CURLOPT_SSL_VERIFYPEER => 0,
                  CURLOPT_SSL_VERIFYHOST => 0,		  
                  CURLOPT_POSTFIELDS => "",
                  CURLOPT_HTTPHEADER => array(
                    "Content-Type: application/json",
                    "Cookie: session_cookie=$cookie",

                  ),
                ));

                $response = curl_exec($curl);
                $err = curl_error($curl);

                curl_close($curl);

                if ($err) {
                  echo "cURL Error #:" . $err;
                } else {
                  return $response;
                }

	}

	public function CreateSegmentInterfaceIP($cidr,$segmentname,$user){
                //$user = Auth::User();
                $BMAASDBController = new BMAASDBController;
                $tenantget = $BMAASDBController->GetTenantbyUser($user->id);
                $tenant =  $tenantget->tenant_name;
                
		$cookie = $this->Login();
		$exist = $this->GetSegmentInterfaceIP($cidr,$segmentname,$user);
                if($exist == 1){
                        return 100;
                }elseif($exist == 0 ){
                	$curl = curl_init();
                	curl_setopt_array($curl, array(
                        CURLOPT_URL => "https://172.16.0.2:8443/api/v1/data/controller/applications/bcf/tenant%5Bname%3D%22$tenant%22%5D/logical-router/segment-interface%5Bsegment%3D%22$segmentname%22%5D/ip-subnet%5Bip-cidr%3D%22$cidr/24%22%5D",
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_ENCODING => "",
                        CURLOPT_MAXREDIRS => 10,
                        CURLOPT_SSL_VERIFYPEER => 0,
                        CURLOPT_SSL_VERIFYHOST => 0,
                        CURLOPT_TIMEOUT => 30,
                        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                        CURLOPT_CUSTOMREQUEST => "POST",
                        CURLOPT_POSTFIELDS => '{"ip-cidr": "'.$cidr.'/24"}',
                        CURLOPT_HTTPHEADER => array(
                                "Content-Type: application/json",
                                "Cookie: session_cookie=$cookie",
                                ),
                	));

              		$response = curl_exec($curl);
                	$err = curl_error($curl);
			$httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
               		curl_close($curl);
			//echo $response;
                	if ($err) {
                   		echo "cURL Error #:" . $err;
                	} else {
                     		return $httpcode;
			}
		}
	}
	public function GetSegmentInterfaceIP($cidr,$segmentname,$user){
                //$user = Auth::User();
                $BMAASDBController = new BMAASDBController;
                $tenantget = $BMAASDBController->GetTenantbyUser($user->id);
                $tenant =  $tenantget->tenant_name;
		
		$cookie = $this->Login();
		$curl = curl_init();
                curl_setopt_array($curl, array(
                        CURLOPT_URL => "https://172.16.0.2:8443/api/v1/data/controller/applications/bcf/tenant%5Bname%3D%22$tenant%22%5D/logical-router/segment-interface%5Bsegment%3D%22$segmentname%22%5D/ip-subnet%5Bip-cidr%3D%22$cidr/24%22%5D",
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_ENCODING => "",
                        CURLOPT_MAXREDIRS => 10,
                        CURLOPT_SSL_VERIFYPEER => 0,
                        CURLOPT_SSL_VERIFYHOST => 0,
                        CURLOPT_TIMEOUT => 30,
                        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                        CURLOPT_CUSTOMREQUEST => "GET",
                        CURLOPT_POSTFIELDS => '{"ip-cidr": "'.$cidr.'/24"}',
                        CURLOPT_HTTPHEADER => array(
                                "Content-Type: application/json",
                                "Cookie: session_cookie=$cookie",
                                ),
                ));

                $response = curl_exec($curl);
                $err = curl_error($curl);
                $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
                curl_close($curl);

                if ($err) {
                        echo "cURL Error #:" . $err;
		} else {
			$val = json_decode($response);
			return count($val);
                }

	}
	public function GetTenant($user){
                //$user = Auth::User();
                $BMAASDBController = new BMAASDBController;
                $tenantget = $BMAASDBController->GetTenantbyUser($user->id);
                $tenant =  $tenantget->tenant_name;
                
                $cookie = $this->Login();
                $curl = curl_init();

                curl_setopt_array($curl, array(
                        #CURLOPT_PORT => "8443",
                        CURLOPT_URL => "https://172.16.0.2:8443/api/v1/data/controller/applications/bcf/tenant%5Bname%3D%22$tenant%22%5D",
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_ENCODING => "",
                        CURLOPT_MAXREDIRS => 10,
                        CURLOPT_SSL_VERIFYPEER => 0,
                        CURLOPT_SSL_VERIFYHOST => 0,
                        CURLOPT_TIMEOUT => 30,
                        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                        CURLOPT_CUSTOMREQUEST => "GET",
                        CURLOPT_HTTPHEADER => array(
                                "Content-Type: application/json",
                                "Cookie: session_cookie=$cookie",
                        ),
                ));

                $response = curl_exec($curl);
                $err = curl_error($curl);

                curl_close($curl);

                if ($err) {
                        echo "cURL Error #:" . $err;
                } else {
			//echo $response;
			$val = json_decode($response);
			return count($val);
                }		
	}
	public function CreateTenant($tenant){
		//$tenant = request()->segment(2);
		$cookie = $this->Login();
		$curl = curl_init();
		//$exist = $this->GetTenant($user);
		//if($exist == 1){
		//	return 100;
		//}elseif($exist == 0 ){
			curl_setopt_array($curl, array(
  			CURLOPT_PORT => "8443",
  			CURLOPT_URL => "https://172.16.0.2:8443/api/v1/data/controller/applications/bcf/tenant",
  			CURLOPT_RETURNTRANSFER => true,
  			CURLOPT_ENCODING => "",
			CURLOPT_MAXREDIRS => 10,
                        CURLOPT_SSL_VERIFYPEER => 0,
                        CURLOPT_SSL_VERIFYHOST => 0,			
  			CURLOPT_TIMEOUT => 30,
  			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  			CURLOPT_CUSTOMREQUEST => "POST",
  			CURLOPT_POSTFIELDS => '{"name": "'.$tenant.'"}',
  			CURLOPT_HTTPHEADER => array(
    				"Content-Type: application/json",
    				"Cookie: session_cookie=$cookie",
  				),
			));

			$response = curl_exec($curl);
			$err = curl_error($curl);
			$httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
			curl_close($curl);

			if ($err) {
  				echo "cURL Error #:" . $err;
			} else {
  				return $httpcode;
			}		
		//}
	}
	public function GetSubnet($user){
                //$user = Auth::User();
                $BMAASDBController = new BMAASDBController;
                $tenantget = $BMAASDBController->GetTenantbyUser($user->id);
                $tenant =  $tenantget->tenant_name;
                
                $cookie = $this->Login();
                $curl = curl_init();
		$segment = "$tenant-kub";
                curl_setopt_array($curl, array(
                        #CURLOPT_PORT => "8443",
                        CURLOPT_URL => "https://172.16.0.2:8443/api/v1/data/controller/applications/bcf/tenant%5Bname%3D%22$tenant%22%5D/segment%5Bname%3D%22$segment%22%5D",
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_ENCODING => "",
                        CURLOPT_MAXREDIRS => 10,
                        CURLOPT_SSL_VERIFYPEER => 0,
                        CURLOPT_SSL_VERIFYHOST => 0,
                        CURLOPT_TIMEOUT => 30,
                        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                        CURLOPT_CUSTOMREQUEST => "GET",
                        CURLOPT_HTTPHEADER => array(
                                "Content-Type: application/json",
                                "Cookie: session_cookie=$cookie",
                        ),
                ));

                $response = curl_exec($curl);
                $err = curl_error($curl);

                curl_close($curl);

                if ($err) {
                        echo "cURL Error #:" . $err;
                } else {
                        //echo $response;
                        $val = json_decode($response);
                        return count($val);
                }
	}
	public function CreateSegment($segmentname,$user){
                //$user = Auth::User();
                $BMAASDBController = new BMAASDBController;
                $tenantget = $BMAASDBController->GetTenantbyUser($user->id);
                $tenant =  $tenantget->tenant_name;
             
                $cookie = $this->Login();
                $curl = curl_init();
		$exist = $this->GetSubnet($user);
		$vlan = 202;
                if($exist == 1){
                        return 100;
		}elseif($exist == 0 ){
			$subnetparam='{
        				"name": "'.$segmentname.'",
        				"qos-traffic-class": "traffic-class-0"
    			}';
                        curl_setopt_array($curl, array(
                        #CURLOPT_PORT => "8443",
                        CURLOPT_URL => "https://172.16.0.2:8443/api/v1/data/controller/applications/bcf/tenant%5Bname%3D%22$tenant%22%5D/segment",
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_ENCODING => "",
                        CURLOPT_MAXREDIRS => 10,
                        CURLOPT_SSL_VERIFYPEER => 0,
                        CURLOPT_SSL_VERIFYHOST => 0,
                        CURLOPT_TIMEOUT => 30,
                        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                        CURLOPT_CUSTOMREQUEST => "POST",
                        CURLOPT_POSTFIELDS => $subnetparam,
                        CURLOPT_HTTPHEADER => array(
                                "Content-Type: application/json",
                                "Cookie: session_cookie=$cookie",
                                ),
                        ));

                        $response = curl_exec($curl);
                        $err = curl_error($curl);
			$httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
                        curl_close($curl);

                        if ($err) {
                                echo "cURL Error #:" . $err;
                        } else {
                                return $httpcode;
                        }
                }
	}
	public function Login(){

		$curl = curl_init();

		curl_setopt_array($curl, array(
  			CURLOPT_PORT => "8443",
  			CURLOPT_URL => "https://172.16.0.2:8443/api/v1/auth/login",
  			CURLOPT_RETURNTRANSFER => true,
  			CURLOPT_ENCODING => "",
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_SSL_VERIFYPEER => 0,
			CURLOPT_SSL_VERIFYHOST => 0,
  			CURLOPT_TIMEOUT => 30,
  			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  			CURLOPT_CUSTOMREQUEST => "POST",
  			CURLOPT_POSTFIELDS => "{\"user\":\"admin\",\"password\":\"BiznetGio2017\"}",
  			CURLOPT_HTTPHEADER => array(
    				"Content-Type: application/json",
  			),
		));

		$response = curl_exec($curl);
		$err = curl_error($curl);

		curl_close($curl);

		if ($err) {
  			echo "cURL Error #:" . $err;
		} else {
			$val=json_decode($response);
			return $val->session_cookie;
		}

	}
}
