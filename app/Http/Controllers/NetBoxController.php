<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use App\Http\Controllers\SubnetCalculatorController;


class NetBoxController extends Controller
{

	public function GetPrefixFromIPAdd($ipaddr){
		$ipaddrexplode = explode("/", $ipaddr);
		$ipaddr = $ipaddrexplode[0];
		$subnet = $ipaddrexplode[1];
		$sub = new SubnetCalculatorController($ipaddr, $subnet);
		$network = $sub->getNetworkPortion();
                $curl = curl_init();

                curl_setopt_array($curl, array(
                        CURLOPT_URL => "https://172.16.1.4/api/ipam/prefixes/?prefix=$network%2F$subnet",
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_ENCODING => "",
                        CURLOPT_MAXREDIRS => 10,
                        CURLOPT_SSL_VERIFYHOST => 0,
                        CURLOPT_SSL_VERIFYPEER => 0,
                        CURLOPT_TIMEOUT => 30,
                        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                        CURLOPT_CUSTOMREQUEST => "GET",
                        CURLOPT_HTTPHEADER => array(
                                "Authorization: Token 0554acc7c422e39ce6e5abde46df10edddc1e377",
                                "Content-Type: application/json",
                        ),
                ));

                $response = curl_exec($curl);
                $err = curl_error($curl);

                curl_close($curl);

                if ($err) {
                        echo "cURL Error #:" . $err;
                } else {
                        $val = json_decode($response);
                        $prefix['id']=$val->results[0]->id;
                        $prefix['prefix']=$val->results[0]->prefix;
			$prefix['vlan']=$val->results[0]->vlan->vid;
			$prefix['gateway'] = $sub->getMaxHost();
                        return $prefix;
                }
        }


	public function GetAvailablePrefix($role){
		$curl = curl_init();

		curl_setopt_array($curl, array(
  			CURLOPT_URL => "https://172.16.1.4/api/ipam/prefixes/?status=active&limit=1&role=$role",
  			CURLOPT_RETURNTRANSFER => true,
  			CURLOPT_ENCODING => "",
			CURLOPT_MAXREDIRS => 10,
                        CURLOPT_SSL_VERIFYHOST => 0,
                        CURLOPT_SSL_VERIFYPEER => 0,
  			CURLOPT_TIMEOUT => 30,
  			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  			CURLOPT_CUSTOMREQUEST => "GET",
  			CURLOPT_HTTPHEADER => array(
    				"Authorization: Token 0554acc7c422e39ce6e5abde46df10edddc1e377",
    				"Content-Type: application/json",
  			),
		));

		$response = curl_exec($curl);
		$err = curl_error($curl);

		curl_close($curl);

		if ($err) {
  			echo "cURL Error #:" . $err;
		} else {
			$val = json_decode($response);
                        $prefix['id']=$val->results[0]->id;
			$prefix['prefix']=$val->results[0]->prefix;
			$prefix['vlan']=$val->results[0]->vlan->vid;
                        return $prefix;

                        //return $val->id;
		}
	}

        public function GetPrivatePrefixDetail($prefixid){
		$curl = curl_init();
		echo "yrdy";
		curl_setopt_array($curl, array(
  			CURLOPT_URL => "https://172.16.1.4/api/ipam/prefixes/$prefixid/",
  			CURLOPT_RETURNTRANSFER => true,
  			CURLOPT_ENCODING => "",
  			CURLOPT_MAXREDIRS => 10,
  			CURLOPT_TIMEOUT => 30,
  			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  			CURLOPT_CUSTOMREQUEST => "GET",
                        CURLOPT_SSL_VERIFYHOST => 0,
                        CURLOPT_SSL_VERIFYPEER => 0,
  			CURLOPT_POSTFIELDS => "",
  			CURLOPT_HTTPHEADER => array(
    				"Authorization: Token 0554acc7c422e39ce6e5abde46df10edddc1e377",
    				"Content-Type: application/json",
    				"Postman-Token: 90b06eb5-c2f0-4a80-b3d2-d50dababaa9d",
    				"cache-control: no-cache"
  			),
		));

		$response = curl_exec($curl);
		$err = curl_error($curl);

		curl_close($curl);
                if ($err) {
                        echo "cURL Error #:" . $err;
                } else {
			$val = json_decode($response);
                        $prefix['id']=$val->id;
                        $prefix['prefix']=$val->prefix;
                        $prefix['vlan']=$val->vlan->vid;
                        return $prefix;

                        //return $val->id;
                }
        }

