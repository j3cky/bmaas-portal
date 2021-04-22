<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Http\Controllers\BCFController;
use App\Http\Controllers\NetBoxController;
use App\Http\Controllers\BMAASDBController;
use App\Http\Controllers\SubnetCalculatorController;
use App\Http\Controllers\VPNController;
use App\Http\Controllers\ILOController;
use App\Jobs\DeployKubernetesCluster;
use App\Jobs\DeployBareMetal;
use App\Jobs\DeployWindows;
use App\Jobs\DeployGioPrivateJob;
use Illuminate\Support\Facades\Auth;
use Cookie;
use Dotenv\Regex\Result;
use Redirect;

class RackNController extends Controller
{
	//protected $BCFController;
	//public function __construct(BCFController $BCFController)
	//{
	//	$this->BCFController = $BCFController;
	//}

	//$NetBoxController = new NetBoxController;

	private $swagger_url = "https://103.93.128.193:8092/api/v3";
	private $swagger_user = 'rocketskates';
	private $swagger_pass = 'yrQE5fcuB6hd';
	private $vcmnguser = "administrator@vsphere.local";
	private $vcmngpass = "4dy0@pmR";
	private $tenant = "AA99999";
	private $vcsessionurl = "https://vc-mng.biznetgio.local/rest";


	public function TestIRC()
	{
		$cookie = Cookie::make('altModeProb', 'c74bd8c6e1bdaac40df13be628dc05f6');
		return Redirect::to('https://bmaas.arch.biznetgio.xyz:4443/irc.html?gui=true&lang=en')->withCookies([$cookie]);
	}
	public function MachineAction(Request $request)
	{
	}
	public function TestClusterProfile()
	{
		$user = Auth::User();
		$BMAASDBController = new BMAASDBController;
		$tenantget = $BMAASDBController->GetTenantbyUser($user->id);
		$tenant =  $tenantget->tenant_name;
		$cluster = $BMAASDBController->GetLastKubCluster($tenantget->tenant_id);
		$clustername = $cluster->profile_name;
		if (is_null($clustername)) {
			$clustername = $tenant . "-C00001";
		} else {
			++$clustername;
		}
		echo $clustername;
	}
	public function testVPN()
	{
		//$user = Auth::User();
		$BMAASDBController = new BMAASDBController;
		$tenantget = $BMAASDBController->GetTenantbyUser($user->id);
		$tenant =  $tenantget->tenant_name;
		$checkvpn = $BMAASDBController->CheckTenantVPN($user->tenant_id);
		if (is_null($checkvpn)) {
			echo "no VPN";
			$VPNController = new VPNController;
			$VPNController->CreateGroup($tenant);
			$VPNController->CreateUser($tenant, "test123456", $tenant, $user);
			$VPNController->CreateACL($tenant, $tenant, "172.16.30.0/24");
		} else {
			echo "ada von";
		}
	}
	public function CreateRackNMachine(){
		$curl = curl_init();
		$ILOController = new ILOController;
		$name = $ILOController->password_generate(6);
		curl_setopt_array($curl, array(
		  CURLOPT_URL => 'https://103.93.128.193:8092/api/v3/machines',
		  CURLOPT_RETURNTRANSFER => true,
		  CURLOPT_ENCODING => '',
		  CURLOPT_MAXREDIRS => 10,
		  CURLOPT_TIMEOUT => 0,
		  CURLOPT_FOLLOWLOCATION => true,
		  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		  CURLOPT_CUSTOMREQUEST => 'POST',
                  CURLOPT_SSL_VERIFYHOST => 0,
                  CURLOPT_SSL_VERIFYPEER => 0,
		  CURLOPT_POSTFIELDS =>'{"Meta": {"BaseContext": "govc"}, "Name": "'.$name.'" }',
		  CURLOPT_HTTPHEADER => array(
		    'Accept: application/json',
		    'Content-Type: application/json',
		    'Authorization: Basic cm9ja2V0c2thdGVzOnlyUUU1ZmN1QjZoZA=='
		  ),
		));

		$response = curl_exec($curl);

		curl_close($curl);
		$value = json_decode($response);
                return $value->Uuid;

	}
	public function SSHKeyDelete(Request $request)
	{
		$user = Auth::User();
		$BMAASDBController = new BMAASDBController;
		$ids = $request->checkssh;
		foreach ($ids as $id) {
			$BMAASDBController->RemoveSSHKey($id);
		}
		return redirect()->action('RackNController@ListSSHKey');
	}
	public function SubscribeView()
	{
		$user = Auth::User();
		$BMAASDBController = new BMAASDBController;
		$check = $BMAASDBController->CheckUserSubscribe($user);
		if (empty($check->tenant_id)) {
			return view('subscribe');
		} else {
			return redirect()->action('RackNController@GetListMachines');
		}
	}
	public function RedeployMachine(Request $request)
	{
		//$tenant = request()->segment(1);
		$user = Auth::User();
		$BMAASDBController = new BMAASDBController;
		$tenantget = $BMAASDBController->GetTenantbyUser($user->id);
		$tenant =  $tenantget->tenant_name;
		//$uuids= $request->check;
		$typeos = $request->typeos;
		if ($typeos == "linux") {
			$uuids = $request->check;
		} else if ($typeos == "windows") {
			$uuids = $request->checkwin;
		}
		foreach ($uuids as $uuid) {
			$ipmiaddr = $this->GetMachineIPMIAddr($uuid);
			$wf = $this->GetMachinesWF($uuid, "Workflow");
			$this->AssignWorkflow($uuid, "discover-base-Machines");
			$this->AssignWorkflow($uuid, $wf);
			$this->PatchUEFIBoot($ipmiaddr, $uuid);
			$this->ResetServer($ipmiaddr);
		}
		return redirect()->action('RackNController@GetListMachines');
	}

	public function OrderPage(Request $request)
	{
		$user = Auth::User();
		$BMAASDBController = new BMAASDBController;
		$tenantget = $BMAASDBController->GetTenantbyUser($user->id);
		if (empty($tenantget)) {
			//do something
			return redirect("/subscribe")->with('InfoSubscribtion', 'User ' . $user->name . ' is not Not Subscribed');
		} else {
			$tenant =  $tenantget->tenant_name;
		}

		$tenant =  $tenantget->tenant_name;
		//$tenant = request()->segment(1);
		$BMAASDBController = new BMAASDBController;
		$tenantid = $BMAASDBController->GetTenantID($tenant);
		$sshkey = $BMAASDBController->GetSSHKey($tenant);
		$profile_cluster = $BMAASDBController->GetBMAASKubCluster($tenantget->tenant_id);
		return view('order', ['sshkeys' => $sshkey, 'profilenames' => $profile_cluster]);
	}


	public function ListSSHKey()
	{
		$user = Auth::User();
		$BMAASDBController = new BMAASDBController;
		$tenantget = $BMAASDBController->GetTenantbyUser($user->id);
		if (empty($tenantget)) {
			//do something
			return redirect("/subscribe")->with('InfoSubscribtion', 'User ' . $user->name . ' is not Not Subscribed');
		} else {
			$tenant =  $tenantget->tenant_name;
		}

		$tenant =  $tenantget->tenant_name;
		$sshkey = $BMAASDBController->GetSSHKey($tenant);
		return view('sshkey', ['sshkeys' => $sshkey]);
	}

	public function SubscribeUser()
	{
		$user = Auth::User();
		$BMAASDBController = new BMAASDBController;
		$check = $BMAASDBController->CheckUserSubscribe($user);
		if (empty($check->tenant_id)) {
			$service = $BMAASDBController->GetLastServiceId();
			$serviceid = $service->serviceid;
			$serviceid++;
			$BMAASDBController->SubscribeUserUpdate($serviceid, $user);
		}
		return redirect()->action('RackNController@GetListMachines');
	}

	public function GetLastKubCluster($user)
	{
		$user = Auth::User();
	}

	public function SSHKeyCreate(Request $request)
	{
		//$tenant = request()->segment(1);
		$user = Auth::User();
		$validatedData = $request->validate([
			'sshkeycreate' => 'required',
			'sshkeyname' => 'required'
		]);

		$BMAASDBController = new BMAASDBController;
		$tenantget = $BMAASDBController->GetTenantbyUser($user->id);
		$tenant =  $tenantget->tenant_name;
		$tenantid = $BMAASDBController->GetTenantID($tenant);
		$sshkey[0] = $request->input('sshkeyname');
		$sshkey[1] = $request->input('sshkeycreate');
		$sshkey[2] = $tenantid->tenant_id;
		$BMAASDBController->AddBMAASSSHKey($sshkey);
		return redirect()->action('RackNController@ListSSHKey');
	}


