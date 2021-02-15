<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Http\Request;
use App\Traits\AuthTrait;
use Illuminate\Support\Str;

class LicenseController extends Controller
{
	use AuthTrait;
	public function GetOrg(){
		$this->LoginvCloud();
		$admtoken = File::get('/var/www/html/GIO2/storage/app/admtoken.txt');
		$curl = curl_init();

		curl_setopt_array($curl, array(
  			CURLOPT_URL => "https://vcloud.biznetgiocloud.com/api/query?type=organization",
  			CURLOPT_RETURNTRANSFER => true,
  			CURLOPT_ENCODING => "",
  			CURLOPT_MAXREDIRS => 10,
  			CURLOPT_TIMEOUT => 0,
			CURLOPT_FOLLOWLOCATION => true,
  			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  			CURLOPT_CUSTOMREQUEST => "GET",
  			CURLOPT_HTTPHEADER => array(
    				"Accept: application/*+xml;version=34.0",
	    			"Authorization: Bearer $admtoken"
  			),
		));

		$response = curl_exec($curl);
		//echo $response;
                $orgresult = new \SimpleXMLElement($response);
                $jsonorg = json_encode($orgresult);
                $arrayorg = json_decode($jsonorg,TRUE);
		//print_r($arrayorg['OrgRecord'][0]['@attributes']['href']);
		$countorg = count($arrayorg['OrgRecord']);
		//echo $countorg;
		for ($i=0;$i<$countorg;$i++){
			$orguuid = substr($arrayorg['OrgRecord'][$i]['@attributes']['href'],-36);
			$orgarr[$orguuid]['name']=$arrayorg['OrgRecord'][$i]['@attributes']['displayName'];
			 $orgarr[$orguuid]['Windows2012STD'] = 0;
			 $orgarr[$orguuid]['Windows2012DC'] = 0;
			 $orgarr[$orguuid]['Windows2016STD'] = 0;
			 $orgarr[$orguuid]['Windows2016DC'] = 0;
			 $orgarr[$orguuid]['Windows2019STD'] = 0;
			 $orgarr[$orguuid]['Windows2019DC'] = 0;
			 $orgarr[$orguuid]['Redhat8'] = 0;
			 $orgarr[$orguuid]['Redhat7'] = 0;
			 $orgarr[$orguuid]['Redhat6'] = 0;
		}
		curl_close($curl);
		return $orgarr;
		//return view('licenseusage',['orglicense' => $orgarr]);
		
		
	}

    	public function GetOrgLicenseUsage(){
		//$orglicenseuse=array();	
		$orglicenseuse= $this->GetOrg();
		$admtoken = File::get('/var/www/html/GIO2/storage/app/admtoken.txt');
		//echo $admtoken.'<br>';
		$licensetypes = array('Windows2012STD','Windows2012DC','Windows2016STD','Windows2016DC','Windows2019STD','Windows2019DC','Redhat8','Redhat7','Redhat6');
		//$licensetypes=array('Windows2016STD');
		$curl = curl_init();
		foreach ($licensetypes as $licensetype ){
			//echo "$licensetype <br>";
			$contains = Str::contains($licensetype, 'Windows');
			if($contains == true){
				$OS="WindowsType";
			}else{
				$OS="RedhatType";
			}

			curl_setopt_array($curl, array(
  				CURLOPT_URL => "https://vcloud.biznetgiocloud.com/api/query?type=adminVM&filter=metadata@SYSTEM:$OS==STRING:$licensetype;(isVAppTemplate==false)&filterEncoded=true",
  				CURLOPT_RETURNTRANSFER => true,
  				CURLOPT_ENCODING => "",
  				CURLOPT_MAXREDIRS => 10,
  				CURLOPT_TIMEOUT => 0,
  				CURLOPT_FOLLOWLOCATION => true,
  				CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  				CURLOPT_CUSTOMREQUEST => "GET",
  				CURLOPT_HTTPHEADER => array(
    					"Accept: application/*+xml;version=34.0",
    					"Authorization: Bearer $admtoken"
  				),
			));

			$response = curl_exec($curl);


	                $osresult = new \SimpleXMLElement($response);
	                $jsonos = json_encode($osresult);
	                $arrayos = json_decode($jsonos,TRUE);
			if(array_key_exists('AdminVMRecord',$arrayos)){
				//print_r($arrayos['AdminVMRecord']);
				//echo "<br>";
				$vmrecord = $arrayos['AdminVMRecord'];
				$i=0;
				$vmarr=array();
				$count = count($arrayos['AdminVMRecord']);
				if($count > 1){
                                	foreach($vmrecord as $vmkey){
						$orguuid = substr($vmkey['@attributes']['org'],-36);
						//$orglicenseuse[$orguuid]['name'] = $orgnamearr[$orguuid];

						$vmarr[$i] = $orguuid;
						$i++;	
                                	}
				$vmcounts = array_count_values($vmarr);
				foreach($vmcounts as $vmcount => $value){
					$orglicenseuse[$vmcount][$licensetype] = $value;
				}	
				
				}else{
					$orguuid =  substr($arrayos['AdminVMRecord']['@attributes']['org'],-36);
					$orglicenseuse[$orguuid][$licensetype] = 1;	
				}
			}
		}
		//print_r($orglicenseuse);
		return view('license',['orglicense' => $orglicenseuse]);
		curl_close($curl);

	}
}