        public function UpdateStatusPrefix($status,$tenantNB,$prefixid){
		$curl = curl_init();

		curl_setopt_array($curl, array(
  			CURLOPT_URL => "https://172.16.1.4/api/ipam/prefixes/$prefixid/",
  			CURLOPT_RETURNTRANSFER => true,
  			CURLOPT_ENCODING => "",
  			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 30,
                        CURLOPT_SSL_VERIFYHOST => 0,
                        CURLOPT_SSL_VERIFYPEER => 0,
  			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  			CURLOPT_CUSTOMREQUEST => "PATCH",
  			CURLOPT_POSTFIELDS => '{"status": "'.$status.'","tenant": '.$tenantNB.'}',
  			CURLOPT_HTTPHEADER => array(
    				"Authorization: Token 0554acc7c422e39ce6e5abde46df10edddc1e377",
    				"Content-Type: application/json",
  			),
		));

		$response = curl_exec($curl);
		$err = curl_error($curl);

		curl_close($curl);

		if ($err) {
  			echo "cURL Error #:" . $err;
		} else {
  			echo $response;
		}
	}

        public function UpdateStatusPubIP($status,$tenantNB,$pubipid){
                $curl = curl_init();

                curl_setopt_array($curl, array(
                        CURLOPT_URL => "https://172.16.1.4/api/ipam/ip-addresses/$pubipid/",
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_ENCODING => "",
                        CURLOPT_MAXREDIRS => 10,
                        CURLOPT_TIMEOUT => 30,
                        CURLOPT_SSL_VERIFYHOST => 0,
                        CURLOPT_SSL_VERIFYPEER => 0,
                        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                        CURLOPT_CUSTOMREQUEST => "PATCH",
                        CURLOPT_POSTFIELDS => '{"status": "'.$status.'","tenant": '.$tenantNB.'}',
                        CURLOPT_HTTPHEADER => array(
                                "Authorization: Token 0554acc7c422e39ce6e5abde46df10edddc1e377",
                                "Content-Type: application/json",
                        ),
                ));

                $response = curl_exec($curl);
                $err = curl_error($curl);

                curl_close($curl);

                if ($err) {
                        echo "cURL Error #:" . $err;
                } else {
                        echo $response;
                }
        }

	public function CreateNBTenant($tenant){
		//$tenant = request()->segment(2);
		$exist=$this->GetNBTenant($tenant);
		if($exist==0){
			$curl = curl_init();

			curl_setopt_array($curl, array(
  			CURLOPT_URL => "https://172.16.1.4/api/tenancy/tenants/",
  			CURLOPT_RETURNTRANSFER => true,
  			CURLOPT_ENCODING => "",
  			CURLOPT_MAXREDIRS => 10,
  			CURLOPT_TIMEOUT => 30,
  			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => "POST",
                        CURLOPT_SSL_VERIFYHOST => 0,
                        CURLOPT_SSL_VERIFYPEER => 0,
  			CURLOPT_POSTFIELDS => '{"name": "'.strtoupper($tenant).'","slug": "'.strtoupper($tenant).'"}',
  			CURLOPT_HTTPHEADER => array(
    				"Authorization: Token 0554acc7c422e39ce6e5abde46df10edddc1e377",
    				"Content-Type: application/json",
  				),
			));

			$response = curl_exec($curl);
			$err = curl_error($curl);

			curl_close($curl);

			if ($err) {
  				echo "cURL Error #:" . $err;
			} else {
  				$val = json_decode($response);
                        	return $val->id;

			}
		}
	}

	public function GetNBTenant($tenant){
                $curl = curl_init();

                curl_setopt_array($curl, array(
                        CURLOPT_URL => "https://172.16.1.4/api/tenancy/tenants/?name=$tenant",
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_ENCODING => "",
                        CURLOPT_MAXREDIRS => 10,
                        CURLOPT_TIMEOUT => 30,
                        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => "GET",
                        CURLOPT_SSL_VERIFYHOST => 0,
                        CURLOPT_SSL_VERIFYPEER => 0,
                        CURLOPT_HTTPHEADER => array(
                        "Authorization: Token 0554acc7c422e39ce6e5abde46df10edddc1e377",
                        "Content-Type: application/json",
                        ),
                ));

                $response = curl_exec($curl);
                $err = curl_error($curl);

                curl_close($curl);

                if ($err) {
                        echo "cURL Error #:" . $err;
                } else {
                        $val = json_decode($response);
			return $val->count;


                }
	}