	public function UnsubscribeKubernetesJob(Request $request)
	{
		$user = Auth::User();
		$objectvalue = (object) array(
			'kubprofiles' => $request->kubprofiles
		);
		$BMAASDBController = new BMAASDBController;
		//$BMAASDBController->BMAASQueue($objectvalue);
		dispatch(new UnsubKubernetesCluster($objectvalue, $user));
		$tenantget = $BMAASDBController->GetTenantbyUser($user->id);
		$tenant =  $tenantget->tenant_name;
		//return redirect()->action('RackNController@GetListMachines');
		return redirect()->action('RackNController@GetListMachines')->with('errorMessageDuration', 'Kubernetes Cluster Deployment in Progress, you will be notified when deployment has complete or check status on Container Tab');
	}
	public function UnsubscribeKubServer(Request $request)
	{
		$user = Auth::User();
		$BMAASDBController = new BMAASDBController;
		$NetBoxController = new NetBoxController;
		$BCFController = new BCFController;
	}
	public function UnsubscribeKubCluster(Request $request)
	{
		//$tenant = request()->segment(1);
		$user = Auth::User();
		$BMAASDBController = new BMAASDBController;
		$NetBoxController = new NetBoxController;
		$BCFController = new BCFController;
		$tenantget = $BMAASDBController->GetTenantbyUser($user->id);
		$tenant =  $tenantget->tenant_name;
		$profile = $request->input('kubprofiles');
		$certarrs = array("$profile-client-ca", "$profile-server-ca", "$profile-peer-ca");
		//DeleteCertDRP($certname);
		//echo $profile;
		//UpdateStatusPubIP($status,$tenantNB,$pubipid);
		$machineslist = $this->GetMachinesFromProfiles($profile, true);
		$token = $this->GetVCToken();
		foreach ($machineslist as $key => $val) {
			$vmid =  $val->Params->vmname;
			$uuid = $val->Uuid;
			$this->PowerVM($vmid, "stop", $token);
			$this->DeleteMasterVM($vmid, $token);
			$pubipid = $BMAASDBController->GetMachinePubIPId($uuid);
			$NetBoxController->UpdateStatusPubIP("active", "null", $pubipid->netbox_addr_id);
			$this->DeleteVMfromDRP($uuid);
			$BMAASDBController->RemoveBMAASPublicMachineAddr($uuid);
			$BMAASDBController->RemoveBMAASMachineAddr($uuid);
			$BMAASDBController->RemoveBMAASWF($uuid);
		}
		//$BCFController->DeleteInterfaceGroup($tenant."-baremetal","CVC001",$user);
		$machineslist = $this->GetMachinesFromProfiles($profile, false);
		foreach ($machineslist as $key => $val) {
			$uuid = $val->Uuid;
			$profilepatch = '[{ "op": "remove", "path": "/Profiles", "value": "" }]';
			$this->PatchMachinesProfiles($uuid, $profilepatch);
			$parampatch = '[
                                { "op": "remove", "path": "/tenant", "value": "" },
				{ "op": "remove", "path": "/access-keys", "value": "" },
                                { "op": "remove", "path": "/etcd~1ip", "value": "" },
                                { "op": "remove", "path": "/krib~1ip", "value": "" },
                                { "op": "remove",
                                  "path": "/net~1interface-topology",
                                  "value": ""
                                },
                                { "op": "remove",
                                  "path": "/net~1interface-config",
                                  "value":  ""
                                }
                        ]';
			$this->PatchMachinesParam($uuid, $parampatch);
			$parampatch = '[
                                { "op": "remove", "path": "/krib~1i-am-master", "value": false  }
                        ]';
			$this->PatchMachinesParam($uuid, $parampatch);
			$ipmiaddr = $this->GetMachineIPMIAddr($uuid);
			$this->PatchUEFIBoot($ipmiaddr, $uuid);
			$this->ResetServer($ipmiaddr);
			$pubipid = $BMAASDBController->GetMachinePubIPId($uuid);
			$NetBoxController->UpdateStatusPubIP("active", "null", $pubipid->netbox_addr_id);
			$BMAASDBController->RemoveBMAASPublicMachineAddr($uuid);
			$BMAASDBController->RemoveBMAASMachineAddr($uuid);
			$BMAASDBController->RemoveBMAASWF($uuid);
			$statuswf = $this->AssignWorkflow($uuid, "discover-base-Machines");
			$ifgroup = $BMAASDBController->GetMachineIfGroup($uuid);
			$BCFController->DeleteInterfaceGroup($tenant . "-baremetal", $ifgroup->ifgroup_uplink, $user);
		}
		//$querynet = $BMAASDBController->GetTenantPublicNetworkInfo($tenant,"baremetal");
		$querynet = $BMAASDBController->GetClusterPublicNetworkInfo($profile);
		//array_key_exists('netbox_prefix_id', $querynet);
		$NetBoxController->UpdateStatusPrefix("active", "null", $querynet->netbox_prefix_id);
		$tenantquery = $BMAASDBController->GetTenantID($tenant);

		//$BMAASDBController->RemoveBMAASNetwork($tenantquery->tenant_id);
		$lbranges = $BMAASDBController->GetProfileLBRange($profile);
		foreach ($lbranges as $lbrange) {
			$NetBoxController->UpdateStatusPubIP("active", "null", $lbrange->netbox_addr_id);
		}
		$BMAASDBController->RemoveBMAASPubNetworkLBRange($profile);
		$BMAASDBController->RemoveBMAASPubNetwork($querynet->id);
		$BMAASDBController->RemoveBMAASKubCluster($tenantquery->tenant_id, $profile);

		$this->DeleteProfiles($profile);
		foreach ($certarrs as $certarr) {
			//echo $certarr;
			$this->DeleteCertDRP($certarr);
		}
		return redirect()->action('RackNController@GetListMachines');
	}

	public function UnsubGioPrivate(Request $request)
	{
		$user = Auth::User();
		//$tenant = request()->segment(1);
		$BMAASDBController = new BMAASDBController;
		$NetBoxController = new NetBoxController;
		$BCFController = new BCFController;
		$ILOController = new ILOController;
		$tenantget = $BMAASDBController->GetTenantbyUser($user->id);
		$tenant =  $tenantget->tenant_name;
		$uuids = $request->checkpriv;
		foreach ($uuids as $uuid) {
			$parampatch = '[
                     		{ "op": "remove", "path": "/tenant", "value": "" },
                      		{ "op": "remove", "path": "/esxi~1network-firstboot-gateway", "value": "" },
                     		{ "op": "remove", "path": "/esxi~1network-firstboot-ipaddr", "value": "" },
				{ "op": "remove", "path": "/esxi~1network-firstboot-netmask", "value": "" }
			]';
			$this->PatchMachinesParam($uuid, $parampatch);
                        $parampatch = '[
                                { "op": "remove", "path": "/esxi~1patch-index", "value": "" },
                                { "op": "remove", "path": "/esxi~1selected-vendor", "value": "" }
                        ]';
			$this->PatchMachinesParam($uuid, $parampatch);

			$ipmiaddr = $this->GetMachineIPMIAddr($uuid);
			$this->PatchUEFIBoot($ipmiaddr, $uuid);
			$this->ResetServer($ipmiaddr);
			//$this->PatchMachinesParam($uuid, $parampatch);
			$profilepatch = '[{ "op": "remove", "path": "/Profiles", "value": "" }]';
			$this->PatchMachinesProfiles($uuid, $profilepatch);
			$this->AssignWorkflow($uuid, "discover-base-Machines");
			$ifgroup = $BMAASDBController->GetMachineIfGroup($uuid);
			$BCFController->DeleteInterfaceGroup($tenant . "-baremetal", $ifgroup->ifgroup_uplink, $user);
			$BMAASDBController->RemoveBMAASMachineAddr($tenant . "-vCenter");
			$BMAASDBController->RemoveBMAASMachineAddr($uuid);
			$BMAASDBController->RemoveBMAASWF($uuid);
			$BCFController->ChangeInterfaceGroupMode($ifgroup->ifgroup_uplink, "lacp-fallback-individual");
		}
		$this->DeleteVC();
		$this->DeleteProfiles("$tenant-Profile");
		return redirect()->action('RackNController@GetListMachines');
	}

	public function UnsubscribeBareMetal(Request $request)
	{
		$user = Auth::User();
		//$tenant = request()->segment(1);
		$BMAASDBController = new BMAASDBController;
		$NetBoxController = new NetBoxController;
		$BCFController = new BCFController;
		$ILOController = new ILOController;
		$tenantget = $BMAASDBController->GetTenantbyUser($user->id);
		$tenant =  $tenantget->tenant_name;
		$typeos = $request->typeos;
		if ($typeos == "linux") {
			$uuids = $request->check;
		} else if ($typeos == "windows") {
			$uuids = $request->checkwin;
		}
		foreach ($uuids as $uuid) {
			$param =  $this->GetMachinesWF($uuid, "Params");
			$machinesinfo = $BMAASDBController->GetMachinesPubIp($uuid);
			if (!empty($machinesinfo->public_ip)) {
				$pubip_id = $NetBoxController->GetIPIdfromIpAdr($machinesinfo->public_ip);
			}
			$wf = $this->GetMachinesWF($uuid, "Workflow");
			if ($wf == "image-deploy") {
				$parampatch = '[
                                { "op": "remove", "path": "/tenant", "value": "" },
				{ "op": "remove", "path": "/image-deploy~1admin-password", "value": "" },
				{ "op": "remove", "path": "/raid-skip-config", "value": true },
				{ "op": "remove", "path": "/raid-target-config", "value": ""  },
                                { "op": "remove", "path": "/windows~1private-network", "value": ""}
				]';
				$ipmiaddr = $this->GetMachineIPMIAddr($uuid);
				$this->PatchUEFIBoot($ipmiaddr, $uuid);
				$this->ResetServer($ipmiaddr);
				$this->PatchMachinesParam($uuid, $parampatch);
				$parampatch = '[{ "op": "remove", "path": "/windows~1public-network", "value": ""}]';
				$this->PatchMachinesParam($uuid, $parampatch);
				$profilepatch = '[{ "op": "remove", "path": "/Profiles", "value": "" }]';
				$this->PatchMachinesProfiles($uuid, $profilepatch);
				$this->AssignWorkflow($uuid, "discover-base-Machines");
				$ifgroup = $BMAASDBController->GetMachineIfGroup($uuid);
				$BCFController->DeleteInterfaceGroup($tenant . "-baremetal", $ifgroup->ifgroup_uplink, $user);
				$BMAASDBController->RemoveBMAASWF($uuid);
				//$this->ResetVMUefiBoot($param->vmname);
				if (!empty($machinesinfo->public_ip)) {
					$NetBoxController->UpdateStatusPubIP("active", "null", $pubip_id);
				}
				$BMAASDBController->RemoveBMAASMachineAddr($uuid);
				$ipmival = $BMAASDBController->GetIPMIUsers($ipmiaddr);
				$BMAASDBController->RemoveIPMIUsers($ipmival->id);
				$ILOController->RemoveILOUser($ipmiaddr, $ipmival->user_ilo_id);
			} else {
				$parampatch = '[
                                { "op": "remove", "path": "/tenant", "value": "" },
				{ "op": "remove", "path": "/access-keys", "value": "" },
				{ "op": "remove", "path": "/raid-skip-config", "value": true },
				{ "op": "remove", "path": "/raid-target-config", "value": ""  },
                                { "op": "remove",
                                  "path": "/net~1interface-topology",
				  "value": ""
				},
                                { "op": "remove",
                                  "path": "/net~1interface-config",
                                  "value":  ""
				}
				]';
				$ipmiaddr = $this->GetMachineIPMIAddr($uuid);
				$this->PatchUEFIBoot($ipmiaddr, $uuid);

				$this->ResetServer($ipmiaddr);
				$this->PatchMachinesParam($uuid, $parampatch);
				$this->AssignWorkflow($uuid, "discover-base-Machines");
				$ifgroup = $BMAASDBController->GetMachineIfGroup($uuid);
				$BMAASDBController->RemoveBMAASWF($uuid);
				$BCFController->DeleteInterfaceGroup($tenant . "-baremetal", $ifgroup->ifgroup_uplink, $user);
				//$this->ResetVMUefiBoot($param->vmname);
				if (!empty($machinesinfo->public_ip)) {
					$NetBoxController->UpdateStatusPubIP("active", "null", $pubip_id);
				}
				$BMAASDBController->RemoveBMAASMachineAddr($uuid);
				$ipmival = $BMAASDBController->GetIPMIUsers($ipmiaddr);
				$BMAASDBController->RemoveIPMIUsers($ipmival->id);
				$ILOController->RemoveILOUser($ipmiaddr, $ipmival->user_ilo_id);
			}
			if ($wf == "ubuntu-20") {
				$parampatch = '[
					{ "op": "remove", "path": "/access-ssh-root-mode", "value": "" }
				]';
				$this->PatchMachinesParam($uuid, $parampatch);
			}
			if ($wf == "ubuntu-18") {
				$parampatch = '[
					{ "op": "remove", "path": "/operating-system-disk", "value": "" },
					{ "op": "remove", "path": "/debian-network", "value": "" }
                                ]';
				$this->PatchMachinesParam($uuid, $parampatch);
			}
			if ($wf == 'debian10-base' || $wf == 'debian9-base') {
				$parampatch = '[
                                        { "op": "remove", "path": "/operating-system-disk", "value": "" },
					{ "op": "remove", "path": "/debian-network", "value": "" },
					{ "op": "remove", "path": "/debian-pubnetwork", "value": "" }
                                ]';
				$this->PatchMachinesParam($uuid, $parampatch);
			}
		}
		return redirect()->action('RackNController@GetListMachines');
	}

	public function ResetVMUefiBoot($vmname)
	{
		$ps = '
$env:HOME = "/home"
Update-Module "VMware.PowerCLI"
Import-Module "VMware.PowerCLI" | Out-Null
Set-PowerCLIConfiguration -InvalidCertificateAction:Ignore -Confirm:$false
Connect-VIServer -Server vc-mng.biznetgio.local -u administrator@vsphere.local -Password 4dy0@pmR
$strVMName = "' . $vmname . '"
## the device name of the NIC to which to boot
$strBootNICDeviceName = "Network adapter 3"
## the device name of the hard disk to which to boot
$strBootHDiskDeviceName = "Hard disk 1"
## get the VM object
$vm = Get-VM $strVMName

## get the VirtualEthernetCard device, and then grab its Key (DeviceKey, used later)
$intNICDeviceKey = ($vm.ExtensionData.Config.Hardware.Device | ?{$_.DeviceInfo.Label -eq $strBootNICDeviceName}).Key
## bootable NIC BootOption device, for use in setting BootOrder (the corresponding VirtualEthernetCard device on the VM has PXE enabled, assumed)
$oBootableNIC = New-Object -TypeName VMware.Vim.VirtualMachineBootOptionsBootableEthernetDevice -Property @{"DeviceKey" = $intNICDeviceKey}

## get the VirtualDisk device, then grab its Key (DeviceKey, used later)
$intHDiskDeviceKey = ($vm.ExtensionData.Config.Hardware.Device | ?{$_.DeviceInfo.Label -eq $strBootHDiskDeviceName}).Key
## bootable Disk BootOption device, for use in setting BootOrder (the corresponding VirtualDisk device is bootable, assumed)
$oBootableHDisk = New-Object -TypeName VMware.Vim.VirtualMachineBootOptionsBootableDiskDevice -Property @{"DeviceKey" = $intHDiskDeviceKey}

## bootable CDROM device (per the docs, the first CDROM with bootable media found is used)
$oBootableCDRom = New-Object -Type VMware.Vim.VirtualMachineBootOptionsBootableCdromDevice

$spec = New-Object VMware.Vim.VirtualMachineConfigSpec -Property @{
    "BootOptions" = New-Object VMware.Vim.VirtualMachineBootOptions -Property @{
        BootOrder = $oBootableNIC, $oBootableHDisk, $oBootableCDRom
    } ## end new-object
} ## end new-object

## reconfig the VM to use the spec with the new BootOrder
$vm.ExtensionData.ReconfigVM_Task($spec)
Restart-VM -VM $strVMName -RunAsync -Confirm:$false
';
		Storage::put('file.ps1', $ps);
		$content = File::get('/var/www/html/bmaas/storage/app/file.txt');
		shell_exec('pwsh -File /var/www/html/bmaas/storage/app/file.ps1 > /dev/null 2>&1 &');

		//echo $ps;
		//shell_exec('pwsh -Command '.$ps);

	}

	public function DeleteKubernetesNode(Request $request)
	{
		$user = Auth::User();
		$uuids = $request->checkkub;
		$BMAASDBController = new BMAASDBController;
		$NetBoxController = new NetBoxController;
		$BCFController = new BCFController;
		$tenantget = $BMAASDBController->GetTenantbyUser($user->id);
		$tenant =  $tenantget->tenant_name;

		foreach ($uuids as $uuid) {
			$statuswf = $this->AssignWorkflow($uuid, "krib-operate-delete");
			$curl = curl_init();

			curl_setopt_array($curl, array(
				CURLOPT_URL => "https://103.93.128.193:8092/api/v3/machines/$uuid",
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_ENCODING => '',
				CURLOPT_MAXREDIRS => 10,
				CURLOPT_TIMEOUT => 0,
				CURLOPT_FOLLOWLOCATION => true,
				CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
				CURLOPT_CUSTOMREQUEST => 'GET',
				CURLOPT_SSL_VERIFYHOST => 0,
				CURLOPT_SSL_VERIFYPEER => 0,
				CURLOPT_HTTPHEADER => array(
					'Authorization: Basic cm9ja2V0c2thdGVzOnlyUUU1ZmN1QjZoZA=='
				),
			));

			$response = curl_exec($curl);

			curl_close($curl);
			//echo $response;
			$result = json_decode($response);
			//print_r($result);
			$lastjob = $result->CurrentJob;
			echo $lastjob;
			$state = "";
			while ($state != "complete" || $state == "failed") {
				$state = $this->CheckLastJobStat($lastjob);
				echo $state;
			}
			if ($state == "complete") {
				$profilepatch = '[{ "op": "remove", "path": "/Profiles", "value": "" }]';
				$this->PatchMachinesProfiles($uuid, $profilepatch);
				$parampatch = '[
                                { "op": "remove", "path": "/tenant", "value": "" },
                                { "op": "remove", "path": "/access-keys", "value": "" },
                                { "op": "remove", "path": "/etcd~1ip", "value": "" },
                                { "op": "remove", "path": "/krib~1ip", "value": "" },
                                { "op": "remove",
                                  "path": "/net~1interface-topology",
                                  "value": ""
                                },
                                { "op": "remove",
                                  "path": "/net~1interface-config",
                                  "value":  ""
                                }
        	                ]';
				$this->PatchMachinesParam($uuid, $parampatch);
				$parampatch = '[
                                	{ "op": "remove", "path": "/krib~1i-am-master", "value": false  }
	                        ]';
				$this->PatchMachinesParam($uuid, $parampatch);
				$ipmiaddr = $this->GetMachineIPMIAddr($uuid);
				$this->PatchUEFIBoot($ipmiaddr, $uuid);
				$this->ResetServer($ipmiaddr);
				$pubipid = $BMAASDBController->GetMachinePubIPId($uuid);
				$NetBoxController->UpdateStatusPubIP("active", "null", $pubipid->netbox_addr_id);
				$BMAASDBController->RemoveBMAASPublicMachineAddr($uuid);
				$BMAASDBController->RemoveBMAASMachineAddr($uuid);
				$BMAASDBController->RemoveBMAASWF($uuid);
				$statuswf = $this->AssignWorkflow($uuid, "discover-base-Machines");
				$ifgroup = $BMAASDBController->GetMachineIfGroup($uuid);
				$BCFController->DeleteInterfaceGroup($tenant . "-baremetal", $ifgroup->ifgroup_uplink, $user);
			}
		}
		return redirect()->action('RackNController@GetListMachines');
	}

	public function CheckLastJobStat($jobuuid)
	{
		$curl = curl_init();

		curl_setopt_array($curl, array(
			CURLOPT_URL => "https://103.93.128.193:8092/api/v3/jobs/$jobuuid",
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => '',
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 0,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => 'GET',
			CURLOPT_SSL_VERIFYHOST => 0,
			CURLOPT_SSL_VERIFYPEER => 0,
			CURLOPT_HTTPHEADER => array(
				'Authorization: Basic cm9ja2V0c2thdGVzOnlyUUU1ZmN1QjZoZA=='
			),
		));

		$response = curl_exec($curl);

		curl_close($curl);
		$result = json_decode($response);
		//dd($result);
		return $result->ExitState;
		
	}

	public function GetActivityJobs()
	{
		$curl = curl_init();

		curl_setopt_array($curl, array(
			CURLOPT_URL => "https://103.93.128.193:8092/api/v3/jobs/9ce511c3-74dc-4951-a697-b80e3af50d56",
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => '',
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 0,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => 'GET',
			CURLOPT_SSL_VERIFYHOST => 0,
			CURLOPT_SSL_VERIFYPEER => 0,
			CURLOPT_HTTPHEADER => array(
				'Authorization: Basic cm9ja2V0c2thdGVzOnlyUUU1ZmN1QjZoZA=='
			),
		));

		$response = curl_exec($curl);

		curl_close($curl);
		$result = json_decode($response);
		// foreach ($activity as $key => $value){


		// }
		//dd($activity);
		return $result->ExitState;
	}

	public function GetListMachines()
	{
		$user = Auth::User();
		$machinesadd[][] = "";
		$BMAASDBController = new BMAASDBController;
		$tenantget = $BMAASDBController->GetTenantbyUser($user->id);

		//exit;
		if (empty($tenantget)) {
			//do something
			return redirect("/subscribe")->with('InfoSubscribtion', 'User ' . $user->name . ' is not Not Subscribed');
		} else {
			$tenant =  $tenantget->tenant_name;
		}
		$tenant =  $tenantget->tenant_name;
		//$this->tenant = $tenantval;
		//$tenant = request()->segment(1);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $this->swagger_url . '/' . "machines?tenant=$tenant&Workflow=image-deploy");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_USERPWD, $this->swagger_user . ':' . $this->swagger_pass);
		$headers = array();
		$headers[] = 'Accept: application/x-gzip';
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		$resultwin = curl_exec($ch);
		if (curl_errno($ch)) {
			echo 'Error:' . curl_error($ch);
		} else {
			//print_r(json_decode($result));
			//$resultrep = str_replace ('windows/private-network','privatenetwork',$result);
			//$resultrep = str_replace ('windows/public-network','publicnetwork',$resultrep);
			//$resultrep = str_replace ('net/interface-config','interfaceconfig',$resultrep);
			$resultwindec = json_decode($resultwin);

			//print_r($resultdec[0]->Params->privatenetwork->IP);
			//exit;
			foreach ($resultwindec as $key => $value) {
				$addresults = $BMAASDBController->GetMachinesIP($value->Uuid);
				$wf = $BMAASDBController->GetBMAASMachinesWF($value->Uuid, $tenantget->tenant_id);
				//print_r($addresults);
				$machinesadd[$addresults->machine_uuid]['private'] = $addresults->ip_address;
				$machinesadd[$addresults->machine_uuid]['public'] = $addresults->public_ip;
				$machinesadd[$addresults->machine_uuid]['workflow'] = $wf->workflow;
			}
			//	print_r($machinesadd);
			//	exit;
		}

		curl_setopt($ch, CURLOPT_URL, $this->swagger_url . '/' . "machines?tenant=$tenant&Workflow=kub-install-cluster");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_USERPWD, $this->swagger_user . ':' . $this->swagger_pass);
		$headers = array();
		$headers[] = 'Accept: application/x-gzip';
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		$resultkub = curl_exec($ch);
		if (curl_errno($ch)) {
			echo 'Error:' . curl_error($ch);
		} else {
			//print_r(json_decode($resultkub));
			//$resultrep = str_replace ('windows/private-network','privatenetwork',$result);
			//$resultrep = str_replace ('windows/public-network','publicnetwork',$resultrep);
			//$resultrep = str_replace ('net/interface-config','interfaceconfig',$resultrep);
			$resultkubdec = json_decode($resultkub);

			//print_r($resultdec[0]->Params->privatenetwork->IP);
			//exit;
			foreach ($resultkubdec as $key => $value) {
				$addresults = $BMAASDBController->GetMachinesIP($value->Uuid);
				$wf = $BMAASDBController->GetBMAASMachinesWF($value->Uuid, $tenantget->tenant_id);
				//print_r($addresults);
				$machinesadd[$value->Uuid]['private'] = $addresults->ip_address;
				$machinesadd[$value->Uuid]['public'] = $addresults->public_address;
				$machinesadd[$addresults->machine_uuid]['workflow'] = $wf->workflow;
			}
			//print_r($machinesadd);
			//exit;
		}

		curl_setopt($ch, CURLOPT_URL, $this->swagger_url . '/' . "machines?tenant=$tenant&Workflow=centos7-base&Workflow=centos8-base&Workflow=ubuntu-18&Workflow=ubuntu-20&Workflow=rhel-server-7-installation&Workflow=rhel-server-8-dvd-installation&Workflow=debian10-base&Workflow=debian9-base");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_USERPWD, $this->swagger_user . ':' . $this->swagger_pass);
		$headers = array();
		$headers[] = 'Accept: application/x-gzip';
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		$resultlin = curl_exec($ch);
		if (curl_errno($ch)) {
			echo 'Error:' . curl_error($ch);
		} else {
			//print_r(json_decode($result));
			//$resultrep = str_replace ('windows/private-network','privatenetwork',$result);
			//$resultrep = str_replace ('windows/public-network','publicnetwork',$resultrep);
			//$resultrep = str_replace ('net/interface-config','interfaceconfig',$resultrep);
			$resultlindec = json_decode($resultlin);

			//print_r($resultdec[0]->Params->privatenetwork->IP);
			//exit;
			foreach ($resultlindec as $key => $value) {
				$addresults = $BMAASDBController->GetMachinesIP($value->Uuid);
				$wf = $BMAASDBController->GetBMAASMachinesWF($value->Uuid, $tenantget->tenant_id);
				$machinesadd[$addresults->machine_uuid]['private'] = $addresults->ip_address;
				$machinesadd[$addresults->machine_uuid]['public'] = $addresults->public_ip;
				$machinesadd[$addresults->machine_uuid]['workflow'] = $wf->workflow;
			}
			//print_r($wf);
			//exit;
		}

		curl_setopt($ch, CURLOPT_URL, $this->swagger_url . '/' . "machines?tenant=$tenant&Workflow=Esxi-Deployment");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_USERPWD, $this->swagger_user . ':' . $this->swagger_pass);
		$headers = array();
		$headers[] = 'Accept: application/x-gzip';
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		$resultpriv = curl_exec($ch);
		if (curl_errno($ch)) {
			echo 'Error:' . curl_error($ch);
		} else {
			//print_r(json_decode($result));
			//$resultrep = str_replace ('windows/private-network','privatenetwork',$result);
			//$resultrep = str_replace ('windows/public-network','publicnetwork',$resultrep);
			//$resultrep = str_replace ('net/interface-config','interfaceconfig',$resultrep);
			$resultprivdec = json_decode($resultpriv);
			$vc = $BMAASDBController->GetvCenter($tenantget->tenant_id, "$tenantget->tenant_name-vCenter");
			foreach ($resultprivdec as $key => $value) {
				$addresults = $BMAASDBController->GetMachinesIP($value->Uuid);
				//print_r($addresults);
				//exit;
				$wf = $BMAASDBController->GetBMAASMachinesWF($value->Uuid, $tenantget->tenant_id);
				$machinesadd[$addresults->machine_uuid]['private'] = $addresults->ip_address;
				$machinesadd[$addresults->machine_uuid]['public'] = $addresults->public_ip;
				$machinesadd[$addresults->machine_uuid]['workflow'] = $wf->workflow;
				$machinesadd[$addresults->machine_uuid]['vcenter'] = $vc->ip_address;
			}
			
			//$vc = $BMAASDBController->GetvCenter($tenantid,$uuid);



			//print_r($resultdec[0]->Params->privatenetwork->IP);
			//exit;
			/*foreach ($resultprivdec as $key => $value){
                                $addresults = $BMAASDBController->GetMachinesIP($value->Uuid);
                                $wf = $BMAASDBController->GetBMAASMachinesWF($value->Uuid,$tenantget->tenant_id);
                                $machinesadd[$addresults->machine_uuid]['private'] = $addresults->ip_address;
                                $machinesadd[$addresults->machine_uuid]['public'] = $addresults->public_ip;
                                $machinesadd[$addresults->machine_uuid]['workflow'] = $wf->workflow;
			}*/
			//print_r($wf);
			//exit;
		}
		$profile_cluster = $BMAASDBController->GetBMAASKubCluster($tenantget->tenant_id);

		//return view('listmachines',['listmachineswin' => json_decode($resultwin)],['listmachineskub' => json_decode($resultkub)],['addr' => $machinesadd]);	
		return view('listmachines', ['listmachineswin' => json_decode($resultwin), 'listmachineskub' => json_decode($resultkub), 'listmachineslin' => json_decode($resultlin), 'addr' => $machinesadd, 'listmachinespriv' => json_decode($resultpriv), 'profilenames' => $profile_cluster]);
		curl_close($ch);
	}

	public function ListKubMachines()
	{
		$user = Auth::User();
		$machinesadd[][] = "";
		$BMAASDBController = new BMAASDBController;
		$tenantget = $BMAASDBController->GetTenantbyUser($user->id);
		$tenant =  $tenantget->tenant_name;
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $this->swagger_url . '/' . "machines?tenant=$tenant&Workflow=kub-install-cluster");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_USERPWD, $this->swagger_user . ':' . $this->swagger_pass);
		$headers = array();
		$headers[] = 'Accept: application/x-gzip';
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		$resultkub = curl_exec($ch);
		if (curl_errno($ch)) {
			echo 'Error:' . curl_error($ch);
		} else {
			$resultkubdec = json_decode($resultkub);
			foreach ($resultkubdec as $key => $value) {
				$addresults = $BMAASDBController->GetMachinesIP($value->Uuid);
				$wf = $BMAASDBController->GetBMAASMachinesWF($value->Uuid, $tenantget->tenant_id);
				//print_r($addresults);
				$machinesadd[$value->Uuid]['private'] = $addresults->ip_address;
				$machinesadd[$value->Uuid]['public'] = $addresults->public_address;
				$machinesadd[$value->Uuid]['workflow'] = $wf->workflow;
			}
		}
		return view('listkubtable', ['listmachineskub' => json_decode($resultkub), 'addr' => $machinesadd]);
		curl_close($ch);
	}
	public function ListWinMachines()
	{
		$user = Auth::User();
		$machinesadd[][] = "";
		$BMAASDBController = new BMAASDBController;
		$tenantget = $BMAASDBController->GetTenantbyUser($user->id);
		$tenant =  $tenantget->tenant_name;
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $this->swagger_url . '/' . "machines?tenant=$tenant&Workflow=image-deploy");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_USERPWD, $this->swagger_user . ':' . $this->swagger_pass);
		$headers = array();
		$headers[] = 'Accept: application/x-gzip';
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		$resultwin = curl_exec($ch);
		if (curl_errno($ch)) {
			echo 'Error:' . curl_error($ch);
		} else {
			$resultwindec = json_decode($resultwin);
			foreach ($resultwindec as $key => $value) {
				$addresults = $BMAASDBController->GetMachinesIP($value->Uuid);
				$wf = $BMAASDBController->GetBMAASMachinesWF($value->Uuid, $tenantget->tenant_id);
				$machinesadd[$addresults->machine_uuid]['private'] = $addresults->ip_address;
				$machinesadd[$addresults->machine_uuid]['public'] = $addresults->public_ip;
				$machinesadd[$addresults->machine_uuid]['workflow'] = $wf->workflow;
			}
		}
		curl_close($ch);
		return view('listwintable', ['listmachineswin' => json_decode($resultwin), 'addr' => $machinesadd]);
	}
	public function ListLinMachines()
	{
		$user = Auth::User();
		$machinesadd[][] = "";
		$BMAASDBController = new BMAASDBController;
		$tenantget = $BMAASDBController->GetTenantbyUser($user->id);
		$tenant =  $tenantget->tenant_name;
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $this->swagger_url . '/' . "machines?tenant=$tenant&Workflow=centos7-base&Workflow=centos8-base&Workflow=ubuntu-18&Workflow=ubuntu-20&Workflow=rhel-server-7-installation&Workflow=rhel-server-8-dvd-installation&Workflow=debian10-base&Workflow=debian9-base");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_USERPWD, $this->swagger_user . ':' . $this->swagger_pass);
		$headers = array();
		$headers[] = 'Accept: application/x-gzip';
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		$resultlin = curl_exec($ch);
		if (curl_errno($ch)) {
			echo 'Error:' . curl_error($ch);
		} else {
			$resultlindec = json_decode($resultlin);
			foreach ($resultlindec as $key => $value) {
				$addresults = $BMAASDBController->GetMachinesIP($value->Uuid);
				$wf = $BMAASDBController->GetBMAASMachinesWF($value->Uuid, $tenantget->tenant_id);
				$machinesadd[$addresults->machine_uuid]['private'] = $addresults->ip_address;
				$machinesadd[$addresults->machine_uuid]['public'] = $addresults->public_ip;
				$machinesadd[$addresults->machine_uuid]['workflow'] = $wf->workflow;
			}
		}
		curl_close($ch);
		return view('listlintable', ['listmachineslin' => json_decode($resultlin), 'addr' => $machinesadd]);
	}

	public function ProcessGioPrivateOrder1()
	{
		//return redirect("/$this-tenant");
		return redirect()->action('RackNController@GetListMachines', ['tenantval' => 'AA99999']);
		//return redirect()->route('/AA99999', 'RackNController@GetListMachines');
	}

	public function ProcessBareMetalJob(Request $request)
	{

		$user = Auth::User();
		$uuid = $this->GetAvailableMachine($request->serverspek);
		if (empty($uuid)) {
			return redirect("listmachines")->with('errorMessageDuration', "$request->serverspek Machine not Available");
		}
		$validatedData = $request->validate([
			'sshkeybare' => 'required',
			'region' => 'required',
			'serverspek' => 'required',
			'selectos' => 'required'
		]);

		$objectvalue = (object) array(
			'sshkeybare' => $request->sshkeycon,
			'region' => $request->region,
			'serverspek' => $request->serverspek,
			'selectos' => $request->selectos,
			'pubipcheckbare' => $request->pubipcheckbare,
			'raidbare' => $request->raidbare
		);
		$BMAASDBController = new BMAASDBController;
		//$BMAASDBController->BMAASQueue($objectvalue);
		dispatch(new DeployBareMetal($objectvalue, $user));
		$tenantget = $BMAASDBController->GetTenantbyUser($user->id);
		$tenant =  $tenantget->tenant_name;
		//return redirect()->action('RackNController@GetListMachines');
		return redirect()->action('RackNController@GetListMachines')->with('LinMessageDuration', 'Server Deployment in Progress, you will be notified when deployment has complete or check status on Linux Tab');
	}

	public function ProcessWindowsJob(Request $request)
	{

		$user = Auth::User();
		$uuid = $this->GetAvailableMachine($request->serverspek);
		if (empty($uuid)) {
			return redirect("listmachines")->with('errorMessageDuration', "$request->serverspek Machine not Available");
		}

		$validatedData = $request->validate([
			'adminpass' => 'required',
			'region' => 'required',
			'serverspek' => 'required',
			'selectoswin' => 'required'
		]);

		$objectvalue = (object) array(
			'adminpass' => $request->adminpass,
			'region' => $request->region,
			'serverspek' => $request->serverspek,
			'selectoswin' => $request->selectoswin,
			'pubipcheckwin' => $request->pubipcheckwin,
			'raidwin' => $request->raidwin
		);


		$BMAASDBController = new BMAASDBController;
		//$BMAASDBController->BMAASQueue($objectvalue);
		dispatch(new DeployWindows($objectvalue, $user));
		$tenantget = $BMAASDBController->GetTenantbyUser($user->id);
		$tenant =  $tenantget->tenant_name;
		//return redirect()->action('RackNController@GetListMachines');
		return redirect()->action('RackNController@GetListMachines')->with('WinMessageDuration', 'Server Deployment in Progress, you will be notified when deployment has complete or check status on Linux Tab');
	}

	public function ProcessKubernetesJob(Request $request)
	{
		$user = Auth::User();
		$uuid = $this->GetAvailableMachine($request->serverspek);
		if (empty($uuid)) {
			return redirect("listmachines")->with('errorMessageDuration', "$request->serverspek Machine not Available");
		}

		$objectvalue = (object) array(
			'sshkey' => $request->sshkeycon,
			'selectkubha' => $request->selectkubha,
			'workernum' => $request->workernum,
			'serverspek' => $request->serverspek,
			'pubipcheck' => $request->pubipcheckcon,
			'selectcluster' => $request->selectcluster
		);
		$BMAASDBController = new BMAASDBController;
		//$BMAASDBController->BMAASQueue($objectvalue);
		dispatch(new DeployKubernetesCluster($objectvalue, $user));
		$tenantget = $BMAASDBController->GetTenantbyUser($user->id);
		$tenant =  $tenantget->tenant_name;
		//return redirect()->action('RackNController@GetListMachines');
		return redirect()->action('RackNController@GetListMachines')->with('KubMessageDuration', 'Kubernetes Cluster Deployment in Progress, you will be notified when deployment has complete or check status on Container Tab');
	}


	public function ProcessKubernetesOrder($request, $user)
	{
		//$user = Auth::User();
		$NetBoxController = new NetBoxController;
		$BMAASDBController = new BMAASDBController;
		$BCFController = new BCFController;
		$ILOController = new ILOController;
		$tenantget = $BMAASDBController->GetTenantbyUser($user->id);
		$tenant =  $tenantget->tenant_name;
		$workflow = "kub-install-cluster";
		$cluster = $BMAASDBController->GetLastKubCluster($tenantget->tenant_id);
		$clustername = $cluster->profile_name;
		if (is_null($clustername)) {
			$clustername = $tenant . "-C00001";
		} else {
			++$clustername;
		}
		$sshkey = $request->sshkey;
		$selectkubha = $request->selectkubha;
		$workernum = $request->workernum;
		$serverspek = $request->serverspek;
		$pubipcheck = $request->pubipcheck;
		$selectcluster = $request->selectcluster;

		/*$sshkey = $request->input('sshkeycon');
                $selectkubha = $request->input('selectkubha');
                $workernum = $request->input('workernum');
                $serverspek = $request->input('serverspek');
		$pubipcheck = $request->input('pubipcheckcon');
		$selectcluster = $request->input('selectcluster');
		*/
		$subnet = "24";
		$tenantexist = $BMAASDBController->CheckTenantExist($tenant);
		$NBtenantid = $NetBoxController->GetNBTenantid($tenant);
		if ($tenantexist == null) {
			//return view('order');
			return redirect("orderpage/$tenant")->with('errorMessageDuration', 'Tenant Nor Found');
		}
		$networkexist = $BMAASDBController->CheckTenantNetwork($tenant, "baremetal");
		if ($selectcluster == "newcluster") {
			$networkexist = $BMAASDBController->CheckTenantNetwork($tenant, "baremetal");
			if ($networkexist == null) {
				$tenantdb = $BMAASDBController->GetTenantID($tenant);
				$prefixarr = $NetBoxController->GetAvailablePrefix("Private");
				$tenantdb->tenant_id;
				$networkinfo[0] = $tenantdb->tenant_id;
				$networkinfo[1] = $prefixarr['id'];
				$networkinfo[2] = "baremetal";
				$networkid = $BMAASDBController->AddBMAASTenantNetwork($networkinfo);
				$prefix = explode("/", $prefixarr['prefix']);
				$sub = new SubnetCalculatorController($prefix[0], $prefix[1]);
				$privategw = $sub->getMaxHost();
				$privateip = $sub->getMinHost();
				$ipcount = ip2long($privateip);
				$ipexplode = explode(".", $privateip);
				$strcount = strlen($ipexplode[3]);
				//$privategw=substr_replace($privateip,"254",-$strcount);
				$mastervip = substr_replace($privateip, "250", -$strcount);
				$systemtenantint = $BCFController->SystemTenantInterface($user);
				$segmentint = "";
				if ($systemtenantint = 204 or $systemtenantint == 100) {
					$segment = $BCFController->CreateSegment($tenant . "-baremetal", $user);
				}
				if ($segment == 204 or $segment == 100) {
					$BCFController->AddInterfaceGroup($tenant . "-baremetal", $prefixarr['vlan'], "CVC001", $user);
					//$BCFController->AddInterfaceGroup($prefixarr['vlan']);
					$segmentint = $BCFController->CreateSegmentInterface($tenant . "-baremetal", $user);
				}
				if ($segmentint == 204 or $segmentint == 100) {
					$segmentintip = $BCFController->CreateSegmentInterfaceIP($privategw, $tenant . "-baremetal", $user);
					$BCFController->ConfigureStaticRoute($prefixarr['prefix'], $user);
				}
			} else {
				$networkinfo = $BMAASDBController->GetTenantNetworkInfo($tenant, "baremetal");
				$networkid = $networkinfo->id;
				$prefixarr = $NetBoxController->GetPrivatePrefixDetail($networkinfo->netbox_prefix_id);
				$lastip = $BMAASDBController->GetLastPrivateIP($networkinfo->netbox_prefix_id);
				if ($lastip->ip_address == null) {
					$prefix = explode("/", $prefixarr['prefix']);
					$ipcount = ip2long($prefix[0]) + 1;
					$privateip = long2ip($ipcount);
				} else {
					$ipcount = $lastip->ip_address + 1;
					$privateip = long2ip($ipcount);
				}
				$ipexplode = explode(".", $privateip);
				$strcount = strlen($ipexplode[3]);
				$privategw = substr_replace($privateip, "254", -$strcount);
				$mastervip = substr_replace($privateip, "250", -$strcount);
				$checkcluster = $BMAASDBController->CheckBMAASKubCluster($tenantget->tenant_id, $clustername);
				if (empty($checkcluster)) {
					//$params[0] = $tenantget->tenant_id;
					//$params[1] = $clustername;				
					//$BMAASDBController->AddBMAASKubCluster($params);
					$BCFController->AddInterfaceGroup($tenant . "-baremetal", $prefixarr['vlan'], "CVC001", $user);
				}
			}
			// Public Prefix
			$tenantdb = $BMAASDBController->GetTenantID($tenant);
			$pubprefixarr = $NetBoxController->GetAvailablePrefix("Public");
			$pubaddrexplode = explode("/", $pubprefixarr['prefix']);
			$pubaddr = $pubaddrexplode[0];
			$pubsubnet = $pubaddrexplode[1];
			$sub = new SubnetCalculatorController($pubaddr, $pubsubnet);
			$pubgw = $sub->getMaxHost();
			$metallbrange = $NetBoxController->GetAvailablePubIPfromPrefix($pubprefixarr['prefix'], 2);
			$metallbrange1 = explode("/", $metallbrange[0]['address']);
			$metallbrange2 = explode("/", $metallbrange[1]['address']);
			$NetBoxController->UpdateStatusPubIP('reserved', $NBtenantid, $metallbrange[0]['id']);
			$NetBoxController->UpdateStatusPubIP('reserved', $NBtenantid, $metallbrange[1]['id']);
			$tenantdb->tenant_id;
			$pubnetworkinfo[0] = $tenantdb->tenant_id;
			$pubnetworkinfo[1] = $pubprefixarr['id'];
			$pubnetworkinfo[2] = "baremetal";
			$pubprefix = explode("/", $pubprefixarr['prefix']);
			$subnet = $pubprefix[1];
			$pubnetworkid = $BMAASDBController->AddBMAASTenantPublicNetwork($pubnetworkinfo);
			$addpubnetworkinfo[0] = $pubnetworkid;
			$addpubnetworkinfo[1] = $metallbrange[0]['id'];
			$addpubnetworkinfo[2] = $clustername;
			$addpubnetworkinfo[3] = $metallbrange[0]['address'];
			$BMAASDBController->AddBMAASMachinePublicAddr($addpubnetworkinfo);
			$addpubnetworkinfo[0] = $pubnetworkid;
			$addpubnetworkinfo[1] = $metallbrange[1]['id'];
			$addpubnetworkinfo[2] = $clustername;
			$addpubnetworkinfo[3] = $metallbrange[1]['address'];
			$BMAASDBController->AddBMAASMachinePublicAddr($addpubnetworkinfo);
			$segment = $BCFController->CreateSegment($tenant . '-' . $pubprefixarr['vlan'], $user);
			if ($segment == 204 or $segment == 100) {
				$BCFController->AddInterfaceGroup($tenant . '-' . $pubprefixarr['vlan'], $pubprefixarr['vlan'], "CVC001", $user);
			}
			// End Public Prefix
			if ($selectkubha == "kubnoha") {
				$jsonparam = '{
                        	        "Validated": true,
	                                "Available": true,
	                                "Errors": [],
	                                "ReadOnly": false,
	                                "Meta": {
	                                        "color": "black",
	                                        "icon": "tags",
	                                        "title": "User added"
	                                },
		                        "Endpoint": "",
	                                "Bundle": "",
	                                "Partial": false,
	                                "Name": "' . $clustername . '",
	                                "Description": "",
	                                "Documentation": "",
					"Params": {
	                                        "cluster/profile": "' . $clustername . '",
	                                        "etcd/cluster-profile": "' . $clustername . '",
	                                        "etcd/name": "' . $clustername . '",
	                                        "krib/cluster-profile": "' . $clustername . '",
	                                        "krib/cluster-kubernetes-version": "v1.18.10",
						"krib/cluster-crictl-version": "v1.18.0",
						"krib/cluster-cni-version": "v0.8.7",
						"krib/container-runtime": "containerd",
						"krib/cluster-master-vip": "' . $mastervip . '",
						"krib/package-repository": "http://103.93.128.193:8091/package/",
	                                        "krib/metallb-version": "v0.9.4",
	                                        "metallb/l2-ip-range": "' . $metallbrange1[0] . '-' . $metallbrange2[0] . '"				
	                                },
					"Profiles": []
				}';
				$this->CreateProfile($jsonparam);
				for ($i = 0; $i < $workernum; $i++) {
					$pubip = $NetBoxController->GetAvailablePubIPfromPrefix($pubprefixarr['prefix'], 1);
					$parampatch = '[
                                        { "op": "add", "path": "/tenant", "value": "' . $tenant . '" },
                                        { "op": "add", "path": "/access-keys", "value": {"user":"' . $sshkey . '"} },
                                        { "op": "add", "path": "/etcd~1ip", "value": "' . long2ip($ipcount) . '" },
                                        { "op": "add", "path": "/krib~1ip", "value": "' . long2ip($ipcount) . '" },
                                        { "op": "add",
                                                "path": "/net~1interface-topology",
                                                "value": {
                                                        "network": {
                                                                "bonds": {
                                                                        "bond0": {}
                                                                },
                                                                "ethernets": {
                                                                        "eno5": {},
                                                                        "eno6": {},
                                                                        "eno7": {}
                                                                },
                                                                "vlans": {
                                                                        "bond0.' . $prefixarr['vlan'] . '": {},
                                                                        "bond0.' . $pubprefixarr['vlan'] . '": {}
                                                                },
                                                                "version": 2
                                                        }
                                                }
                                        },
                                        { "op": "add",
                                                "path": "/net~1interface-config",
                                                "value":  {
                                                        "bond0": {
                                                                "interfaces": [
                                                                        "eno6",
                                                                        "eno7"
                                                                ],
                                                                "parameters": {
                                                                        "mii-monitor-interval": 100,
	                                                                "mode": "802.3ad",
        	                                                        "lacp-rate": "fast"
                                                                        
                                                                }
                                                        },
                                                        "bond0.' . $prefixarr['vlan'] . '": {
                                                                        "addresses": [
                                                                        "' . long2ip($ipcount) . '/24"
                                                                ],
                                                                "id": ' . $prefixarr['vlan'] . ',
                                                                "link": "bond0",
                                                                "nameservers": {
                                                                        "addresses": [
                                                                                "8.8.8.8"
                                                                        ]
                                                                },
                                                                "routes": [
                                                                {
                                                                        "on-link": true,
                                                                        "to": "172.16.0.0/16",
                                                                        "via": "' . $privategw . '"
                                                                }
                                                                ]
							},
                                                        "bond0.' . $pubprefixarr['vlan'] . '": {
                                                                "addresses": [
                                                                        "' . $pubip[0]['address'] . '"
                                                                ],
                                                                "gateway4": "' . $pubgw . '",
                                                                "id": ' . $pubprefixarr['vlan'] . ',
                                                                "link": "bond0",
                                                                "nameservers": {
                                                                        "addresses": [
                                                                                "8.8.8.8"
                                                                        ]
                                                                }
                                                        },
                                                        "eno5": {
                                                                "dhcp4": "no"
                                                        }
                                                }
                                        }]';
					$uuid = $this->GetAvailableMachine($serverspek);
					$this->CreateProfile($jsonparam);
					$profilepatch = '[{ "op": "add", "path": "/Profiles", "value": ["' . $clustername . '"] }]';
					$this->PatchMachinesProfiles($uuid, $profilepatch);
					$this->PatchMachinesParam($uuid, $parampatch);
					$statuswf = $this->AssignWorkflow($uuid, $workflow);
					$ipmiaddr = $this->GetMachineIPMIAddr($uuid);
					if ($statuswf == 200) {
						$addnetworkinfo[0] = $networkid;
						$addnetworkinfo[1] = long2ip($ipcount);
						$addnetworkinfo[2] = $uuid;
						$addnetworkinfo[3] = $pubip[0]['address'];

						$addpubnetworkinfo[0] = $pubnetworkid;
						$addpubnetworkinfo[1] = $pubip[0]['id'];
						$addpubnetworkinfo[2] = $uuid;
						$addpubnetworkinfo[3] = $pubip[0]['address'];

						$BMAASDBController->AddBMAASMachineAddr($addnetworkinfo);
						$BMAASDBController->AddBMAASMachinePublicAddr($addpubnetworkinfo);
						$params[0] = $uuid;
						$params[1] = $workflow;
						$params[2] = $tenantget->tenant_id;
						$BMAASDBController->AddBMAASWF($params);
						$NetBoxController->UpdateStatusPubIP('reserved', $NBtenantid, $pubip[0]['id']);
						$ifgroup = $BMAASDBController->GetMachineIfGroup($uuid);
						$BCFController->AddInterfaceGroup($tenant . '-' . $pubprefixarr['vlan'], $pubprefixarr['vlan'], $ifgroup->ifgroup_uplink, $user);
						$BCFController->AddInterfaceGroup($tenant . "-baremetal", $prefixarr['vlan'], $ifgroup->ifgroup_uplink, $user);

						$ILOController->CreateILOUser($ipmiaddr, $ifgroup->ipmi_console_hostname, $user);
					}
					$ipcount++;
				} // End For Looping
				if ($networkexist == null) {
					$NetBoxController->UpdateStatusPrefix("reserved", $NBtenantid, $prefixarr['id']);
				}
				$NetBoxController->UpdateStatusPrefix("reserved", $NBtenantid, $pubprefixarr['id']);
			} // End if Select Kubnoha
			elseif ($selectkubha == "kubha") {
				$jsonparam = '{
                                "Validated": true,
                                "Available": true,
                                "Errors": [],
                                "ReadOnly": false,
                                "Meta": {
                                        "color": "black",
                                        "icon": "tags",
                                        "title": "User added"
                                },
                                "Endpoint": "",
                                "Bundle": "",
                                "Partial": false,
                                "Name": "' . $clustername . '",
                                "Description": "",
                                "Documentation": "",
                                "Params": {
                                        "cluster/profile": "' . $clustername . '",
                                        "etcd/cluster-profile": "' . $clustername . '",
                                        "etcd/name": "' . $clustername . '",
                                        "krib/cluster-profile": "' . $clustername . '",
                                        "krib/cluster-kubernetes-version": "v1.18.10",
					"krib/cluster-crictl-version": "v1.18.0",
					"krib/container-runtime": "containerd",
					"etcd/server-count": 3,
					"krib/cluster-master-count": 3,
					"krib/cluster-master-vip": "' . $mastervip . '",
					"krib/selective-mastership": true,
					"krib/cluster-masters-untainted": false,
					"krib/cluster-vlan": ' . $prefixarr['vlan'] . ',
					"krib/cluster-cni-version": "v0.8.7",
					"krib/metallb-version": "v0.9.4",
					"krib/package-repository": "http://103.93.128.193:8091/package/",
					"krib/public-vlan": "' . $pubprefixarr['vlan'] . '",
					"metallb/l2-ip-range": "' . $metallbrange1[0] . '-' . $metallbrange2[0] . '"
                                },
                                "Profiles": []
				}';
				$this->CreateProfile($jsonparam);
				$profilepatch = '[{ "op": "add", "path": "/Profiles", "value": ["' . $clustername . '"] }]';
				for ($i = 0; $i < 3; $i++) {
					//$ipcount++;
					//$pubipcount++;
					$pubip = $NetBoxController->GetAvailablePubIPfromPrefix($pubprefixarr['prefix'], 1);
					$pubipvalarr[$i]['id'] = $pubip[0]['id'];
					$pubipvalarr[$i]['address'] = $pubip[0]['address'];
					$ipvalarr[$i] = long2ip($ipcount);
					$NetBoxController->UpdateStatusPubIP('reserved', $NBtenantid, $pubip[0]['id']);
					//$pubiparr = $NetBoxController->GetAvailablePubIP();
					$parampatch[$i] = '[
	                                { "op": "add", "path": "/tenant", "value": "' . $tenant . '" },
	                                { "op": "add", "path": "/access-keys", "value": {"user":"' . $sshkey . '"} },
					{ "op": "add", "path": "/krib~1i-am-master", "value": true },
					{ "op": "add", "path": "/raid-skip-config", "value": true },
					{ "op": "add", "path": "/etcd~1ip", "value": "' . long2ip($ipcount) . '" },
					{ "op": "add", "path": "/krib~1ip", "value": "' . long2ip($ipcount) . '" },
	                                { "op": "add",
	                                  "path": "/net~1interface-topology",
	                                  "value": {
                                                "network": {
                                                        "ethernets": {
                                                                "ens192": {},
								"ens224": {},
								"ens256": {}

							},
                                                        "vlans": {
                                                            	"ens256.' . $prefixarr['vlan'] . '": {},
                                                         	"ens224.' . $pubprefixarr['vlan'] . '": {}
                                                        },

                                                        "version": 2
                                                }
                                        }
	                                },
	                                { "op": "add",
	                                  "path": "/net~1interface-config",
	                                  "value":  {
                                                "ens192": {
                                                        "dhcp4": "no"
								
                                                },
                                                "ens224.' . $pubprefixarr['vlan'] . '": {
                                                        "dhcp4": "no",
                                                        "addresses": [
                                                                "' . $pubip[0]['address'] . '"
							],
							"id": ' . $pubprefixarr['vlan'] . ',
							"link": "ens224",
                                                        "gateway4": "' . $pubgw . '",
                                                        "nameservers": {
                                                                "addresses": [
                                                                        "8.8.8.8"
                                                                ]
                                                        }
                                                },
                                                "ens256.' . $prefixarr['vlan'] . '": {
                                                        "dhcp4": "no",
                                                        "addresses": [
                                                                "' . long2ip($ipcount) . '/24"
							],
							"id": ' . $prefixarr['vlan'] . ',
							"link": "ens256",
                                                        "routes": [
                                                          {
                                                                "on-link": true,
                                                                "to": "172.16.0.0/16",
                                                                "via": "' . $privategw . '"
                                                          }
                                                        ]
                                                }
                                        }
                                	}]';
					//$this->CreateDeploymentVM("kubha",$parampatch);
					$ipcount++;
				}
				$status = $this->CreateDeploymentVM("kubha", $parampatch, $user, $clustername);
				if ($networkexist == null) {
					$NetBoxController->UpdateStatusPrefix("reserved", $NBtenantid, $prefixarr['id']);
				}
				//if ($pubnetworkexist == null){
				$NetBoxController->UpdateStatusPrefix("reserved", $NBtenantid, $pubprefixarr['id']);
				//}
				for ($i = 0; $i < 3; $i++) {
					$addnetworkinfo[0] = $networkid;
					$addnetworkinfo[1] = $ipvalarr[$i];
					$addnetworkinfo[2] = $status[$i]['machineuuid'];
					$addnetworkinfo[3] = $pubipvalarr[$i]['address'];
					$addpubnetworkinfo[0] = $pubnetworkid;
					$addpubnetworkinfo[1] = $pubipvalarr[$i]['id'];
					$addpubnetworkinfo[2] = $status[$i]['machineuuid'];
					$addpubnetworkinfo[3] = $pubipvalarr[$i]['address'];

					if ($status[$i]['httpcode'] == 200) {
						$BMAASDBController->AddBMAASMachineAddr($addnetworkinfo);
						$BMAASDBController->AddBMAASMachinePublicAddr($addpubnetworkinfo);
					} else {
						$NetBoxController->UpdateStatusPubIP('active', $NBtenantid, $pubipvalarr[$i]['id']);
					}
				}
				for ($i = 0; $i < $workernum; $i++) {
					$pubip = $NetBoxController->GetAvailablePubIPfromPrefix($pubprefixarr['prefix'], 1);
					$pubipvalarr[$i]['id'] = $pubip[0]['id'];
					$pubipvalarr[$i]['address'] = $pubip[0]['address'];
					$parampatch = '[
                                	{ "op": "add", "path": "/tenant", "value": "' . $tenant . '" },
					{ "op": "add", "path": "/access-keys", "value": {"user":"' . $sshkey . '"} },
	                                { "op": "add", "path": "/etcd~1ip", "value": "' . long2ip($ipcount) . '" },
        	                        { "op": "add", "path": "/krib~1ip", "value": "' . long2ip($ipcount) . '" },
                                	{ "op": "add",
                                  		"path": "/net~1interface-topology",
                                  		"value": {
                                                	"network": {
                                                        	"bonds": {
                                                                	"bond0": {}
                                                        	},
                                                        	"ethernets": {
                                                                	"eno5": {},
                                                                	"eno6": {},
                                                                	"eno7": {}
                                                        	},
                                                        	"vlans": {
                                                                	"bond0.' . $prefixarr['vlan'] . '": {},
                                                                	"bond0.' . $pubprefixarr['vlan'] . '": {}
                                                        	},
                                                        	"version": 2
                                                	}
                                        	}
					},
                                	{ "op": "add",
                                  		"path": "/net~1interface-config",
                                  		"value":  {
                                                	"bond0": {
                                                        	"interfaces": [
                                                                	"eno6",
                                                                	"eno7"
                                                        	],
                                                        	"parameters": {
                                                                	"mii-monitor-interval": 100,
	                                                                "mode": "802.3ad",
        	                                                        "lacp-rate": "fast"
                                                                	
                                                        	}
                                                	},
                                                	"bond0.' . $prefixarr['vlan'] . '": {
                                                        		"addresses": [
                                                                	"' . long2ip($ipcount) . '/24"
                                                        	],
                                                        	"id": ' . $prefixarr['vlan'] . ',
                                                        	"link": "bond0",
                                                        	"nameservers": {
                                                                	"addresses": [
                                                                        	"8.8.8.8"
                                                                	]
                                                        	},
                                                        	"routes": [
                                                          	{
                                                                	"on-link": true,
                                                                	"to": "172.16.0.0/16",
                                                                	"via": "' . $privategw . '"
                                                          	}
                                                        	]
							},
                                                	"bond0.' . $pubprefixarr['vlan'] . '": {
                                                        	"addresses": [
                                                                	"' . $pubip[0]['address'] . '"
                                                        	],
                                                        	"gateway4": "' . $pubgw . '",
                                                        	"id": ' . $pubprefixarr['vlan'] . ',
                                                       		"link": "bond0",
                                                        	"nameservers": {
                                                                	"addresses": [
                                                                        	"8.8.8.8"
                                                                	]
								}
                                                	},
                                                	"eno5": {
                                                        	"dhcp4": "no"
                                                	}
                                        	}
					}]';
					$uuid = $this->GetAvailableMachine($serverspek);
					$this->PatchMachinesProfiles($uuid, $profilepatch);
					$this->PatchMachinesParam($uuid, $parampatch);
					$parampatch = '[{"op": "add", "path": "/krib~1i-am-master", "value": false}]';
					$this->PatchMachinesParam($uuid, $parampatch);
					$statuswf = $this->AssignWorkflow($uuid, $workflow);
					$ipmiaddr = $this->GetMachineIPMIAddr($uuid);
					if ($statuswf == 200) {
						$addnetworkinfo[0] = $networkid;
						$addnetworkinfo[1] = long2ip($ipcount);
						$addnetworkinfo[2] = $uuid;
						$addnetworkinfo[3] = $pubip[0]['address'];;

						$addpubnetworkinfo[0] = $pubnetworkid;
						$addpubnetworkinfo[1] = $pubip[0]['id'];
						$addpubnetworkinfo[2] = $uuid;
						$addpubnetworkinfo[3] = $pubip[0]['address'];

						$params[0] = $uuid;
						$params[1] = $workflow;
						$params[2] = $tenantget->tenant_id;
						$params[3] = 0;
						$BMAASDBController->AddBMAASWF($params);


						$BMAASDBController->AddBMAASMachineAddr($addnetworkinfo);
						$BMAASDBController->AddBMAASMachinePublicAddr($addpubnetworkinfo);
						$NetBoxController->UpdateStatusPubIP('reserved', $NBtenantid, $pubip[0]['id']);
						$ifgroup = $BMAASDBController->GetMachineIfGroup($uuid);
						$BCFController->AddInterfaceGroup($tenant . '-' . $pubprefixarr['vlan'], $pubprefixarr['vlan'], $ifgroup->ifgroup_uplink, $user);
						$BCFController->AddInterfaceGroup($tenant . "-baremetal", $prefixarr['vlan'], $ifgroup->ifgroup_uplink, $user);
						$ILOController->CreateILOUser($ipmiaddr, $ifgroup->ipmi_console_hostname, $user);
					}
					$ipcount++;
				}
			} // End Elseif Kubha	

			//$params[0] = $tenantget->tenant_id;
			//$params[1] = $clustername; 
			//$BMAASDBController->AddBMAASKubCluster($params);
			$params[0] = $tenantget->tenant_id;
			$params[1] = $clustername;
			$params[2] = $pubnetworkid;
			$BMAASDBController->AddBMAASKubCluster($params);
		} else {
			$profilepatch = '[{ "op": "add", "path": "/Profiles", "value": ["' . $selectcluster . '"] }]';
			$kubcluster = $BMAASDBController->CheckBMAASKubCluster($tenantget->tenant_id, $selectcluster);
			$pubnetworkid = $kubcluster->public_network_info_id;
			$clusternetinfo = $BMAASDBController->GetClusterPublicNetworkInfo($selectcluster);

			$networkinfo = $BMAASDBController->GetTenantNetworkInfo($tenant, "baremetal");
			$networkid = $networkinfo->id;
			$prefixarr = $NetBoxController->GetPrivatePrefixDetail($networkinfo->netbox_prefix_id);
			$pubprefixarr = $NetBoxController->GetPrivatePrefixDetail($clusternetinfo->netbox_prefix_id);
			$pubaddrexplode = explode("/", $pubprefixarr['prefix']);
			$pubaddr = $pubaddrexplode[0];
			$pubsubnet = $pubaddrexplode[1];
			$sub = new SubnetCalculatorController($pubaddr, $pubsubnet);
			$pubgw = $sub->getMaxHost();

			$lastip = $BMAASDBController->GetLastPrivateIP($networkinfo->netbox_prefix_id);
			if ($lastip->ip_address == null) {
				$prefix = explode("/", $prefixarr['prefix']);
				$ipcount = ip2long($prefix[0]) + 1;
				$privateip = long2ip($ipcount);
			} else {
				$ipcount = $lastip->ip_address + 1;
				$privateip = long2ip($ipcount);
			}
			$ipexplode = explode(".", $privateip);
			$strcount = strlen($ipexplode[3]);
			$privategw = substr_replace($privateip, "254", -$strcount);
			for ($i = 0; $i < $workernum; $i++) {
				$pubip = $NetBoxController->GetAvailablePubIPfromPrefix($pubprefixarr['prefix'], 1);
				$pubipvalarr[$i]['id'] = $pubip[0]['id'];
				$pubipvalarr[$i]['address'] = $pubip[0]['address'];
				$parampatch = '[
                                	{ "op": "add", "path": "/tenant", "value": "' . $tenant . '" },
					{ "op": "add", "path": "/access-keys", "value": {"user":"' . $sshkey . '"} },
	                                { "op": "add", "path": "/etcd~1ip", "value": "' . long2ip($ipcount) . '" },
        	                        { "op": "add", "path": "/krib~1ip", "value": "' . long2ip($ipcount) . '" },
                                	{ "op": "add",
                                  		"path": "/net~1interface-topology",
                                  		"value": {
                                                	"network": {
                                                        	"bonds": {
                                                                	"bond0": {}
                                                        	},
                                                        	"ethernets": {
                                                                	"eno5": {},
                                                                	"eno6": {},
                                                                	"eno7": {}
                                                        	},
                                                        	"vlans": {
                                                                	"bond0.' . $prefixarr['vlan'] . '": {},
                                                                	"bond0.' . $pubprefixarr['vlan'] . '": {}
                                                        	},
                                                        	"version": 2
                                                	}
                                        	}
					},
                                	{ "op": "add",
                                  		"path": "/net~1interface-config",
                                  		"value":  {
                                                	"bond0": {
                                                        	"interfaces": [
                                                                	"eno6",
                                                                	"eno7"
                                                        	],
                                                        	"parameters": {
                                                                	"mii-monitor-interval": 100,
	                                                                "mode": "802.3ad",
        	                                                        "lacp-rate": "fast"
                                                                	
                                                        	}
                                                	},
                                                	"bond0.' . $prefixarr['vlan'] . '": {
                                                        		"addresses": [
                                                                	"' . long2ip($ipcount) . '/24"
                                                        	],
                                                        	"id": ' . $prefixarr['vlan'] . ',
                                                        	"link": "bond0",
                                                        	"nameservers": {
                                                                	"addresses": [
                                                                        	"8.8.8.8"
                                                                	]
                                                        	},
                                                        	"routes": [
                                                          	{
                                                                	"on-link": true,
                                                                	"to": "172.16.0.0/16",
                                                                	"via": "' . $privategw . '"
                                                          	}
                                                        	]
							},
                                                	"bond0.' . $pubprefixarr['vlan'] . '": {
                                                        	"addresses": [
                                                                	"' . $pubip[0]['address'] . '"
                                                        	],
                                                        	"gateway4": "' . $pubgw . '",
                                                        	"id": ' . $pubprefixarr['vlan'] . ',
                                                       		"link": "bond0",
                                                        	"nameservers": {
                                                                	"addresses": [
                                                                        	"8.8.8.8"
                                                                	]
								}
                                                	},
                                                	"eno5": {
                                                        	"dhcp4": "no"
                                                	}
                                        	}
                                	}
				]';
				$uuid = $this->GetAvailableMachine($serverspek);
				$this->PatchMachinesProfiles($uuid, $profilepatch);
				$this->PatchMachinesParam($uuid, $parampatch);
				$parampatch = '[{"op": "add", "path": "/krib~1i-am-master", "value": false}]';
				$this->PatchMachinesParam($uuid, $parampatch);
				$statuswf = $this->AssignWorkflow($uuid, $workflow);
				if ($statuswf == 200) {
					$addnetworkinfo[0] = $networkid;
					$addnetworkinfo[1] = long2ip($ipcount);
					$addnetworkinfo[2] = $uuid;
					$addnetworkinfo[3] = $pubip[0]['address'];;

					$addpubnetworkinfo[0] = $pubnetworkid;
					$addpubnetworkinfo[1] = $pubip[0]['id'];
					$addpubnetworkinfo[2] = $uuid;
					$addpubnetworkinfo[3] = $pubip[0]['address'];

					$params[0] = $uuid;
					$params[1] = $workflow;
					$params[2] = $tenantget->tenant_id;
					$params[3] = 0;
					$BMAASDBController->AddBMAASWF($params);


					$BMAASDBController->AddBMAASMachineAddr($addnetworkinfo);
					$BMAASDBController->AddBMAASMachinePublicAddr($addpubnetworkinfo);
					$NetBoxController->UpdateStatusPubIP('reserved', $NBtenantid, $pubip[0]['id']);
					$ifgroup = $BMAASDBController->GetMachineIfGroup($uuid);
					$BCFController->AddInterfaceGroup($tenant . '-' . $pubprefixarr['vlan'], $pubprefixarr['vlan'], $ifgroup->ifgroup_uplink, $user);
					$BCFController->AddInterfaceGroup($tenant . "-baremetal", $prefixarr['vlan'], $ifgroup->ifgroup_uplink, $user);
				}
				$ipcount++;
			}
			//exsiting Cluster
		}
	}

	public function ProcessKubernetesOrder1($request, $user)
	{
		//$user = Auth::User();
		//echo "user adalah : ";
		//print_r($user);

		$NetBoxController = new NetBoxController;
		$BMAASDBController = new BMAASDBController;
		$BCFController = new BCFController;
		//$tenant = request()->segment(1);
		$tenantget = $BMAASDBController->GetTenantbyUser($user->id);
		$tenant =  $tenantget->tenant_name;
		$workflow = "kub-install-cluster";
		$cluster = $BMAASDBController->GetLastKubCluster($tenantget->tenant_id);
		$clustername = $cluster->profile_name;
		if (is_null($clustername)) {
			$clustername = $tenant . "-C00001";
		} else {
			++$clustername;
		}
		/*$sshkey = $request->input('sshkeycon');
		$selectkubha = $request->input('selectkubha');
		$workernum = $request->input('workernum');
		$serverspek = $request->input('serverspek');
		$pubipcheck = $request->input('pubipcheckcon');
		 */
		$sshkey = $request->sshkey;
		$selectkubha = $request->selectkubha;
		$workernum = $request->workernum;
		$serverspek = $request->serverspek;
		$pubipcheck = $request->pubipcheck;
		$selectcluster = $request->selectcluster;

		//if($workernum == "")$workernum=1;
		//$clustervlan = 202;
		//$ip="$privateip/$subnet";
		//$privateip="172.16.12.0";
		//$pubip="103.93.128.193";
		//$pubipcount=ip2long($pubip);
		//$ipcount=ip2long($privateip);
		$subnet = "24";
		//$ipexplode=explode(".",$privateip);
		//$strcount=strlen($ipexplode[3]);
		//$privategw=substr_replace($privateip,"254",-$strcount);
		//$mastervip=substr_replace($privateip,"253",-$strcount);	
		$tenantexist = $BMAASDBController->CheckTenantExist($tenant);
		$NBtenantid = $NetBoxController->GetNBTenantid($tenant);
		if ($tenantexist == null) {
			//return view('order');
			return redirect("orderpage/$tenant")->with('errorMessageDuration', 'Tenant Nor Found');
		}
		$networkexist = $BMAASDBController->CheckTenantNetwork($tenant, "baremetal");
		$pubnetworkexist = $BMAASDBController->CheckTenantPublicNetwork($tenant, "baremetal");
		if ($networkexist == null) {
			$tenantdb = $BMAASDBController->GetTenantID($tenant);
			$prefixarr = $NetBoxController->GetAvailablePrefix("Private");
			$tenantdb->tenant_id;
			$networkinfo[0] = $tenantdb->tenant_id;
			$networkinfo[1] = $prefixarr['id'];
			$networkinfo[2] = "baremetal";
			$networkid = $BMAASDBController->AddBMAASTenantNetwork($networkinfo);
			$prefix = explode("/", $prefixarr['prefix']);
			$sub = new SubnetCalculatorController($prefix[0], $prefix[1]);
			$privategw = $sub->getMaxHost();
			$privateip = $sub->getMinHost();
			$ipcount = ip2long($privateip);
			/*$lastip = $BMAASDBController->GetLastPrivateIP($networkinfo[1]);
                        if($lastip->ip_address == null){
                                $prefix = explode("/", $prefixarr['prefix']);
                                $ipcount=ip2long($prefix[0]);
                                $privateip = long2ip($ipcount);
                        }else{
                                $ipcount = $lastip->ip_address+1;
                                $privateip = long2ip($ipcount);
			}*/
			$ipexplode = explode(".", $privateip);
			$strcount = strlen($ipexplode[3]);
			//$privategw=substr_replace($privateip,"254",-$strcount);
			$mastervip = substr_replace($privateip, "250", -$strcount);
			$systemtenantint = $BCFController->SystemTenantInterface($user);
			$segmentint = "";
			if ($systemtenantint = 204 or $systemtenantint == 100) {
				$segment = $BCFController->CreateSegment($tenant . "-baremetal", $user);
			}
			if ($segment == 204 or $segment == 100) {
				$BCFController->AddInterfaceGroup($tenant . "-baremetal", $prefixarr['vlan'], "CVC001", $user);
				//$BCFController->AddInterfaceGroup($prefixarr['vlan']);
				$segmentint = $BCFController->CreateSegmentInterface($tenant . "-baremetal", $user);
			}
			if ($segmentint == 204 or $segmentint == 100) {
				$segmentintip = $BCFController->CreateSegmentInterfaceIP($privategw, $tenant . "-baremetal", $user);
				$BCFController->ConfigureStaticRoute($prefixarr['prefix'], $user);
			}
			$checkcluster = $BMAASDBController->CheckBMAASKubCluster($tenantget->tenant_id);
			if (empty($checkcluster)) {
				$params[0] = $tenantget->tenant_id;
				$params[1] = $clustername;
				$BMAASDBController->AddBMAASKubCluster($params);
			}
		} else {
			$networkinfo = $BMAASDBController->GetTenantNetworkInfo($tenant, "baremetal");
			$networkid = $networkinfo->id;
			$prefixarr = $NetBoxController->GetPrivatePrefixDetail($networkinfo->netbox_prefix_id);
			$lastip = $BMAASDBController->GetLastPrivateIP($networkinfo->netbox_prefix_id);
			if ($lastip->ip_address == null) {
				$prefix = explode("/", $prefixarr['prefix']);
				$ipcount = ip2long($prefix[0]) + 1;
				$privateip = long2ip($ipcount);
			} else {
				$ipcount = $lastip->ip_address + 1;
				$privateip = long2ip($ipcount);
			}
			$ipexplode = explode(".", $privateip);
			$strcount = strlen($ipexplode[3]);
			$privategw = substr_replace($privateip, "254", -$strcount);
			$mastervip = substr_replace($privateip, "250", -$strcount);
			$checkcluster = $BMAASDBController->CheckBMAASKubCluster($tenantget->tenant_id);
			if (empty($checkcluster)) {
				$params[0] = $tenantget->tenant_id;
				$params[1] = $clustername;
				$BMAASDBController->AddBMAASKubCluster($params);
				$BCFController->AddInterfaceGroup($tenant . "-baremetal", $prefixarr['vlan'], "CVC001", $user);
			}
		}
		if ($selectcluster == "newcluster") {
			$tenantdb = $BMAASDBController->GetTenantID($tenant);
			$pubprefixarr = $NetBoxController->GetAvailablePrefix("Public");
			$pubaddrexplode = explode("/", $pubprefixarr['prefix']);
			$pubaddr = $pubaddrexplode[0];
			$pubsubnet = $pubaddrexplode[1];
			$sub = new SubnetCalculatorController($pubaddr, $pubsubnet);
			$pubgw = $sub->getMaxHost();
			$metallbrange = $NetBoxController->GetAvailablePubIPfromPrefix($pubprefixarr['prefix'], 2);
			$metallbrange1 = explode("/", $metallbrange[0]['address']);
			$metallbrange2 = explode("/", $metallbrange[1]['address']);
			$NetBoxController->UpdateStatusPubIP('reserved', $NBtenantid, $metallbrange[0]['id']);
			$NetBoxController->UpdateStatusPubIP('reserved', $NBtenantid, $metallbrange[1]['id']);
			$tenantdb->tenant_id;
			$pubnetworkinfo[0] = $tenantdb->tenant_id;
			$pubnetworkinfo[1] = $pubprefixarr['id'];
			$pubnetworkinfo[2] = "baremetal";
			$pubprefix = explode("/", $pubprefixarr['prefix']);
			$subnet = $pubprefix[1];
			$pubnetworkid = $BMAASDBController->AddBMAASTenantPublicNetwork($pubnetworkinfo);
			$addpubnetworkinfo[0] = $pubnetworkid;
			$addpubnetworkinfo[1] = $metallbrange[0]['id'];
			$addpubnetworkinfo[2] = $clustername;
			$addpubnetworkinfo[3] = $metallbrange[0]['address'];
			$BMAASDBController->AddBMAASMachinePublicAddr($addpubnetworkinfo);
			$addpubnetworkinfo[0] = $pubnetworkid;
			$addpubnetworkinfo[1] = $metallbrange[1]['id'];
			$addpubnetworkinfo[2] = $clustername;
			$addpubnetworkinfo[3] = $metallbrange[1]['address'];
			$BMAASDBController->AddBMAASMachinePublicAddr($addpubnetworkinfo);
			$segment = $BCFController->CreateSegment($tenant . '-' . $pubprefixarr['vlan'], $user);
			if ($segment == 204 or $segment == 100) {
				$BCFController->AddInterfaceGroup($tenant . '-' . $pubprefixarr['vlan'], $pubprefixarr['vlan'], "CVC001", $user);
			}
			/*$lastpubip = $BMAASDBController->GetLastPublicIP($pubnetworkinfo[1]);
                        if($lastpubip->public_address == null){
                                $pubipcount=long2ip((ip2long($sub->getMinHost())+2));;//ip2long($pubprefix[0]);
                                $pubip = long2ip($pubipcount);
                        }else{
                                $pubipcount = $lastpubip->public_address;
                                $pubip = long2ip($pubipcount);
			}*/
		} else {
			$pubnetworkinfo = $BMAASDBController->GetTenantPublicNetworkInfo($tenant, "baremetal");
			$pubnetworkid = $pubnetworkinfo->id;
			$pubprefixarr = $NetBoxController->GetPrivatePrefixDetail($pubnetworkinfo->netbox_prefix_id);
			$pubaddrexplode = explode("/", $pubprefixarr['prefix']);
			$pubaddr = $pubaddrexplode[0];
			$pubsubnet = $pubaddrexplode[1];
			$metallbrange = $NetBoxController->GetAvailablePubIPfromPrefix($pubprefixarr['prefix'], 2);
			$metallbrange1 = explode("/", $metallbrange[0]['address']);
			$metallbrange2 = explode("/", $metallbrange[1]['address']);
			$sub = new SubnetCalculatorController($pubaddr, $pubsubnet);
			$pubgw = $sub->getMaxHost();
			$pubprefix = explode("/", $pubprefixarr['prefix']);
			$subnet = $pubprefix[1];
			/*$lastip = $BMAASDBController->GetLastPublicIP($pubnetworkinfo->netbox_prefix_id);
                        if($lastip->ip_address == null){

                        }else{
                                $pubipcount = $lastip->ip_address;
                                $pubip = long2ip($pubipcount);
			}*/
		}
		//$uuid = $this->GetAvailableMachine();
		//$this->BCFController->Execute($privateip,$clustervlan);

		/*$parampatch = '[
				{ "op": "add", "path": "/tenant", "value": "'.$tenant.'" },
                                { "op": "add", "path": "/access-keys", "value": {"user":"'.$sshkey.'"} },
                                { "op": "add",
                                  "path": "/net~1interface-topology",
                                  "value": {
                                                "network": {
                                                        "bonds": {
                                                                "bond0": {}
                                                        },
                                                        "ethernets": {
                                                                "ens3f0": {},
								"ens3f1": {},
								"ens3f2": {}
							},
							"vlans": {
								"bond0.100": {},
								"bond0.203": {}
    							},
                                                        "version": 2
                                                }
                                        }
				},
                                { "op": "add",
                                  "path": "/net~1interface-config",
				  "value":  {
                                                "bond0": {
                                                        "interfaces": [
                                                                "ens3f0",
								"ens3f1"
							],
							"parameters": {
                                                                "mii-monitor-interval": 100,
                                                                "mode": "802.3ad",
                                                                "lacp-rate": "fast"							
                                                        }
						},
						"bond0.'.$clustervlan.'": {
                                                        "addresses": [
                                                                "'.$privateip.'/24"
                                                        ],
                                                        "id": '.$clustervlan.',
                                                        "link": "bond0",
                                                        "nameservers": {
                                                                "addresses": [
                                                                        "8.8.8.8"
                                                                ]
							},
    							"routes": [
      							  {
        							"on-link": true,
        							"to": "172.16.0.0/16",
        							"via": "'.$privategw.'"
      							  }
    							]
						},
  						"bond0.203": {
    							"addresses": [
      								"103.93.128.197/28"
    							],
    							"gateway4": "103.93.128.206",
    							"id": 203,
    							"link": "bond0",
    							"nameservers": {
      								"addresses": [
        								"8.8.8.8"
      								]
    							
  						},
                                                "ens3f2": {
                                                        "dhcp4": "no"
                                                }
                                        }
  				}				  

		]';*/
		//echo "Test Configure";
		//exit;
		if ($selectkubha == "kubnoha") {
			$jsonparam = '{
                                "Validated": true,
                                "Available": true,
                                "Errors": [],
                                "ReadOnly": false,
                                "Meta": {
                                        "color": "black",
                                        "icon": "tags",
                                        "title": "User added"
                                },
                                "Endpoint": "",
                                "Bundle": "",
                                "Partial": false,
                                "Name": "' . $clustername . '",
                                "Description": "",
                                "Documentation": "",
				"Params": {
                                        "cluster/profile": "' . $clustername . '",
                                        "etcd/cluster-profile": "' . $clustername . '",
                                        "etcd/name": "' . $clustername . '",
                                        "krib/cluster-profile": "' . $clustername . '",
                                        "krib/cluster-kubernetes-version": "v1.18.10",
					"krib/cluster-crictl-version": "v1.18.0",
					"krib/cluster-cni-version": "v0.8.7",
					"krib/container-runtime": "containerd",
					"krib/cluster-master-vip": "' . $mastervip . '",
					"krib/package-repository": "http://103.93.128.193:8091/package/",
                                        "krib/metallb-version": "v0.9.4",
                                        "metallb/l2-ip-range": "' . $metallbrange1[0] . '-' . $metallbrange2[0] . '"				
                                },
				"Profiles": []
			}';
			$this->CreateProfile($jsonparam);
			for ($i = 0; $i < $workernum; $i++) {
				//$ipcount++;
				$pubip = $NetBoxController->GetAvailablePubIPfromPrefix($pubprefixarr['prefix'], 1);
				$parampatch = '[
                                        { "op": "add", "path": "/tenant", "value": "' . $tenant . '" },
                                        { "op": "add", "path": "/access-keys", "value": {"user":"' . $sshkey . '"} },
                                        { "op": "add", "path": "/etcd~1ip", "value": "' . long2ip($ipcount) . '" },
                                        { "op": "add", "path": "/krib~1ip", "value": "' . long2ip($ipcount) . '" },
                                        { "op": "add",
                                                "path": "/net~1interface-topology",
                                                "value": {
                                                        "network": {
                                                                "bonds": {
                                                                        "bond0": {}
                                                                },
                                                                "ethernets": {
                                                                        "eno5": {},
                                                                        "eno6": {},
                                                                        "eno7": {}
                                                                },
                                                                "vlans": {
                                                                        "bond0.' . $prefixarr['vlan'] . '": {},
                                                                        "bond0.' . $pubprefixarr['vlan'] . '": {}
                                                                },
                                                                "version": 2
                                                        }
                                                }
                                        },
                                        { "op": "add",
                                                "path": "/net~1interface-config",
                                                "value":  {
                                                        "bond0": {
                                                                "interfaces": [
                                                                        "eno6",
                                                                        "eno7"
                                                                ],
                                                                "parameters": {
                                                                        "mii-monitor-interval": 100,
	                                                                "mode": "802.3ad",
        	                                                        "lacp-rate": "fast"
                                                                        
                                                                }
                                                        },
                                                        "bond0.' . $prefixarr['vlan'] . '": {
                                                                        "addresses": [
                                                                        "' . long2ip($ipcount) . '/24"
                                                                ],
                                                                "id": ' . $prefixarr['vlan'] . ',
                                                                "link": "bond0",
                                                                "nameservers": {
                                                                        "addresses": [
                                                                                "8.8.8.8"
                                                                        ]
                                                                },
                                                                "routes": [
                                                                {
                                                                        "on-link": true,
                                                                        "to": "172.16.0.0/16",
                                                                        "via": "' . $privategw . '"
                                                                }
                                                                ]
							},
                                                        "bond0.' . $pubprefixarr['vlan'] . '": {
                                                                "addresses": [
                                                                        "' . $pubip[0]['address'] . '"
                                                                ],
                                                                "gateway4": "' . $pubgw . '",
                                                                "id": ' . $pubprefixarr['vlan'] . ',
                                                                "link": "bond0",
                                                                "nameservers": {
                                                                        "addresses": [
                                                                                "8.8.8.8"
                                                                        ]
                                                                }
                                                        },
                                                        "eno5": {
                                                                "dhcp4": "no"
                                                        }
                                                }
                                        }
				]';
				//echo $parampatch;			
				/*$parampatch[$i] = '[
                                { "op": "add", "path": "/tenant", "value": "'.$tenant.'" },
                                { "op": "add", "path": "/access-keys", "value": {"user":"'.$sshkey.'"} },
                                { "op": "add", "path": "/etcd~1ip", "value": "'.long2ip($ipcount).'" },
                                { "op": "add", "path": "/krib~1ip", "value": "'.long2ip($ipcount).'" },
                                { "op": "add",
                                  "path": "/net~1interface-topology",
                                  "value": {
                                                "network": {
                                                        "ethernets": {
                                                                "ens192": {},
                                                                "ens224": {},
                                                                "ens256": {}

                                                        },
                                                        "vlans": {
                                                                "ens256.'.$prefixarr['vlan'].'": {},
                                                                "ens224.'.$pubprefixarr['vlan'].'": {}
                                                        },

                                                        "version": 2
                                                }
                                        }
				},
                                { "op": "add",
                                  "path": "/net~1interface-config",
                                  "value":  {
                                                "ens192": {
                                                        "dhcp4": true
                                                },
                                                "ens224.'.$pubprefixarr['vlan'].'": {
                                                        "dhcp4": "no",
                                                        "addresses": [
                                                                "'.$pubip[0]['address'].'"
                                                        ],
                                                        "id": '.$pubprefixarr['vlan'].',
                                                        "link": "ens224",
                                                        "gateway4": "'.$pubgw.'",
                                                        "nameservers": {
                                                                "addresses": [
                                                                        "8.8.8.8"
                                                                ]
                                                        }
                                                },
                                                "ens256.'.$prefixarr['vlan'].'": {
                                                        "dhcp4": "no",
                                                        "addresses": [
                                                                "'.long2ip($ipcount).'/24"
                                                        ],
                                                        "id": '.$prefixarr['vlan'].',
                                                        "link": "ens256",
                                                        "routes": [
                                                          {
                                                                "on-link": true,
                                                                "to": "172.16.0.0/16",
                                                                "via": "'.$privategw.'"
                                                          }
                                                        ]
                                                }
                                        }
                                }
			]';*/
				$uuid = $this->GetAvailableMachine($serverspek);
				$this->CreateProfile($jsonparam);
				$profilepatch = '[{ "op": "add", "path": "/Profiles", "value": ["' . $clustername . '"] }]';
				$this->PatchMachinesProfiles($uuid, $profilepatch);
				$this->PatchMachinesParam($uuid, $parampatch);
				$statuswf = $this->AssignWorkflow($uuid, $workflow);
				if ($statuswf == 200) {
					$addnetworkinfo[0] = $networkid;
					$addnetworkinfo[1] = long2ip($ipcount);
					$addnetworkinfo[2] = $uuid;
					$addnetworkinfo[3] = $pubip[0]['address'];

					$addpubnetworkinfo[0] = $pubnetworkid;
					$addpubnetworkinfo[1] = $pubip[0]['id'];
					$addpubnetworkinfo[2] = $uuid;
					$addpubnetworkinfo[3] = $pubip[0]['address'];

					$BMAASDBController->AddBMAASMachineAddr($addnetworkinfo);
					$BMAASDBController->AddBMAASMachinePublicAddr($addpubnetworkinfo);
					$params[0] = $uuid;
					$params[1] = $workflow;
					$params[2] = $tenantget->tenant_id;
					$BMAASDBController->AddBMAASWF($params);
					$NetBoxController->UpdateStatusPubIP('reserved', $NBtenantid, $pubip[0]['id']);
					$ifgroup = $BMAASDBController->GetMachineIfGroup($uuid);
					$BCFController->AddInterfaceGroup($tenant . '-' . $pubprefixarr['vlan'], $pubprefixarr['vlan'], $ifgroup->ifgroup_uplink, $user);
					$BCFController->AddInterfaceGroup($tenant . "-baremetal", $prefixarr['vlan'], $ifgroup->ifgroup_uplink, $user);
				}
				$ipcount++;
			}
			if ($networkexist == null) {
				$NetBoxController->UpdateStatusPrefix("reserved", $NBtenantid, $prefixarr['id']);
			}
			if ($pubnetworkexist == null) {
				$NetBoxController->UpdateStatusPrefix("reserved", $NBtenantid, $pubprefixarr['id']);
			}

			//return redirect()->action('RackNController@GetListMachines');

		} elseif ($selectkubha == "kubha") {

			$jsonparam = '{
                                "Validated": true,
                                "Available": true,
                                "Errors": [],
                                "ReadOnly": false,
                                "Meta": {
                                        "color": "black",
                                        "icon": "tags",
                                        "title": "User added"
                                },
                                "Endpoint": "",
                                "Bundle": "",
                                "Partial": false,
                                "Name": "' . $clustername . '",
                                "Description": "",
                                "Documentation": "",
                                "Params": {
                                        "cluster/profile": "' . $clustername . '",
                                        "etcd/cluster-profile": "' . $clustername . '",
                                        "etcd/name": "' . $clustername . '",
                                        "krib/cluster-profile": "' . $clustername . '",
                                        "krib/cluster-kubernetes-version": "v1.18.10",
					"krib/cluster-crictl-version": "v1.18.0",
					"krib/container-runtime": "containerd",
					"etcd/server-count": 3,
					"krib/cluster-master-count": 3,
					"krib/cluster-master-vip": "' . $mastervip . '",
					"krib/selective-mastership": true,
					"krib/cluster-masters-untainted": false,
					"krib/cluster-vlan": ' . $prefixarr['vlan'] . ',
					"krib/cluster-cni-version": "v0.8.7",
					"krib/metallb-version": "v0.9.4",
					"krib/package-repository": "http://103.93.128.193:8091/package/",
					"krib/public-vlan": "' . $pubprefixarr['vlan'] . '",
					"metallb/l2-ip-range": "' . $metallbrange1[0] . '-' . $metallbrange2[0] . '"
                                },
                                "Profiles": []
			}';
			$this->CreateProfile($jsonparam);
			$profilepatch = '[{ "op": "add", "path": "/Profiles", "value": ["' . $clustername . '"] }]';
			for ($i = 0; $i < 3; $i++) {
				//$ipcount++;
				//$pubipcount++;
				$pubip = $NetBoxController->GetAvailablePubIPfromPrefix($pubprefixarr['prefix'], 1);
				$pubipvalarr[$i]['id'] = $pubip[0]['id'];
				$pubipvalarr[$i]['address'] = $pubip[0]['address'];
				$ipvalarr[$i] = long2ip($ipcount);
				$NetBoxController->UpdateStatusPubIP('reserved', $NBtenantid, $pubip[0]['id']);
				//$pubiparr = $NetBoxController->GetAvailablePubIP();
				$parampatch[$i] = '[
                                { "op": "add", "path": "/tenant", "value": "' . $tenant . '" },
                                { "op": "add", "path": "/access-keys", "value": {"user":"' . $sshkey . '"} },
				{ "op": "add", "path": "/krib~1i-am-master", "value": true },
				{ "op": "add", "path": "/raid-skip-config", "value": true },
				{ "op": "add", "path": "/etcd~1ip", "value": "' . long2ip($ipcount) . '" },
				{ "op": "add", "path": "/krib~1ip", "value": "' . long2ip($ipcount) . '" },
                                { "op": "add",
                                  "path": "/net~1interface-topology",
                                  "value": {
                                                "network": {
                                                        "ethernets": {
                                                                "ens192": {},
								"ens224": {},
								"ens256": {}

							},
                                                        "vlans": {
                                                            	"ens256.' . $prefixarr['vlan'] . '": {},
                                                         	"ens224.' . $pubprefixarr['vlan'] . '": {}
                                                        },

                                                        "version": 2
                                                }
                                        }
                                },
                                { "op": "add",
                                  "path": "/net~1interface-config",
                                  "value":  {
                                                "ens192": {
                                                        "dhcp4": "no"
								
                                                },
                                                "ens224.' . $pubprefixarr['vlan'] . '": {
                                                        "dhcp4": "no",
                                                        "addresses": [
                                                                "' . $pubip[0]['address'] . '"
							],
							"id": ' . $pubprefixarr['vlan'] . ',
							"link": "ens224",
                                                        "gateway4": "' . $pubgw . '",
                                                        "nameservers": {
                                                                "addresses": [
                                                                        "8.8.8.8"
                                                                ]
                                                        }
                                                },
                                                "ens256.' . $prefixarr['vlan'] . '": {
                                                        "dhcp4": "no",
                                                        "addresses": [
                                                                "' . long2ip($ipcount) . '/24"
							],
							"id": ' . $prefixarr['vlan'] . ',
							"link": "ens256",
                                                        "routes": [
                                                          {
                                                                "on-link": true,
                                                                "to": "172.16.0.0/16",
                                                                "via": "' . $privategw . '"
                                                          }
                                                        ]
                                                }
                                        }
                                }
                        	]';
				//$this->CreateDeploymentVM("kubha",$parampatch);
				$ipcount++;
			}
			//print_r($parampatch);
			//exit;
			$status = $this->CreateDeploymentVM("kubha", $parampatch, $user, "");
			if ($networkexist == null) {
				$NetBoxController->UpdateStatusPrefix("reserved", $NBtenantid, $prefixarr['id']);
			}
			if ($pubnetworkexist == null) {
				$NetBoxController->UpdateStatusPrefix("reserved", $NBtenantid, $pubprefixarr['id']);
			}

			for ($i = 0; $i < 3; $i++) {
				$addnetworkinfo[0] = $networkid;
				$addnetworkinfo[1] = $ipvalarr[$i];
				$addnetworkinfo[2] = $status[$i]['machineuuid'];
				$addnetworkinfo[3] = $pubipvalarr[$i]['address'];
				$addpubnetworkinfo[0] = $pubnetworkid;
				$addpubnetworkinfo[1] = $pubipvalarr[$i]['id'];
				$addpubnetworkinfo[2] = $status[$i]['machineuuid'];
				$addpubnetworkinfo[3] = $pubipvalarr[$i]['address'];

				if ($status[$i]['httpcode'] == 200) {
					$BMAASDBController->AddBMAASMachineAddr($addnetworkinfo);
					$BMAASDBController->AddBMAASMachinePublicAddr($addpubnetworkinfo);
				} else {
					$NetBoxController->UpdateStatusPubIP('active', $NBtenantid, $pubipvalarr[$i]['id']);
				}
			}

			for ($i = 0; $i < $workernum; $i++) {
				//$ipcount++;
				//$pubipcount++;
				//$pubiparr = $NetBoxController->GetAvailablePubIP();
				$pubip = $NetBoxController->GetAvailablePubIPfromPrefix($pubprefixarr['prefix'], 1);
				$pubipvalarr[$i]['id'] = $pubip[0]['id'];
				$pubipvalarr[$i]['address'] = $pubip[0]['address'];
				$parampatch = '[
                                	{ "op": "add", "path": "/tenant", "value": "' . $tenant . '" },
					{ "op": "add", "path": "/access-keys", "value": {"user":"' . $sshkey . '"} },
	                                { "op": "add", "path": "/etcd~1ip", "value": "' . long2ip($ipcount) . '" },
        	                        { "op": "add", "path": "/krib~1ip", "value": "' . long2ip($ipcount) . '" },
                                	{ "op": "add",
                                  		"path": "/net~1interface-topology",
                                  		"value": {
                                                	"network": {
                                                        	"bonds": {
                                                                	"bond0": {}
                                                        	},
                                                        	"ethernets": {
                                                                	"eno5": {},
                                                                	"eno6": {},
                                                                	"eno7": {}
                                                        	},
                                                        	"vlans": {
                                                                	"bond0.' . $prefixarr['vlan'] . '": {},
                                                                	"bond0.' . $pubprefixarr['vlan'] . '": {}
                                                        	},
                                                        	"version": 2
                                                	}
                                        	}
					},
                                	{ "op": "add",
                                  		"path": "/net~1interface-config",
                                  		"value":  {
                                                	"bond0": {
                                                        	"interfaces": [
                                                                	"eno6",
                                                                	"eno7"
                                                        	],
                                                        	"parameters": {
                                                                	"mii-monitor-interval": 100,
	                                                                "mode": "802.3ad",
        	                                                        "lacp-rate": "fast"
                                                                	
                                                        	}
                                                	},
                                                	"bond0.' . $prefixarr['vlan'] . '": {
                                                        		"addresses": [
                                                                	"' . long2ip($ipcount) . '/24"
                                                        	],
                                                        	"id": ' . $prefixarr['vlan'] . ',
                                                        	"link": "bond0",
                                                        	"nameservers": {
                                                                	"addresses": [
                                                                        	"8.8.8.8"
                                                                	]
                                                        	},
                                                        	"routes": [
                                                          	{
                                                                	"on-link": true,
                                                                	"to": "172.16.0.0/16",
                                                                	"via": "' . $privategw . '"
                                                          	}
                                                        	]
							},
                                                	"bond0.' . $pubprefixarr['vlan'] . '": {
                                                        	"addresses": [
                                                                	"' . $pubip[0]['address'] . '"
                                                        	],
                                                        	"gateway4": "' . $pubgw . '",
                                                        	"id": ' . $pubprefixarr['vlan'] . ',
                                                       		"link": "bond0",
                                                        	"nameservers": {
                                                                	"addresses": [
                                                                        	"8.8.8.8"
                                                                	]
								}
                                                	},
                                                	"eno5": {
                                                        	"dhcp4": "no"
                                                	}
                                        	}
                                	}
				]';
				$uuid = $this->GetAvailableMachine($serverspek);
				$this->PatchMachinesProfiles($uuid, $profilepatch);
				$this->PatchMachinesParam($uuid, $parampatch);
				$parampatch = '[{"op": "add", "path": "/krib~1i-am-master", "value": false}]';
				$this->PatchMachinesParam($uuid, $parampatch);
				$statuswf = $this->AssignWorkflow($uuid, $workflow);
				if ($statuswf == 200) {
					$addnetworkinfo[0] = $networkid;
					$addnetworkinfo[1] = long2ip($ipcount);
					$addnetworkinfo[2] = $uuid;
					$addnetworkinfo[3] = $pubip[0]['address'];;

					$addpubnetworkinfo[0] = $pubnetworkid;
					$addpubnetworkinfo[1] = $pubip[0]['id'];
					$addpubnetworkinfo[2] = $uuid;
					$addpubnetworkinfo[3] = $pubip[0]['address'];

					$params[0] = $uuid;
					$params[1] = $workflow;
					$params[2] = $tenantget->tenant_id;
					$params[3] = 0;
					$BMAASDBController->AddBMAASWF($params);


					$BMAASDBController->AddBMAASMachineAddr($addnetworkinfo);
					$BMAASDBController->AddBMAASMachinePublicAddr($addpubnetworkinfo);
					$NetBoxController->UpdateStatusPubIP('reserved', $NBtenantid, $pubip[0]['id']);
					$ifgroup = $BMAASDBController->GetMachineIfGroup($uuid);
					$BCFController->AddInterfaceGroup($tenant . '-' . $pubprefixarr['vlan'], $pubprefixarr['vlan'], $ifgroup->ifgroup_uplink, $user);
					$BCFController->AddInterfaceGroup($tenant . "-baremetal", $prefixarr['vlan'], $ifgroup->ifgroup_uplink, $user);
				}
				$ipcount++;
			}

			/*$uuid = $this->GetAvailableMachine();
                        $this->PatchMachinesProfiles($uuid,$profilepatch);
                        $this->PatchMachinesParam($uuid,$parampatch);
                        $parampatch = '[{"op": "add", "path": "/krib~1i-am-master", "value": false}]';
                        $this->PatchMachinesParam($uuid,$parampatch);
			$this->AssignWorkflow($uuid,$workflow);*/

			//return redirect()->action('RackNController@GetListMachines');

		}
	}
	public function ProcessBareMetalOrder($request, $user)
	{
		//$user = Auth::User();
		/*$validatedData = $request->validate([
			'sshkeybare' => 'required',
			'region' => 'required',
			'serverspek' => 'required',
			'selectos' => 'required'
		]);*/
		$NetBoxController = new NetBoxController;
		$BMAASDBController = new BMAASDBController;
		$BCFController = new BCFController;
		$ILOController = new ILOController;
		$tenantget = $BMAASDBController->GetTenantbyUser($user->id);

		$tenant =  $tenantget->tenant_name;
		$NBtenantid = $NetBoxController->GetNBTenantid($tenant);
		//$tenant = request()->segment(1);
		/*$workflow = $request->input('selectos');
		$hostname = $request->input('hostname');
		$sshkey = $request->input('sshkeybare');
		$serverspek = $request->input('serverspek');
		$pubipcheck = $request->input('pubipcheckbare');
		$raid = $request->input('raidbare');
		 */
		$workflow = $request->selectos;
		//$hostname = $request->hostname;
		$sshkey = $request->sshkeybare;
		$serverspek = $request->serverspek;
		$pubipcheck = $request->pubipcheckbare;
		$raid = $request->raidbare;

		$pubiparr['address'] = "";
		$uuid = $this->GetAvailableMachine($serverspek);
		if (empty($uuid)) {
			return redirect("listmachines")->with('errorMessageDuration', "$serverspek Machine not Available");
		} else {
			$parampatch = '[{ "op": "add", "path": "/tenant", "value": "' . $tenant . '" }]';
			$this->PatchMachinesParam($uuid, $parampatch);
		}
		$ipmiaddr = $this->GetMachineIPMIAddr($uuid);
		//$privateip = $request->input('privateip');
		//$subnet = $request->input('selectsubnet');
		$tenantexist = $BMAASDBController->CheckTenantExist($tenant);
		if ($tenantexist == null) {
			//return view('order');
			return redirect("orderpage/$tenant")->with('errorMessageDuration', 'Tenant Nor Found');
		}
		$networkexist = $BMAASDBController->CheckTenantNetwork($tenant, "baremetal");
		if ($networkexist == null) {
			$tenantdb = $BMAASDBController->GetTenantID($tenant);
			$prefixarr = $NetBoxController->GetAvailablePrefix("Private");
			$NetBoxController->UpdateStatusPrefix("reserved", $NBtenantid, $prefixarr['id']);
			$tenantdb->tenant_id;
			$networkinfo[0] = $tenantdb->tenant_id;
			$networkinfo[1] = $prefixarr['id'];
			$networkinfo[2] = "baremetal";
			$networkid = $BMAASDBController->AddBMAASTenantNetwork($networkinfo);
			//$lastip = $BMAASDBController->GetLastPrivateIP($networkinfo[1]);
			$prefix = explode("/", $prefixarr['prefix']);
			$sub = new SubnetCalculatorController($prefix[0], $prefix[1]);
			$privategw = $sub->getMaxHost();
			$privateip = $sub->getMinHost();
			/*if($lastip->ip_address == null){
        	                $prefix = explode("/", $prefixarr['prefix']);
                	        $longprivateip=ip2long($prefix[0])+1;
                        	$privateip = long2ip($longprivateip);
                	}else{
                        	$longprivateip = $lastip->ip_address+1;
                        	$privateip = long2ip($longprivateip);
			}*/
			$ipexplode = explode(".", $privateip);
			$strcount = strlen($ipexplode[3]);
			//$privategw=substr_replace($privateip,"254",-$strcount);
			$systemtenantint = $BCFController->SystemTenantInterface($user);
			$segmentint = "";
			if ($systemtenantint = 204 or $systemtenantint == 100) {
				$segment = $BCFController->CreateSegment($tenant . "-baremetal", $user);
			}
			if ($segment == 204 or $segment == 100) {
				//$BCFController->AddInterfaceGroup($prefixarr['vlan']);
				$segmentint = $BCFController->CreateSegmentInterface($tenant . "-baremetal", $user);
			}
			if ($segmentint == 204 or $segmentint == 100) {
				$segmentintip = $BCFController->CreateSegmentInterfaceIP($privategw, $tenant . "-baremetal", $user);
				$BCFController->ConfigureStaticRoute($prefixarr['prefix'], $user);
			}
		} else {
			$networkinfo = $BMAASDBController->GetTenantNetworkInfo($tenant, "baremetal");
			$networkid = $networkinfo->id;
			$prefixarr = $NetBoxController->GetPrivatePrefixDetail($networkinfo->netbox_prefix_id);
			$lastip = $BMAASDBController->GetLastPrivateIP($networkinfo->netbox_prefix_id);
			if ($lastip->ip_address == null) {
				$prefix = explode("/", $prefixarr['prefix']);
				$longprivateip = ip2long($prefix[0]) + 1;
				$privateip = long2ip($longprivateip);
			} else {
				$longprivateip = $lastip->ip_address + 1;
				$privateip = long2ip($longprivateip);
			}
			$ipexplode = explode(".", $privateip);
			$strcount = strlen($ipexplode[3]);
			$privategw = substr_replace($privateip, "254", -$strcount);
		}
		//$prefixarr = $NetBoxController->GetAvailablePrivatePrefix();
		/*if($lastip->ip_address == null){
	                $prefix = explode("/", $prefixarr['prefix']);
			$longprivateip=ip2long($prefix[0])+1;
			$privateip = long2ip($longprivateip);
		}else{
			$longprivateip = $lastip->ip_address+1;
			$privateip = long2ip($longprivateip);
		}*/
		//$NBtenantid = $NetBoxController->GetNBTenantid($tenant);
		//$prefix = explode("/", $prefixarr['prefix']);
		//$privateip=$prefix[0];
		$subnet = "24";
		$ip = $privateip . "/24";
		$vlan = $prefixarr['vlan'];
		//$uuid = $this->GetAvailableMachine($serverspek);
		//$parampatch = '[{ "op": "add", "path": "/tenant", "value": "'.$tenant.'" }]';
		//$this->PatchMachinesParam($uuid,$parampatch);
		if (isset($pubipcheck)) {
			$pubiparr = $NetBoxController->GetAvailablePubIP();
			//$NetBoxController->UpdateStatusPubIP("reserved",$NBtenantid,$pubiparr['id']);
			//$pubipexplode = explode("/", $pubip['gateway']);
			//$pubgw = $pubipexplode[0];
			$prefixval = $NetBoxController->GetPrefixFromIPAdd($pubiparr['address']);
			$parampatch = '[
                                { "op": "add", "path": "/access-keys", "value": {"user":"' . $sshkey . '"} },
                                { "op": "add",
                                  "path": "/net~1interface-topology",
                                  "value": {
                                                "network": {
                                                        "bonds": {
                                                                "bond0": {}
                                                        },
                                                        "ethernets": {
                                                                "eno5": {},
                                                                "eno6": {},
                                                                "eno7": {}
                                                        },
                                                        "vlans": {
								"bond0.' . $vlan . '": {},
								"bond0.' . $prefixval['vlan'] . '": {}
                                                        },
                                                        "version": 2
                                                }
                                        }
				},
                                { "op": "add",
                                  "path": "/net~1interface-config",
                                  "value":  {
                                                "bond0": {
                                                        "interfaces": [
                                                                "eno6",
                                                                "eno7"
                                                        ],
                                                        "parameters": {
                                                                "mii-monitor-interval": 100,
                                                                "mode": "802.3ad",
                                                                "lacp-rate": "fast"
                                                        }
                                                },
                                                "bond0.' . $vlan . '": {
                                                        "addresses": [
                                                                "' . $ip . '"
                                                        ],
                                                        "id": ' . $vlan . ',
                                                        "link": "bond0",
                                                        "nameservers": {
                                                                "addresses": [
                                                                        "8.8.8.8"
                                                                ]
							},
                                                        "routes": [
                                                               {
                                                            	"on-link": true,
                                                           	"to": "172.16.0.0/16",
                                                         	"via": "' . $privategw . '"
                                                               }
                                                  	]

						},
                                                "bond0.' . $prefixval['vlan'] . '": {
                                                        "addresses": [
                                                                "' . $pubiparr['address'] . '"
                                                        ],
                                                        "gateway4": "' . $prefixval['gateway'] . '",
                                                        "id": ' . $prefixval['vlan'] . ',
                                                        "link": "bond0",
                                                        "nameservers": {
                                                                "addresses": [
                                                                        "8.8.8.8"
                                                                ]
                                                        }
                                                },
                                                "eno5": {
                                                        "dhcp4": false
                                                }
                                        }
                                }
			]';
			$pubprefix = explode("/", $pubiparr['address']);
			if ($workflow == 'debian10-base' || $workflow == 'debian9-base') {
				$parampatch = '[
                                        { "op": "add", "path": "/operating-system-disk", "value": "sdb" },
                                        { "op": "add", "path": "/debian-network", "value": [
                                                  {
                                                        "Address": "' . $privateip . '",
                                                        "gateway": "' . $privategw . '",
                                                        "vlan": ' . $vlan . '
                                                  }
                                                ]
                                        },
                                        { "op": "add", "path": "/debian-pubnetwork", "value": [
                                                  {
                                                        "Address": "' . $pubiparr['address'] . '",
                                                        "gateway": "' . $prefixval['gateway'] . '",
                                                        "vlan": ' . $prefixval['vlan'] . '
                                                  }
                                                ]
                                        }
                                        ]';
				$this->PatchMachinesParam($uuid, $parampatch);
			}

			//echo $parampatch;
			//exit;

		} else {
			$parampatch = '[
				{ "op": "add", "path": "/access-keys", "value": {"user":"' . $sshkey . '"} },
				{ "op": "add", 
				  "path": "/net~1interface-topology", 
                                  "value": {
                                                "network": {
                                                        "bonds": {
                                                                "bond0": {}
                                                        },
                                                        "ethernets": {
                                                                "eno5": {},
								"eno6": {},
								"eno7": {}
                                                        },
                                                        "vlans": {
                                                                "bond0.' . $vlan . '": {}
                                                        },
                                                        "version": 2
                                                }
                                        }
				},
				{ "op": "add",
				  "path": "/net~1interface-config",
                                  "value":  {
                                                "bond0": {
                                                        "interfaces": [
                                                                "eno6",
                                                                "eno7"
                                                        ],
                                                        "parameters": {
                                                                "mii-monitor-interval": 100,
								"mode": "802.3ad",
								"lacp-rate": "fast"
                                                        }
                                                },
                                                "bond0.' . $vlan . '": {
                                                        "addresses": [
                                                                "' . $ip . '"
                                                        ],
                                                        "gateway4": "' . $privategw . '",
                                                        "id": ' . $vlan . ',
                                                        "link": "bond0",
                                                        "nameservers": {
                                                                "addresses": [
                                                                        "8.8.8.8"
                                                                ]
                                                        }
						},
  						"eno5": {
    							"dhcp4": false
  						}
                                        }
				}
			]';

			if ($workflow == 'debian10-base' || $workflow == 'debian9-base') {
				$parampatch = '[
                                        { "op": "add", "path": "/operating-system-disk", "value": "sdb" },
                                        { "op": "add", "path": "/debian-network", "value": [
                                                  {
                                                        "Address": "' . $privateip . '",
                                                        "gateway": "' . $privategw . '",
                                                        "vlan": ' . $vlan . '
                                                  }
                                                ]
                                        }
                                        ]';
				$this->PatchMachinesParam($uuid, $parampatch);
			}
		}
		/*$jsonparam='{
                                "Validated": true,
                                "Available": true,
                                "Errors": [],
                                "ReadOnly": false,
                                "Meta": {
                                        "color": "black",
                                        "icon": "tags",
                                        "title": "User added"
                                },
                                "Endpoint": "",
                                "Bundle": "",
                                "Partial": false,
                                "Name": "'.$tenant.'-'.$hostname.'",
                                "Description": "",
                                "Documentation": "",
				"Params": {
                                        "hostname": "'.$hostname.'",
                                        "access-keys": {"user":"'.$sshkey.'"},
                                },
                                "Profiles": []
		}';*/
		//$this->CreateProfile($jsonparam);
		//$profilepatch='[{ "op": "add", "path": "/Profiles", "value": ["'.$tenant.'-'.$hostname.'"] }]';
		//$this->PatchMachinesProfiles($uuid,$profilepatch);

		$this->PatchMachinesParam($uuid, $parampatch);
		$raidparam = '[{ "op": "add", "path": "/raid-target-config", "value": 
