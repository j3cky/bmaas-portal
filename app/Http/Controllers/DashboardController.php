<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Http\Request;
use App\Traits\AuthTrait;
use Illuminate\Support\Str;


class DashboardController extends Controller
{
    use AuthTrait;
    public function GetOrgCount($admtoken){
	$curl = curl_init();

	curl_setopt_array($curl, array(
   	   CURLOPT_URL => "https://vcloud.biznetgiocloud.com/api/query?type=organization&filter=isEnabled==true",
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
	curl_close($curl);
	$orgresult = new \SimpleXMLElement($response);
        $jsonorg = json_encode($orgresult);
        $arrayorg = json_decode($jsonorg,TRUE);
        $countorg = count($arrayorg['OrgRecord']);
	return $countorg;
    }
    
    public function GetOrgVDCCount($admtoken){
        $curl = curl_init();

        curl_setopt_array($curl, array(
           CURLOPT_URL => "https://vcloud.biznetgiocloud.com/api/query?type=adminOrgVdc&filter=isEnabled==true",
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
        curl_close($curl);
        $orgresult = new \SimpleXMLElement($response);
        $jsonorg = json_encode($orgresult);
        $arrayorg = json_decode($jsonorg,TRUE);
        $countorgvdc = count($arrayorg['AdminVdcRecord']);
        return $countorgvdc;
    }

    public function TotalMemoryAllocated($admtoken){
        $curl = curl_init();
	$totalmem = 0;
	$totalvcpu = 0;
	$vdcalloc = array ();
        curl_setopt_array($curl, array(
           CURLOPT_URL => "https://vcloud.biznetgiocloud.com/api/query?type=adminOrgVdc&filter=isEnabled==true",
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
        curl_close($curl);
        $orgvdcresult = new \SimpleXMLElement($response);
        $jsonorgvdc = json_encode($orgvdcresult);
        $arrayorgvdc = json_decode($jsonorgvdc,TRUE);
	$vdcrecord = $arrayorgvdc['AdminVdcRecord'];
	$count = count($vdcrecord);
        for ($i=0;$i<$count;$i++){
           $memalloc = $vdcrecord[$i]['@attributes']['memoryAllocationMB'];	
           $vcpualloc = $vdcrecord[$i]['@attributes']['cpuAllocationMhz'];	
	   $totalmem = $totalmem + $memalloc;
	   $totalvcpu = $totalvcpu + $vcpualloc;
	}
	$vdcalloc[0] = $totalmem;
	$vdcalloc[1] = $totalvcpu;
	return $vdcalloc;
    }

    public function GetStorageProfile($admtoken){
	$curl = curl_init();
	$spresultarr = array();
	curl_setopt_array($curl, array(
  	  CURLOPT_URL => "https://vcloud.biznetgiocloud.com/api/query?type=storageProfile&filter=name==Basic,name==SSD",
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

	curl_close($curl);
        $spresult = new \SimpleXMLElement($response);
        $jsonsp = json_encode($spresult);
        $arraysp = json_decode($jsonsp,TRUE);
        $sprecord = $arraysp['StorageProfileRecord'];	
        $count = count($sprecord);
        for ($i=0;$i<$count;$i++){
	   $spname = $sprecord[$i]['@attributes']['name'];
	   $spresultarr[$spname]['capacity']= $sprecord[$i]['@attributes']['totalMb'];
	   $spresultarr[$spname]['used']= $sprecord[$i]['@attributes']['usedMb'];
	   $spresultarr[$spname]['provisioned']= $sprecord[$i]['@attributes']['provisionedMb'];
        }
	return $spresultarr;
    }
   
    public function GetDeployedVM($admtoken){
	$curl = curl_init();

	curl_setopt_array($curl, array(
	  CURLOPT_URL => "https://vcloud.biznetgiocloud.com/api/query?type=adminVM&filter=isVAppTemplate==false",
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

	curl_close($curl);
        $vmresult = new \SimpleXMLElement($response);
        $jsonvm = json_encode($vmresult);
        $arrayvm = json_decode($jsonvm,TRUE);
        $vmrecord = $arrayvm['AdminVMRecord'];
	$count = count($vmrecord);	
	return $count;
    }
    
    public function GetPoweronVM($admtoken){
	$curl = curl_init();

	curl_setopt_array($curl, array(
	  CURLOPT_URL => "https://vcloud.biznetgiocloud.com/api/query?type=adminVM&filter=isVAppTemplate==false;status==POWERED_ON",
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

	curl_close($curl);
        $vmresult = new \SimpleXMLElement($response);
        $jsonvm = json_encode($vmresult);
        $arrayvm = json_decode($jsonvm,TRUE);
        $vmrecord = $arrayvm['AdminVMRecord'];
        $count = count($vmrecord);
	return $count;
    }

    public function GetPublicIPInfo($admtoken){
	$pubipresultarr = array();
	$curl = curl_init();

	curl_setopt_array($curl, array(
	  CURLOPT_URL => "https://vcloud.biznetgiocloud.com/api/query?type=externalNetwork",
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

	curl_close($curl);
	$pubipresult = new \SimpleXMLElement($response);
        $jsonpubip = json_encode($pubipresult);
        $arraypubip = json_decode($jsonpubip,TRUE);
        $pubiprecord = $arraypubip['NetworkRecord'];
	$count = count($pubiprecord);
        for ($i=0;$i<$count;$i++){
           $netuuid = $pubiprecord[$i]['@attributes']['ipScopeId'];
           $pubipresultarr[$netuuid]['gateway']= $pubiprecord[$i]['@attributes']['gateway'] ."/". $pubiprecord[$i]['@attributes']['subnetPrefixLength'];
           $pubipresultarr[$netuuid]['usedip']= $pubiprecord[$i]['@attributes']['numberOfUsedIps'];
           $pubipresultarr[$netuuid]['numberip']= $pubiprecord[$i]['@attributes']['numberOfIps'];
        }
	return $pubipresultarr;

    }

    public function PublishDashboard(){
        $this->LoginvCloud();
        $admtoken = File::get('/var/www/html/GIO2/storage/app/admtoken.txt');
	$getcountorg = $this->GetOrgCount($admtoken);
	$getcountorgvdc = $this->GetOrgVDCCount($admtoken);
	$getmemallocated = $this->TotalMemoryAllocated($admtoken);
	$getsp = $this->GetStorageProfile($admtoken);
	$gettotalvm = $this->GetDeployedVM($admtoken);
	$getonvm = $this->GetPoweronVM($admtoken);
	$getpubip = $this->GetPublicIPInfo($admtoken);
        return view('home',['orgcount' => $getcountorg , 'orgvdccount' =>  $getcountorgvdc, 'totalmem' => $getmemallocated[0], 'totalvcpu' => $getmemallocated[1] , 'sp' => $getsp , 'deployedvm' => $gettotalvm , 'poweronvm' => $getonvm, 'pubips' => $getpubip]); 
    }

}