        public function GetNBTenantid($tenant){
                $curl = curl_init();

                curl_setopt_array($curl, array(
                        CURLOPT_URL => "https://172.16.1.4/api/tenancy/tenants/?name=$tenant",
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_ENCODING => "",
                        CURLOPT_MAXREDIRS => 10,
                        CURLOPT_TIMEOUT => 30,
                        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                        CURLOPT_CUSTOMREQUEST => "GET",
                        CURLOPT_SSL_VERIFYHOST => 0,
                        CURLOPT_SSL_VERIFYPEER => 0,
                        CURLOPT_HTTPHEADER => array(
                        "Authorization: Token 0554acc7c422e39ce6e5abde46df10edddc1e377",
                        "Content-Type: application/json",
                        ),
                ));

                $response = curl_exec($curl);
                $err = curl_error($curl);

                curl_close($curl);

                if ($err) {
                        echo "cURL Error #:" . $err;
                } else {
                        $val = json_decode($response);
                        return $val->results[0]->id;


                }
        }


	public function GetAvailablePubIP(){
                $curl = curl_init();

                curl_setopt_array($curl, array(
                        CURLOPT_URL => "https://172.16.1.4/api/ipam/ip-addresses/?tag=Baremetal&status=active&limit=1",
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_ENCODING => "",
                        CURLOPT_MAXREDIRS => 10,
                        CURLOPT_TIMEOUT => 30,
                        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                        CURLOPT_CUSTOMREQUEST => "GET",
                        CURLOPT_SSL_VERIFYHOST => 0,
                        CURLOPT_SSL_VERIFYPEER => 0,
                        CURLOPT_HTTPHEADER => array(
                        "Authorization: Token 0554acc7c422e39ce6e5abde46df10edddc1e377",
                        "Content-Type: application/json",
                        ),
                ));

                $response = curl_exec($curl);
                $err = curl_error($curl);

                curl_close($curl);

                if ($err) {
                        echo "cURL Error #:" . $err;
                } else {
			$val = json_decode($response);
			$result['id']=$val->results[0]->id;
			$result['address']=$val->results[0]->address;
                        return $result;
		}


	}

	public function GetIPIdfromIpAdr($ipaddr){
                $ipaddrexplode = explode("/", $ipaddr);
                $pubipaddr = $ipaddrexplode[0];
                $subnet = $ipaddrexplode[1];
                //$sub = new SubnetCalculatorController($ipaddr, $subnet);
                //$network = $sub->getNetworkPortion();
                $curl = curl_init();

                curl_setopt_array($curl, array(
                        CURLOPT_URL => "https://172.16.1.4/api/ipam/ip-addresses/?address=$pubipaddr%2F$subnet",
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_ENCODING => "",
                        CURLOPT_MAXREDIRS => 10,
                        CURLOPT_SSL_VERIFYHOST => 0,
                        CURLOPT_SSL_VERIFYPEER => 0,
                        CURLOPT_TIMEOUT => 30,
                        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                        CURLOPT_CUSTOMREQUEST => "GET",
                        CURLOPT_HTTPHEADER => array(
                                "Authorization: Token 0554acc7c422e39ce6e5abde46df10edddc1e377",
                                "Content-Type: application/json",
                        ),
                ));

                $response = curl_exec($curl);
                $err = curl_error($curl);

                curl_close($curl);

                if ($err) {
                        echo "cURL Error #:" . $err;
                } else {
                        $val = json_decode($response);
                        //$prefix['id']=$val->results[0]->id;
                        //$prefix['prefix']=$val->results[0]->prefix;
                        //$prefix['vlan']=$val->results[0]->vlan->vid;
                        //$prefix['gateway'] = $sub->getMaxHost();
                        return $val->results[0]->id;
                }
	}

	public function GetAvailablePubIPfromPrefix($pubprefix,$limit){
               	$pubaddrexplode = explode("/", $pubprefix);
             	$prefix = $pubaddrexplode[0];
              	$subnet = $pubaddrexplode[1];

                $curl = curl_init();

                curl_setopt_array($curl, array(
                        CURLOPT_URL => "https://172.16.1.4/api/ipam/ip-addresses/?parent=$prefix%2F$subnet&status=active&limit=$limit",
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_ENCODING => "",
                        CURLOPT_MAXREDIRS => 10,
                        CURLOPT_TIMEOUT => 30,
                        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                        CURLOPT_CUSTOMREQUEST => "GET",
                        CURLOPT_SSL_VERIFYHOST => 0,
                        CURLOPT_SSL_VERIFYPEER => 0,
                        CURLOPT_HTTPHEADER => array(
                        "Authorization: Token 0554acc7c422e39ce6e5abde46df10edddc1e377",
                        "Content-Type: application/json",
                        ),
                ));

                $response = curl_exec($curl);
                $err = curl_error($curl);

                curl_close($curl);

                if ($err) {
                        echo "cURL Error #:" . $err;
                } else {
			$val = json_decode($response);
			for($i=0;$i<$limit;$i++){
                        	$result[$i]['id']=$val->results[$i]->id;
				$result[$i]['address']=$val->results[$i]->address;
			}
                        return $result;
                }


        }

}