[
  {
    "AllowMixedSizes": false,
    "Bootable": true,
    "Controller": 0,
    "Disks": [
      {
        "Enclosure": "1I:1",
        "Protocol": "sas",
        "Size": 322122547200,
        "Slot": 1,
        "Type": "disk",
        "Volume": ""
      },
      {
        "Enclosure": "1I:1",
        "Protocol": "sas",
        "Size": 322122547200,
        "Slot": 2,
        "Type": "disk",
        "Volume": ""
      }
    ],
    "Encrypt": false,
    "Name": "os",
    "Protocol": "sas",
    "RaidLevel": "raid1",
    "Size": "max",
    "StripeSize": "64 KB",
    "Type": "disk",
    "VolumeID": ""
  },
  {
    "AllowMixedSizes": false,
    "Bootable": false,
    "Controller": 0,
    "Disks": [
      {
        "Enclosure": "1I:1",
        "Protocol": "sata",
        "Size": 515396075520,
        "Slot": 3,
        "Type": "ssd",
        "Volume": ""
      },
      {
        "Enclosure": "1I:1",
        "Protocol": "sata",
        "Size": 515396075520,
        "Slot": 4,
        "Type": "ssd",
        "Volume": ""
      },
      {
        "Enclosure": "2I:1",
        "Protocol": "sata",
        "Size": 515396075520,
        "Slot": 5,
        "Type": "ssd",
        "Volume": ""
      },
      {
        "Enclosure": "2I:1",
        "Protocol": "sata",
        "Size": 515396075520,
        "Slot": 6,
        "Type": "ssd",
        "Volume": ""
      }
    ],
    "Encrypt": false,
    "Name": "data",
    "Protocol": "sata",
    "RaidLevel": "' . $raid . '",
    "Size": "max",
    "StripeSize": "128 KB",
    "Type": "ssd",
    "VolumeID": ""
  }
]       
		}]';
		$this->PatchMachinesParam($uuid, $raidparam);
		if ($workflow == 'ubuntu-20') {
			$parampatch = '[
					{ "op": "add", "path": "/access-ssh-root-mode", "value": "yes" }
                                        ]';
			$this->PatchMachinesParam($uuid, $parampatch);
		}
		if ($workflow == 'ubuntu-18') {
			$parampatch = '[
                                        { "op": "add", "path": "/operating-system-disk", "value": "sdb" }
                                        ]';
			$this->PatchMachinesParam($uuid, $parampatch);
		}
		/*if($workflow == 'debian10-base'){
                        $parampatch = '[
					{ "op": "add", "path": "/operating-system-disk", "value": "sdb" },
					{ "op": "add", "path": "/debian-network", "value": [
  						  {
    							"Address": "'.$privateip.'",
    							"gateway": "'.$privategw.'",
    							"vlan": '.$vlan.'
  						  }
						] 
					},
                                        { "op": "add", "path": "/debian-pubnetwork", "value": [
                                                  {
                                                        "Address": "'.$ip.'",
                                                        "gateway": "'.$privategw.'",
                                                        "vlan": '.$vlan.'
                                                  }
                                                ]
                                        }					
					]';
                        $this->PatchMachinesParam($uuid,$parampatch);

		}*/

		$statuswf = $this->AssignWorkflow($uuid, $workflow);
		$addnetworkinfo[0] = $networkid;
		$addnetworkinfo[1] = $privateip;
		$addnetworkinfo[2] = $uuid;
		$addnetworkinfo[3] = $pubiparr['address'];
		if ($statuswf == 200) {
			if ($networkexist == null) {
				$NetBoxController->UpdateStatusPrefix("reserved", $NBtenantid, $prefixarr['id']);
			}
			if (isset($pubipcheck)) {
				$NetBoxController->UpdateStatusPubIP("reserved", $NBtenantid, $pubiparr['id']);
			}
			$ifgroup = $BMAASDBController->GetMachineIfGroup($uuid);
			$BCFController->AddInterfaceGroup($tenant . "-baremetal", $vlan, $ifgroup->ifgroup_uplink, $user);
			$BMAASDBController->AddBMAASMachineAddr($addnetworkinfo);
			$params[0] = $uuid;
			$params[1] = $workflow;
			$params[2] = $tenantget->tenant_id;
			$params[3] = "NULL";
			$BMAASDBController->AddBMAASWF($params);
			$ILOController->CreateILOUser($ipmiaddr, $ifgroup->ipmi_console_hostname, $user);
		}
		//return redirect()->action('RackNController@GetListMachines', ['tenantval' => $tenant]);
		//exit;
		return redirect()->action('RackNController@GetListMachines');
	}

	public function ProcessWindowsOrder($request, $user)
	{
		//$user = Auth::User();
		/*$validatedData = $request->validate([
                        'adminpass' => 'required',
                        'region' => 'required',
                        'serverspek' => 'required',
                        'selectoswin' => 'required'
                ]);
		 */
		$NetBoxController = new NetBoxController;
		$BMAASDBController = new BMAASDBController;
		$BCFController = new BCFController;
		$ILOController = new ILOController;
		$tenantget = $BMAASDBController->GetTenantbyUser($user->id);
		$tenant =  $tenantget->tenant_name;
		//$tenant = request()->segment(1);
		//$hostname = $request->input('winhostname');
		$adminpass = $request->adminpass;
		//$privateip = $request->input('winprivateip');
		//$subnet = $request->input('winselectsubnet');
		$serverspek = $request->serverspek;
		$osimage = $request->selectoswin;
		$pubipcheck = $request->pubipcheckwin;
		$raid = $request->raidwin;
		//$pubiparr['address']="";
		if ($osimage == 'Win2019uefi.img.xz') {
			$template = "2019unattend.xml.tmpl";
			$path = "Windows/Panther/2019unattend.xml";
			$profilename = $tenant . "Windows19";
		} else if ($osimage == 'Win2016uefi.img.xz') {
			$template = "2016unattend.xml.tmpl";
			$path = "Windows/Panther/2016unattend.xml";
			$profilename = $tenant . "Windows16";
		}
		//$ipexplode=explode(".",$privateip);
		//$strcount=strlen($ipexplode[3]);
		//$gw=substr_replace($privateip,"254",-$strcount);
		$uuid = $this->GetAvailableMachine($serverspek);
		//echo $uuid;
		//exit;
		if (empty($uuid)) {
			return redirect("listmachines")->with('errorMessageDuration', "$serverspek Machine not Available");
		} else {
			$parampatch = '[{ "op": "add", "path": "/tenant", "value": "' . $tenant . '" }]';
			$this->PatchMachinesParam($uuid, $parampatch);
		}
		$ipmiaddr = $this->GetMachineIPMIAddr($uuid);
		//
		$tenantexist = $BMAASDBController->CheckTenantExist($tenant);
		if ($tenantexist == null) {
			//return view('order');
			return redirect("/orderpage")->with('errorMessageDuration', 'Tenant Nor Found');
		}
		$networkexist = $BMAASDBController->CheckTenantNetwork($tenant, "baremetal");
		if ($networkexist == null) {
			$tenantdb = $BMAASDBController->GetTenantID($tenant);
			$prefixarr = $NetBoxController->GetAvailablePrefix("Private");
			$tenantdb->tenant_id;
			$networkinfo[0] = $tenantdb->tenant_id;
			$networkinfo[1] = $prefixarr['id'];
			$networkinfo[2] = "baremetal";
			$networkid = $BMAASDBController->AddBMAASTenantNetwork($networkinfo);
			$prefix = explode("/", $prefixarr['prefix']);
			$sub = new SubnetCalculatorController($prefix[0], $prefix[1]);
			$privategw = $sub->getMaxHost();
			$privateip = $sub->getMinHost();
			/*$lastip = $BMAASDBController->GetLastPrivateIP($networkinfo[1]);
                        if($lastip->ip_address == null){
                                $prefix = explode("/", $prefixarr['prefix']);
                                $longprivateip=ip2long($prefix[0])+1;
                                $privateip = long2ip($longprivateip);
                        }else{
                                $longprivateip = $lastip->ip_address+1;
                                $privateip = long2ip($longprivateip);
			}*/
			//$ipexplode=explode(".",$privateip);
			//$strcount=strlen($ipexplode[3]);
			//$privategw=substr_replace($privateip,"254",-$strcount);
			$systemtenantint = $BCFController->SystemTenantInterface($user);
			$segmentint = "";
			if ($systemtenantint = 204 or $systemtenantint == 100) {
				$segment = $BCFController->CreateSegment($tenant . "-baremetal", $user);
			}
			if ($segment == 204 or $segment == 100) {
				//$BCFController->AddInterfaceGroup($prefixarr['vlan']);
				$segmentint = $BCFController->CreateSegmentInterface($tenant . "-baremetal", $user);
			}
			if ($segmentint == 204 or $segmentint == 100) {
				$segmentintip = $BCFController->CreateSegmentInterfaceIP($privategw, $tenant . "-baremetal", $user);
				$BCFController->ConfigureStaticRoute($prefixarr['prefix'], $user);
			}
		} else {
			$networkinfo = $BMAASDBController->GetTenantNetworkInfo($tenant, "baremetal");
			$networkid = $networkinfo->id;
			$prefixarr = $NetBoxController->GetPrivatePrefixDetail($networkinfo->netbox_prefix_id);
			$lastip = $BMAASDBController->GetLastPrivateIP($networkinfo->netbox_prefix_id);
			if ($lastip->ip_address == null) {
				$prefix = explode("/", $prefixarr['prefix']);
				$longprivateip = ip2long($prefix[0]) + 1;
				$privateip = long2ip($longprivateip);
			} else {
				$longprivateip = $lastip->ip_address + 1;
				$privateip = long2ip($longprivateip);
			}
			$ipexplode = explode(".", $privateip);
			$strcount = strlen($ipexplode[3]);
			$privategw = substr_replace($privateip, "254", -$strcount);
		}
		$NBtenantid = $NetBoxController->GetNBTenantid($tenant);
		//$prefix = explode("/", $prefixarr['prefix']);
		//$privateip=$prefix[0];
		//$subnet="24";
		//$ip=$privateip."/24";
		$vlan = $prefixarr['vlan'];

		$jsonparam = '{
                                "Validated": true,
                                "Available": true,
                                "Errors": [],
                                "ReadOnly": false,
                                "Meta": {
                                        "color": "black",
                                        "icon": "tags",
                                        "title": "User added"
                                },
                                "Endpoint": "",
                                "Bundle": "",
                                "Partial": false,
                                "Name": "' . $profilename . '",
                                "Description": "",
                                "Documentation": "",
                                "Params": {
					"image-deploy/windows-unattend-template": "' . $template . '",
					"image-deploy/windows-unattend-path": "' . $path . '",
					"image-deploy/use-cloud-init": true,
					"image-deploy/image-type": "dd-xz",
					"image-deploy/image-os": "windows",
					"image-deploy/image-file": "isos/' . $osimage . '"

                                },
                                "Profiles": []
		}';
		$this->CreateProfile($jsonparam);
		//$uuid = $this->GetAvailableMachine($serverspek);
		$profilepatch = '[{ "op": "add", "path": "/Profiles", "value": ["' . $profilename . '"] }]';
		$this->PatchMachinesProfiles($uuid, $profilepatch);
		if (isset($pubipcheck)) {

			$pubiparr = $NetBoxController->GetAvailablePubIP();
			$prefixval = $NetBoxController->GetPrefixFromIPAdd($pubiparr['address']);
			$ipexplode = explode("/", $pubiparr['address']);
			$parampatch = '[
                                { "op": "add", "path": "/tenant", "value": "' . $tenant . '" },
				{ "op": "add", "path": "/image-deploy~1admin-password", "value": "' . $adminpass . '" },
                                { "op": "add", "path": "/windows~1private-network", "value":   {
                                                "IP": "' . $privateip . '",
                                                "Gateway": "' . $privategw . '",
                                                "VLan": ' . $vlan . '
                                        }
				},
                                { "op": "add", "path": "/windows~1public-network", "value":   {
                                                "IP": "' . $ipexplode[0] . '",
                                                "Gateway": "' . $prefixval['gateway'] . '",
						"VLan": ' . $prefixval['vlan'] . ',
						"PrefixMask":' . $ipexplode[1] . '
                                        }
                                }
                               ]';
		} else {
			$pubiparr['address'] = "";
			$parampatch = '[
				{ "op": "add", "path": "/tenant", "value": "' . $tenant . '" },
				{ "op": "add", "path": "/image-deploy~1admin-password", "value": "' . $adminpass . '" },
				{ "op": "add", "path": "/windows~1private-network", "value":   {
                                        	"IP": "' . $privateip . '",
						"Gateway": "' . $privategw . '",
						"VLan": ' . $vlan . '
					}
				}
			       ]';
		}
		$this->PatchMachinesParam($uuid, $parampatch);
		$raidparam = '[{ "op": "add", "path": "/raid-target-config", "value":  
[
  {
    "AllowMixedSizes": false,
    "Bootable": true,
    "Controller": 0,
    "Disks": [
      {
        "Enclosure": "1I:1",
        "Protocol": "sas",
        "Size": 322122547200,
        "Slot": 1,
        "Type": "disk",
        "Volume": ""
      },
      {
        "Enclosure": "1I:1",
        "Protocol": "sas",
        "Size": 322122547200,
        "Slot": 2,
        "Type": "disk",
        "Volume": ""
      }
    ],
    "Encrypt": false,
    "Name": "os",
    "Protocol": "sas",
    "RaidLevel": "raid1",
    "Size": "max",
    "StripeSize": "64 KB",
    "Type": "disk",
    "VolumeID": ""
  },
  {
    "AllowMixedSizes": false,
    "Bootable": false,
    "Controller": 0,
    "Disks": [
      {
        "Enclosure": "1I:1",
        "Protocol": "sata",
        "Size": 515396075520,
        "Slot": 3,
        "Type": "ssd",
        "Volume": ""
      },
      {
        "Enclosure": "1I:1",
        "Protocol": "sata",
        "Size": 515396075520,
        "Slot": 4,
        "Type": "ssd",
        "Volume": ""
      },
      {
        "Enclosure": "2I:1",
        "Protocol": "sata",
        "Size": 515396075520,
        "Slot": 5,
        "Type": "ssd",
        "Volume": ""
      },
      {
        "Enclosure": "2I:1",
        "Protocol": "sata",
        "Size": 515396075520,
        "Slot": 6,
        "Type": "ssd",
        "Volume": ""
      }
    ],
    "Encrypt": false,
    "Name": "data",
    "Protocol": "sata",
    "RaidLevel": "' . $raid . '",
    "Size": "max",
    "StripeSize": "128 KB",
    "Type": "ssd",
    "VolumeID": ""
  }
]	
		}]';
		$this->PatchMachinesParam($uuid, $raidparam);
		$statuswf = $this->AssignWorkflow($uuid, "image-deploy");
		$addnetworkinfo[0] = $networkid;
		$addnetworkinfo[1] = $privateip;
		$addnetworkinfo[2] = $uuid;
		$addnetworkinfo[3] = $pubiparr['address'];
		if ($statuswf == 200) {
			if ($networkexist == null) {
				$NetBoxController->UpdateStatusPrefix("reserved", $NBtenantid, $prefixarr['id']);
			}
			if (isset($pubipcheck)) {
				$NetBoxController->UpdateStatusPubIP("reserved", $NBtenantid, $pubiparr['id']);
			}
			$ifgroup = $BMAASDBController->GetMachineIfGroup($uuid);
			$BCFController->AddInterfaceGroup($tenant . "-baremetal", $vlan, $ifgroup->ifgroup_uplink, $user);
			$BMAASDBController->AddBMAASMachineAddr($addnetworkinfo);
			$params[0] = $uuid;
			$params[1] = "image-deploy";
			$params[2] = $tenantget->tenant_id;
			$params[3] = "NULL";
			$BMAASDBController->AddBMAASWF($params);
			$ILOController->CreateILOUser($ipmiaddr, $ifgroup->ipmi_console_hostname, $user);
		}

		return redirect()->action('RackNController@GetListMachines');
	}

	public function ProcessGioPrivateJob(Request $request)
	{

		$user = Auth::User();
		$uuid = $this->GetAvailableMachine($request->serverspek);
		if (empty($uuid)) {
			return redirect("listmachines")->with('errorMessageDuration', "$request->serverspek Machine not Available");
		}
		$validatedData = $request->validate([
			'vcenterpass' => 'required',
			'esxipass' => [
            			'required',
            			'string',
            			'min:10',             // must be at least 10 characters in length
            			'regex:/[a-z]/',      // must contain at least one lowercase letter
            			'regex:/[A-Z]/',      // must contain at least one uppercase letter
            			'regex:/[0-9]/',      // must contain at least one digit
            			'regex:/[@$!%*#?&]/', // must contain a special character
        		],
			'serverspek' => 'required'
		]);

		$objectvalue = (object) array(
			'vcenterpass' => $request->vcenterpass,
			'region' => $request->region,
			'serverspek' => $request->serverspek,
			'esxipass' => $request->esxipass,
			'esxicount' => $request->esxicount,
			'stdds' => $request->stdds
		);
		$BMAASDBController = new BMAASDBController;
		//$BMAASDBController->BMAASQueue($objectvalue);
		dispatch(new DeployGioPrivateJob($objectvalue, $user));
		$tenantget = $BMAASDBController->GetTenantbyUser($user->id);
		$tenant =  $tenantget->tenant_name;
		//return redirect()->action('RackNController@GetListMachines');
		return redirect()->action('RackNController@GetListMachines')->with('PrivMessageDuration', 'Server Deployment in Progress, you will be notified when deployment has complete or check status on vSphere Tab');
	}


	public function ProcessGioPrivateOrder($request,$user)
	{
		//$tenant = "AA99999";
		//$user = Auth::User();
		$BMAASDBController = new BMAASDBController;
		$NetBoxController = new NetBoxController;
		$BCFController = new BCFController;
		$ILOController = new ILOController;
		$tenantget = $BMAASDBController->GetTenantbyUser($user->id);
		$tenant =  $tenantget->tenant_name;
		$esxihostname = "esxi01";
		$vcvlan = "300";
		$internalvlan = "4010,4011,4012,4013,4014";
		$iscsinetwork = "192.168.20.0";

		/*$vcenterpass = $request->input('vcenterpass');
		$esxipass = $request->input('esxipass');
		$serverspek = $request->input('serverspek');
		$esxicount = $request->input('esxicount');
		$datastoresize = $request->input('stdds');
		 */
		$vcenterpass = $request->vcenterpass;
		$esxipass = $request->esxipass;
		$serverspek = $request->serverspek;
		$esxicount = $request->esxicount;
		$datastoresize = $request->stdds;
		
		//$esxicount = 1;
		$ipliststr = "";
		//$esxiip = "192.168.21.1";
		//$vcenterip = "192.168.21.101";
		//$vcgateway = "192.168.21.254";
		$vccluster = $tenant . "-Cluster"; //$request->input('vccluster');
		$vcdatacenter = $tenant . "-DC"; //$request->input('vcdatacenter');
		$networkexist = $BMAASDBController->CheckTenantNetwork($tenant, "baremetal");
		if ($networkexist == null) {
			$tenantdb = $BMAASDBController->GetTenantID($tenant);
			$prefixarr = $NetBoxController->GetAvailablePrefix("Private");
			$tenantdb->tenant_id;
			$networkinfo[0] = $tenantdb->tenant_id;
			$networkinfo[1] = $prefixarr['id'];
			$networkinfo[2] = "baremetal";
			$networkid = $BMAASDBController->AddBMAASTenantNetwork($networkinfo);
			$prefix = explode("/", $prefixarr['prefix']);
			$sub = new SubnetCalculatorController($prefix[0], $prefix[1]);
			$privategw = $sub->getMaxHost();
			$privateip = $sub->getMinHost();
			$systemtenantint = $BCFController->SystemTenantInterface($user);
			$segmentint = "";
			if ($systemtenantint = 204 or $systemtenantint == 100) {
				$segment = $BCFController->CreateSegment($tenant . "-baremetal", $user);
			}
			if ($segment == 204 or $segment == 100) {
				//$BCFController->AddInterfaceGroup($prefixarr['vlan']);
				$segmentint = $BCFController->CreateSegmentInterface($tenant . "-baremetal", $user);
			}
			if ($segmentint == 204 or $segmentint == 100) {
				$segmentintip = $BCFController->CreateSegmentInterfaceIP($privategw, $tenant . "-baremetal", $user);
				$BCFController->ConfigureStaticRoute($prefixarr['prefix'], $user);
			}
		} else {
			$networkinfo = $BMAASDBController->GetTenantNetworkInfo($tenant, "baremetal");
			$networkid = $networkinfo->id;
			$prefixarr = $NetBoxController->GetPrivatePrefixDetail($networkinfo->netbox_prefix_id);
			$lastip = $BMAASDBController->GetLastPrivateIP($networkinfo->netbox_prefix_id);
			if ($lastip->ip_address == null) {
				$prefix = explode("/", $prefixarr['prefix']);
				$longprivateip = ip2long($prefix[0]) + 1;
				$privateip = long2ip($longprivateip);
			} else {
				$longprivateip = $lastip->ip_address + 1;
				$privateip = long2ip($longprivateip);
			}
			$prefix = explode("/", $prefixarr['prefix']);
			$sub = new SubnetCalculatorController($prefix[0], $prefix[1]);
			$privategw = $sub->getMaxHost();

			//$ipexplode=explode(".",$privateip);
			//$strcount=strlen($ipexplode[3]);
			//$privategw=substr_replace($privateip,"254",-$strcount);


		}
		//$esxiiplong = ip2long($privateip) + 1;
		//$esxiip = long2ip($esxiiplong);
		//$alamat = $request->input('alamat');
		$jsonparam = '{
    				"Validated": true,
    				"Available": true,
    				"Errors": [],
    				"ReadOnly": false,
    				"Meta": {
      					"color": "black",
      					"icon": "tags",
      					"title": "User added"
    				},
    				"Endpoint": "",
    				"Bundle": "",
    				"Partial": false,
    				"Name": "' . $tenant . '-Profile",
    				"Description": "",
    				"Documentation": "",
    				"Params": {
      					"gio-private/iscsi-vlan": ' . $prefixarr['vlan'] . ',
      					"tenant": "' . $tenant . '",
      					"gio-private/vcenter-vlan": ' . $prefixarr['vlan'] . ',
      					"gio-private/dns_nameserver1": "172.16.10.52",
      					"gio-private/internal-vlan": "' . $internalvlan . '",
      					"gio-private/iscsi-network": "' . $iscsinetwork . '",
      					"gio-private/nimble-volume-name": "' . $tenant . 'Vol",
      					"gio-private/nimble-volume-size": ' . $datastoresize . ',
      					"gio-private/ntp_server_ip": "172.16.10.6",
      					"gio-private/vcenter_appliance_deployment_option": "tiny",
      					"gio-private/vcenter_appliance_name": "' . $tenant . '.biznetgio.local",
      					"gio-private/vcenter_cluster": "' . $vccluster . '",
      					"gio-private/vcenter_datacenter": "' . $vcdatacenter . '",
      					"gio-private/vcenter_ip": "' . $privateip . '",
      					"gio-private/vcenter_network_gateway": "' . $privategw . '",
      					"gio-private/vcenter_network_prefix": "24",
      					"gio-private/vcenter_network_system_name": "' . $privateip . '",
      					"gio-private/vcenter_sso_domain-name": "vsphere.local",
					"gio-private/vcenter_sso_password" : "' . $vcenterpass . '",
					"gio-private/vcenter_password" : "' . $vcenterpass . '",
					"gio-private/esxi-password" : "' . $esxipass . '",
					"esxi/network-kickstart-type" : "dhcp",
					"esxi/vmnic-device" : "vmnic4",
					"esxi/welcome-customize" : false,
					"vmware/esxi-version-override" : "esxi_700-vmware-install"
    				},
    				"Profiles": []
		}';
		$this->CreateProfile($jsonparam);
		$addnetworkinfo[0] = $networkid;
		$addnetworkinfo[1] = $privateip;
		$addnetworkinfo[2] = $tenant . "-vCenter";
		$addnetworkinfo[3] = "";
		$BMAASDBController->AddBMAASMachineAddr($addnetworkinfo);
                $profilepatch = '[
                                { "op": "add", "path": "/Profiles", "value": ["' . $tenant . '-Profile"] }
                ]';

		//exit;
		//echo $jsonparam;
		$ch = curl_init();
		/*
                curl_setopt($ch, CURLOPT_URL, $this->swagger_url.'/profiles');
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_USERPWD, $this->swagger_user . ':' . $this->swagger_pass);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonparam);
                $headers = array();
		$headers[] = 'Accept: application/json';
		$headers[] = "Content-Type: application/json";
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                $result = curl_exec($ch);
                if (curl_errno($ch)) {
                        echo 'Error:' . curl_error($ch);
                } else {
                        //print_r(json_decode($result));
			//return view('listmachines',['listmachines' => json_decode($result)]);
			
		}*/

		// Get Available Machines
		//$ch = curl_init();
		//$longprivateip++;
		//$privateip = long2ip($longprivateip);

		//$this->CreateProfile($esxijsonparam);
		/*curl_setopt($ch, CURLOPT_URL, $this->swagger_url.'/'.'machines?Runnable=true&Stage=sledgehammer-wait&Available=true&BootEnv=sledgehammer&limit=1');
    		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
    		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    		curl_setopt($ch, CURLOPT_USERPWD, $this->swagger_user . ':' . $this->swagger_pass);
		$headers = array();
		$headers[] = 'Accept: application/json';
                $headers[] = "Content-Type: application/json";
    		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    		$result = curl_exec($ch);
    		if (curl_errno($ch)) {
        		echo 'Error:' . curl_error($ch);
		} else {*/
		//$tmpuuid = $this->CreateRackNMachine();
		//$this->PatchMachinesProfiles($tmpuuid, $profilepatch);
		//$this->AssignWorkflow($tmpuuid, "vSphere-Deployment");
		for ($i = 0; $i < $esxicount; $i++) {
			$uuid = $this->GetAvailableMachine($serverspek);
			$longprivateip++;
			$privateip = long2ip($longprivateip);
			//$ipliststr .= $privateip.",";


			//$value =  json_decode($result,true);
			//$uuid = $value[0]['Uuid'];

			$parampatch = '[
					{ "op": "add", "path": "/tenant", "value": "' . $tenant . '" },
					{ "op": "add", "path": "/esxi~1network-firstboot-gateway", "value": "' . $privategw . '" },
					{ "op": "add", "path": "/esxi~1network-firstboot-ipaddr", "value": "' . $privateip . '" },
					{ "op": "add", "path": "/esxi~1network-firstboot-netmask", "value": "255.255.255.0" }
					]';
			/*$profilepatch = '[
				{ "op": "add", "path": "/Profiles", "value": ["' . $tenant . '-Profile"] }	
			]';*/
			$this->PatchMachinesParam($uuid, $parampatch);
			$this->PatchMachinesProfiles($uuid, $profilepatch);
			//if ($i == 0) {
			//	$this->AssignWorkflow($uuid, "Esxi-Deployment");
			//} else {
				$this->AssignWorkflow($uuid, "Esxi-Deployment");
			//}
			//$this->AssignWorkflow($uuid,"vcsa-deployment-workflow");
			$ifgroup = $BMAASDBController->GetMachineIfGroup($uuid);
			$BCFController->AddInterfaceGroup($tenant . "-baremetal", $prefixarr['vlan'], "CVC001", $user);
			$BCFController->AddInterfaceGroup($tenant . "-baremetal", $prefixarr['vlan'], $ifgroup->ifgroup_uplink, $user);
			$BCFController->ChangeInterfaceGroupMode($ifgroup->ifgroup_uplink, "static");
			$addnetworkinfo[0] = $networkid;
			$addnetworkinfo[1] = $privateip;
			$addnetworkinfo[2] = $uuid;
			$addnetworkinfo[3] = "";
			$BMAASDBController->AddBMAASMachineAddr($addnetworkinfo);
			$params[0] = $uuid;
			$params[1] = "Esxi-Deployment";
			$params[2] = $tenantget->tenant_id;
			$params[3] = "NULL";
			$BMAASDBController->AddBMAASWF($params);
			$ipmiaddr = $this->GetMachineIPMIAddr($uuid);
			$ILOController->CreateILOUser($ipmiaddr, $ifgroup->ipmi_console_hostname, $user);
		}
                $tmpuuid = $this->CreateRackNMachine();
                $this->PatchMachinesProfiles($tmpuuid, $profilepatch);
                $this->AssignWorkflow($tmpuuid, "vSphere-Deployment");		
		$checkvpn = $BMAASDBController->CheckTenantVPN($user->tenant_id);
		if (is_null($checkvpn)) {
			echo "no VPN";
			$password = $ILOController->password_generate(12);
			$VPNController = new VPNController;
			$VPNController->CreateGroup($tenant);
			$VPNController->CreateUser($tenant, $password, $tenant, $user);
			$VPNController->CreateACL($tenant, $tenant, $prefixarr['prefix']);
			$info[0] = $user->tenant_id;
			$info[1] = $networkid;
			$BMAASDBController->AddBMAASVPN($info);
		} else {
			echo "ada von";
		}
		//$iplists = substr($ipliststr, 0, -1);
		//$profileparam='[
		//                { "op": "add", "path": "/gio-private~1esxi-list", "value": "'.$iplists.'" }
		//        ]';

		//$this->PatchProfileParam($tenant."-Profile",$profileparam);

		//return $result;
		//}
		//curl_close ($ch);
		//$this->CreateDeploymentVM("ESXi","",$user);
		return redirect()->action('RackNController@GetListMachines');
	}

	public function GetVCToken()
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $this->vcsessionurl . '/com/vmware/cis/session');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_USERPWD, $this->vcmnguser . ':' . $this->vcmngpass);
		$headers = array();
		$headers[] = 'Accept: application/json';
		$headers[] = "Content-Type: application/json";
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		$result = curl_exec($ch);
		if (curl_errno($ch)) {
			echo 'Error:' . curl_error($ch);
		} else {
			$val = json_decode($result);
			return $val->value;
		}
		curl_close($ch);
	}

	public function CreateDeploymentVM($deploymenttype, $parampatch, $user, $clustername)
	{
		//$tenant = request()->segment(1);
		//$user = Auth::User();
		$BMAASDBController = new BMAASDBController;
		$tenantget = $BMAASDBController->GetTenantbyUser($user->id);
		$tenant =  $tenantget->tenant_name;
		//echo is_array($parampatch);
		if (is_array($parampatch) == 1) {
			$count = count($parampatch);
		} else {
			$count = 1;
		}
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $this->vcsessionurl . '/com/vmware/cis/session');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_USERPWD, $this->vcmnguser . ':' . $this->vcmngpass);
		$headers = array();
		$headers[] = 'Accept: application/json';
		$headers[] = "Content-Type: application/json";
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		$result = curl_exec($ch);
		if (curl_errno($ch)) {
			echo 'Error:' . curl_error($ch);
		} else {
			$val = json_decode($result);
			$token = $val->value;
		}
		curl_close($ch);
		// Create Vm in VC MNG
		$vmjson = '{
    "spec": {
        "guest_OS": "RHEL_7_64",
        "placement" : {
            "datastore": "datastore-474",
            "folder": "group-v9",
            "resource_pool": "resgroup-284"
        },
        "memory": {
            "hot_add_enabled": true,
            "size_MiB": 8192
        },
        "cpu": {
            "cores_per_socket": 2,
            "count": 2
        },
        "nics": [
            {
                "backing": {
                    "network": "network-231",
                    "type": "STANDARD_PORTGROUP"
                },
                "start_connected": true
	    },
            {
                "backing": {
                    "network": "network-336",
                    "type": "STANDARD_PORTGROUP"
                },
                "start_connected": true
            },
            {
                "backing": {
                    "network": "network-336",
                    "type": "STANDARD_PORTGROUP"
                },
                "start_connected": true
            }

	],
	"disks": [
	     {
        	"new_vmdk": {
          		"capacity": 42949672960
        	}		
	     }
	]
    }
}
';
		for ($i = 0; $i < $count; $i++) {
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $this->vcsessionurl . '/vcenter/vm');
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $vmjson);
			//curl_setopt($ch, CURLOPT_USERPWD, $this->vcmnguser . ':' . $this->vcmngpass);
			$headers = array();
			$headers[] = 'Accept: application/json';
			$headers[] = "Content-Type: application/json";
			$headers[] = "vmware-api-session-id: $token";
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
			$result = curl_exec($ch);
			if (curl_errno($ch)) {
				echo 'Error:' . curl_error($ch);
			} else {
				$val = json_decode($result);
				//print_r($val);
				$vmid[$i] = $val->value;
			}
			curl_close($ch);
			sleep(1);
		}
		sleep(10);
		//$curl = curl_init();

		for ($i = 0; $i < $count; $i++) {
			//echo "https://vc-mng.biznetgio.local/rest/vcenter/vm/".$vmid[$i];
			//exit;
			$id = $vmid[$i];
			$curl = curl_init();
			curl_setopt_array($curl, array(
				CURLOPT_URL => "https://vc-mng.biznetgio.local/rest/vcenter/vm/$id",
				CURLOPT_RETURNTRANSFER => true,
				//CURLOPT_ENCODING => "",
				CURLOPT_MAXREDIRS => 10,
				CURLOPT_TIMEOUT => 30,
				CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
				CURLOPT_CUSTOMREQUEST => "GET",
				//CURLOPT_POSTFIELDS => "",
				CURLOPT_SSL_VERIFYHOST => 0,
				CURLOPT_SSL_VERIFYPEER => 0,
				CURLOPT_HTTPHEADER => array(
					"Accept: application/json",
					//"Authorization: Basic YWRtaW5pc3RyYXRvckB2c3BoZXJlLmxvY2FsOjRkeTBAcG1S",
					"Content-Type: application/json",
					//"Postman-Token: 09557e3b-8156-41cd-a3a8-63fd5ef48f79",
					//"cache-control: no-cache",
					"vmware-api-session-id: $token"
				)
			));

			$response = curl_exec($curl);
			$err = curl_error($curl);

			curl_close($curl);

			if ($err) {
				echo "cURL Error #:" . $err;
			} else {
				//echo $response;
				$val = json_decode($response);
				$vmname[$i] = $val->value->name;
			}
		}
		//Power On VM
		//
		//
		for ($i = 0; $i < $count; $i++) {
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $this->vcsessionurl . "/vcenter/vm/" . $vmid[$i] . "/power/start");
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
			//curl_setopt($ch, CURLOPT_POSTFIELDS, $vmjson);
			//curl_setopt($ch, CURLOPT_USERPWD, $this->vcmnguser . ':' . $this->vcmngpass);
			$headers = array();
			//$headers[] = 'Accept: application/xml';
			$headers[] = "Content-Type: application/xml";
			$headers[] = "vmware-api-session-id: $token";
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
			$result = curl_exec($ch);
			if (curl_errno($ch)) {
				echo 'Error:' . curl_error($ch);
			} else {
				//echo $result;
			}
			curl_close($ch);
			sleep(1);
		}
		sleep(10);
		// Get VM Detail MAC Address
		for ($i = 0; $i < $count; $i++) {
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $this->vcsessionurl . "/vcenter/vm/" . $vmid[$i]);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
			$headers = array();
			$headers[] = "Content-Type: application/xml";
			$headers[] = "vmware-api-session-id: $token";
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
			$result = curl_exec($ch);
			if (curl_errno($ch)) {
				echo 'Error:' . curl_error($ch);
			} else {
				$vm = json_decode($result, true);
				$vmresult = $vm['value']['nics'][0]['value']['mac_address'];
				$macval = str_replace(':', '-', $vmresult);
				$vmmaccadd[$i] = "01-$macval";
				//echo $vmmaccadd;

			}
			curl_close($ch);
			sleep(1);
		}
		// Assign Tenant Param to new Created VM
		//
		//
		sleep(120);
		for ($i = 0; $i < $count; $i++) {
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $this->swagger_url . '/' . "machines?last-boot-macaddr=" . $vmmaccadd[$i]);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
			//curl_setopt($ch, CURLOPT_POSTFIELDS, $vmjson);
			curl_setopt($ch, CURLOPT_USERPWD, $this->swagger_user . ':' . $this->swagger_pass);
			$headers = array();
			//$headers[] = 'Accept: application/xml';
			$headers[] = "Content-Type: application/json";
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
			$result = curl_exec($ch);
			if (curl_errno($ch)) {
				echo 'Error:' . curl_error($ch);
			} else {
				$value =  json_decode($result, true);
				//print_r($value);

				$uuid = $value[0]['Uuid'];
				//echo $uuid;
				//--------------------------------
				if ($deploymenttype == "ESXi") {
					$parampatch = '[{ "op": "add", "path": "/tenant", "value": "' . $tenant . '" }]';
					$this->PatchMachinesParam($uuid, $parampatch);
					$profilesparam = '[{ "op": "add", "path": "/Profiles", "value": ["' . $tenant . '-Profile"] }]';
					$this->PatchMachinesProfiles($uuid, $profilesparam);
					return $this->AssignWorkflow($uuid, "vcsa-deployment-workflow");
				} elseif ($deploymenttype == "kubha") {
					$paramvmname = '[
						{ "op": "add", "path": "/krib~1vm-name", "value": "' . $vmid[$i] . '"  }
					]';
					$this->PatchMachinesParam($uuid, $parampatch[$i]);
					$this->PatchMachinesParam($uuid, $paramvmname);
					$profileparam = '[{ "op": "add", "path": "/Profiles", "value": ["' . $clustername . '"] }]';
					$this->PatchMachinesProfiles($uuid, $profileparam);
					$params[0] = $uuid;
					$params[1] = "kub-install-cluster";
					$params[2] = $tenantget->tenant_id;
					$params[3] = 1;
					$BMAASDBController->AddBMAASWF($params);

					$returninfo[$i]['httpcode'] = $this->AssignWorkflow($uuid, "kub-install-cluster");
					$returninfo[$i]['machineuuid'] = $uuid;
					//return $returninfo;
				}
				//-------------------------------
			}

			// Patch Profiles
			//$profilesparam = '[{ "op": "add", "path": "/Profiles", "value": ["'.$this->tenant.'-Profile"] }]';
			//$this->PatchMachinesProfiles($uuid,$profilesparam);
			//$profilesparam = '[{ "op": "add", "path": "/Profiles", "value": ["'.$this->tenant.'-ESXI01","'.$this->tenant.'-Profile"] }]';
			//$this->PatchMachinesProfiles($uuid,$profilesparam);
			// End Patch Profiles
			curl_close($ch);
		}
		return $returninfo;
		//return redirect()->action('RackNController@GetListMachines', ['tenantval' => $this->tenant]);
	}
	public function PatchMachinesParam($uuid, $parampatch)
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $this->swagger_url . '/' . "machines/$uuid/params");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $parampatch);
		curl_setopt($ch, CURLOPT_USERPWD, $this->swagger_user . ':' . $this->swagger_pass);
		$headers = array();
		$headers[] = "Content-Type: application/json";
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		$result = curl_exec($ch);
		if (curl_errno($ch)) {
			echo 'Error:' . curl_error($ch);
		} else {
			//echo $result;
		}
		curl_close($ch);
	}

	public function GetMachineIPMIAddr($uuid)
	{
		//echo $uuid;
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $this->swagger_url . '/' . "machines/$uuid/params?params=ipmi%2Faddress");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_USERPWD, $this->swagger_user . ':' . $this->swagger_pass);
		$headers = array();
		$headers[] = "Content-Type: application/json";
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		$result = curl_exec($ch);
		$resultrep = str_replace('ipmi/address', 'ipmiaddress', $result);
		if (curl_errno($ch)) {
			echo 'Error:' . curl_error($ch);
		} else {
			$value = json_decode($resultrep);
			return $value->ipmiaddress;
		}
		curl_close($ch);
		//print_r($value);
	}

	public function PatchUEFIBoot($ipmiaddr, $uuid)
	{
		$BMAASDBController = new BMAASDBController();
		$machine = $BMAASDBController->GetBootDetail($uuid);
		$bootparam = $machine->boot_detail;
		//echo $bootparam;
		//exit;
		$curl = curl_init();

		curl_setopt_array($curl, array(
			CURLOPT_URL => "https://$ipmiaddr/redfish/v1/systems/1/bios/boot/settings/",
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => "",
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 30,
			CURLOPT_SSL_VERIFYHOST => 0,
			CURLOPT_SSL_VERIFYPEER => 0,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => "PATCH",
			CURLOPT_POSTFIELDS => $bootparam,
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
			//echo $response;

		}
	}

	public function ResetServer($ipmiaddr)
	{
		$curl = curl_init();

		curl_setopt_array($curl, array(
			CURLOPT_URL => "https://$ipmiaddr/redfish/v1/Systems/1/Actions/ComputerSystem.Reset",
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => "",
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 30,
			CURLOPT_SSL_VERIFYHOST => 0,
			CURLOPT_SSL_VERIFYPEER => 0,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => "POST",
			CURLOPT_POSTFIELDS => "{\r\n    \"ResetType\": \"ForceRestart\"\r\n}",
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
			//echo $response;
		}
	}

	public function PatchMachinesProfiles($uuid, $parampatch)
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $this->swagger_url . '/' . "machines/$uuid");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $parampatch);
		curl_setopt($ch, CURLOPT_USERPWD, $this->swagger_user . ':' . $this->swagger_pass);
		$headers = array();
		$headers[] = "Content-Type: application/json";
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		$result = curl_exec($ch);
		if (curl_errno($ch)) {
			echo 'Error:' . curl_error($ch);
		} else {
			//echo $result;
		}
		curl_close($ch);
	}

	public function GetMachinesFromProfiles($profile, $masterstat)
	{
		$master = "";
		if ($masterstat == true) {
			$master = "&krib%2Fi-am-master=true";
		}
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $this->swagger_url . '/' . "machines?Profiles=$profile$master");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_USERPWD, $this->swagger_user . ':' . $this->swagger_pass);
		$headers = array();
		$headers[] = 'Accept: application/json';
		$headers[] = "Content-Type: application/json";
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		$result = curl_exec($ch);
		$resultstr = str_replace("krib/vm-name", "vmname", $result);
		//echo $result;
		if (curl_errno($ch)) {
			echo 'Error:' . curl_error($ch);
		} else {
			$value = json_decode($resultstr);
			return $value;
		}
		curl_close($ch);
	}

	public function DeleteVMfromDRP($uuid)
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $this->swagger_url . '/' . "machines/$uuid");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_USERPWD, $this->swagger_user . ':' . $this->swagger_pass);
		$headers = array();
		$headers[] = 'Accept: application/json';
		$headers[] = "Content-Type: application/json";
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		$result = curl_exec($ch);
		//echo $result;
		if (curl_errno($ch)) {
			echo 'Error:' . curl_error($ch);
		} else {
		}
		curl_close($ch);
	}

	public function DeleteCertDRP($certname)
	{

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $this->swagger_url . "/plugins/certs/actions/deleteroot");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_POSTFIELDS, '{"certs/root":"' . $certname . '"}');
		curl_setopt($ch, CURLOPT_USERPWD, $this->swagger_user . ':' . $this->swagger_pass);
		$headers = array();
		$headers[] = 'Accept: application/json';
		$headers[] = "Content-Type: application/json";
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		$result = curl_exec($ch);
		//echo $result;
		if (curl_errno($ch)) {
			echo 'Error:' . curl_error($ch);
		} else {
		}
		curl_close($ch);
	}

	public function PowerVM($vmid, $powerstat, $token)
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $this->vcsessionurl . "/vcenter/vm/$vmid/power/$powerstat");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		//curl_setopt($ch, CURLOPT_POSTFIELDS, $vmjson);
		//curl_setopt($ch, CURLOPT_USERPWD, $this->vcmnguser . ':' . $this->vcmngpass);
		$headers = array();
		//$headers[] = 'Accept: application/xml';
		$headers[] = "Content-Type: application/xml";
		$headers[] = "vmware-api-session-id: $token";
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		$result = curl_exec($ch);
		if (curl_errno($ch)) {
			echo 'Error:' . curl_error($ch);
		} else {
			echo $result;
		}
		curl_close($ch);
		sleep(1);
	}

	public function DeleteMasterVM($vmid, $token)
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $this->vcsessionurl . "/vcenter/vm/$vmid");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		//curl_setopt($ch, CURLOPT_POSTFIELDS, $vmjson);
		//curl_setopt($ch, CURLOPT_USERPWD, $this->vcmnguser . ':' . $this->vcmngpass);
		$headers = array();
		//$headers[] = 'Accept: application/xml';
		$headers[] = "Content-Type: application/xml";
		$headers[] = "vmware-api-session-id: $token";
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		$result = curl_exec($ch);
		if (curl_errno($ch)) {
			echo 'Error:' . curl_error($ch);
		} else {
			//echo $result;
		}
		curl_close($ch);
		sleep(1);
	}


	public function GetMachinesWF($uuid, $data)
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $this->swagger_url . '/' . "machines/$uuid");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_USERPWD, $this->swagger_user . ':' . $this->swagger_pass);
		$headers = array();
		$headers[] = "Content-Type: application/json";
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		$result = curl_exec($ch);
		if (curl_errno($ch)) {
			echo 'Error:' . curl_error($ch);
		} else {
			$value = json_decode($result);
			return $value->$data;
		}
		curl_close($ch);
	}

	public function AssignWorkflow($uuid, $wfparam)
	{
		$parampatch = '[{ "op": "add", "path": "/Workflow", "value": "' . $wfparam . '" }]';
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $this->swagger_url . '/' . "machines/$uuid");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HEADER, true);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $parampatch);
		curl_setopt($ch, CURLOPT_USERPWD, $this->swagger_user . ':' . $this->swagger_pass);
		$headers = array();
		$headers[] = "Content-Type: application/json";
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		$result = curl_exec($ch);
		$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		if (curl_errno($ch)) {
			echo 'Error:' . curl_error($ch);
		} else {
			return $httpcode;
		}
		curl_close($ch);
	}

	public function PatchProfileParam($profilename, $profileparam)
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $this->swagger_url . "/profiles/$profilename/params");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_USERPWD, $this->swagger_user . ':' . $this->swagger_pass);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $profileparam);
		$headers = array();
		$headers[] = 'Accept: application/json';
		$headers[] = "Content-Type: application/json";
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		$result = curl_exec($ch);

		if (curl_errno($ch)) {
			echo 'Error:' . curl_error($ch);
		} else {
			//echo $result;

			//print_r(json_decode($result));
			//return view('listmachines',['listmachines' => json_decode($result)]);

		}
		curl_close($ch);
	}

	public function CreateProfile($jsonparam)
	{
		//echo $jsonparam;
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $this->swagger_url . '/profiles');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_USERPWD, $this->swagger_user . ':' . $this->swagger_pass);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonparam);
		$headers = array();
		$headers[] = 'Accept: application/json';
		$headers[] = "Content-Type: application/json";
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		$result = curl_exec($ch);

		if (curl_errno($ch)) {
			echo 'Error:' . curl_error($ch);
		} else {
			//echo $result;
			//print_r(json_decode($result));
			//return view('listmachines',['listmachines' => json_decode($result)]);

		}
		curl_close($ch);
	}

	public function DeleteProfiles($profilename)
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $this->swagger_url . "/profiles/$profilename");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_USERPWD, $this->swagger_user . ':' . $this->swagger_pass);
		$headers = array();
		$headers[] = 'Accept: application/json';
		$headers[] = "Content-Type: application/json";
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		$result = curl_exec($ch);
		echo $result;
		if (curl_errno($ch)) {
			echo 'Error:' . curl_error($ch);
		} else {
			//print_r(json_decode($result));
			//return view('listmachines',['listmachines' => json_decode($result)]);

		}
		curl_close($ch);
	}

	public function GetAvailableMachine($serverspek)
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $this->swagger_url . '/' . 'machines?Runnable=true&Stage=sledgehammer-wait&Available=true&BootEnv=sledgehammer&limit=1&serverspec=' . $serverspek);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_USERPWD, $this->swagger_user . ':' . $this->swagger_pass);
		$headers = array();
		$headers[] = 'Accept: application/json';
		$headers[] = "Content-Type: application/json";
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		$result = curl_exec($ch);
		if (curl_errno($ch)) {
			echo 'Error:' . curl_error($ch);
		} else {
			$value =  json_decode($result, true);
			$count = count($value);
			if ($count > 0) {
				$uuid = $value[0]['Uuid'];
				return $uuid;
			} else {
				return 0;
			}
		}
		curl_close($ch);
	}

	public function DeleteVC()
	{
		$token = $this->GetVCToken();
		$user = Auth::User();
		$BMAASDBController = new BMAASDBController;
		$tenantget = $BMAASDBController->GetTenantbyUser($user->id);
		$tenant =  $tenantget->tenant_name;
		$vmid = $this->GetvCenterVMId($token, $tenant . ".biznetgio.local");
		$this->PowerVM($vmid, "stop", $token);
		$this->DeleteMasterVM($vmid, $token);
	}

	public function GetvCenterVMId($token, $vmname)
	{

		$curl = curl_init();
		//echo $vmname;
		curl_setopt_array($curl, array(
			CURLOPT_URL => "https://vc-mng.biznetgio.local/rest/vcenter/vm?filter.names=$vmname",
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => "",
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 30,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => "GET",
			CURLOPT_SSL_VERIFYHOST => 0,
			CURLOPT_SSL_VERIFYPEER => 0,
			CURLOPT_HTTPHEADER => array(
				"Content-Type: application/json",
				"vmware-api-session-id: $token"
			),
		));

		$response = curl_exec($curl);
		$err = curl_error($curl);

		curl_close($curl);

		if ($err) {
			echo "cURL Error #:" . $err;
		} else {
			//echo $response;
			//exit;
			$value = json_decode($response);
			return $value->value[0]->vm;
		}
	}

	public function strleft($str, $separator)
	{
		if (intval($separator)) {
			return substr($str, 0, $separator);
		} elseif ($separator === 0) {
			return $str;
		} else {
			$strpos = strpos($str, $separator);

			if ($strpos === false) {
				return $str;
			} else {
				return substr($str, 0, $strpos);
			}
		}
	}
	public function strright($str, $separator)
	{
		if (intval($separator)) {
			return substr($str, -$separator);
			echo "1";
		} elseif ($separator === 0) {
			return $str;
			echo "2";
		} else {
			$strpos = strpos($str, $separator);
			echo "3";
			if ($strpos === false) {
				return $str;
				echo "4";
			} else {
				return substr($str, -$strpos + 1);
				echo "5";
			}
		}
	}
}
