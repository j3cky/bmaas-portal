<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\BCFController;
use App\Http\Controllers\NetBoxController;

//use App\Http\Controllers\SubnetCalculatorController;

class BMAASDBController extends Controller
{
    /*public function index()
    {
	    //$users = DB::table('tenants')->get();
	    $sub = new SubnetCalculatorController('103.93.128.195', 28);
	    $address_range          = $sub->getIPAddressRange();
	    $network = $sub->getNetworkPortion();
	    $gw = $sub->getMaxHost();
	    print_r ($address_range);
	    echo $gw;
 	//$sub = new IPv4\SubnetCalculator('192.168.112.203', 23);
    }*/
	    public function __construct()
    	{
        	$this->middleware('auth');
    	}


	public function CheckTenantExist($tenant){
		return DB::table('tenant')->where('tenant_name', $tenant)->exists();
	}
        public function GetTenantID($tenant){
                return DB::table('tenant')->where('tenant_name', $tenant)->first();
	}
        public function GetMachineIfGroup($uuid){
                return DB::table('machine_detail')->where('machines_uuid', $uuid)->first();
        }
        public function GetMachinePubIPId($uuid){
		return DB::table('machine_public_network_info')
			->where('machine_uuid', $uuid)
			->where('destroyed', NULL)
			->first();
	}
        public function GetPrefixId($uuid){
                return DB::table('public_network_info')
                        ->where('machines_uuid', $uuid)
                        ->where('destroyed', NULL)
                        ->first();
        }


	public function CheckTenantNetwork($tenant,$type){
		return DB::table('tenant')
				->join('network_info', 'tenant.tenant_id', '=', 'network_info.tenant_id')
                                ->where([
                                        ['tenant.tenant_name', $tenant],
					['network_info.type', $type],
					['network_info.destroyed', NULL]
                                ])
            			->exists();
                //return DB::table('network_info')->where('tenant_id', $tenant)->exists();
	}

        public function CheckTenantPublicNetwork($tenant,$type){
                return DB::table('tenant')
                                ->join('public_network_info', 'tenant.tenant_id', '=', 'public_network_info.tenant_id')
                                ->where([
                                        ['tenant.tenant_name', $tenant],
					['public_network_info.type', $type],
					['public_network_info.destroyed', NULL]
                                ])
                                ->exists();
                //return DB::table('network_info')->where('tenant_id', $tenant)->exists();
        }


        public function GetTenantNetworkInfo($tenant,$type){
                return DB::table('tenant')
                                ->join('network_info', 'tenant.tenant_id', '=', 'network_info.tenant_id')
				->where([
					['tenant.tenant_name', $tenant],
					['network_info.type', $type],
					['network_info.destroyed', NULL]
				])
                                ->first();
                //return DB::table('network_info')->where('tenant_id', $tenant)->exists();
	}

        public function GetTenantPublicNetworkInfo($tenant,$type){
                return DB::table('tenant')
                                ->join('public_network_info', 'tenant.tenant_id', '=', 'public_network_info.tenant_id')
                                ->where([
                                        ['tenant.tenant_name', $tenant],
					['public_network_info.type', $type],
					['public_network_info.destroyed', NULL]
                                ])
                                ->first();
                //return DB::table('network_info')->where('tenant_id', $tenant)->exists();
        }


        public function GetLastPrivateIP($prefixid){
		return DB::table('network_info')
				->select(DB::raw('MAX(INET_ATON(machine_network_info.ip_address)) as ip_address'))
				->join('machine_network_info', 'machine_network_info.network_info_id', '=', 'network_info.id')
				->where('network_info.netbox_prefix_id',$prefixid)
				->where('network_info.destroyed',NULL)
				->where('machine_network_info.destroyed',NULL)
                                ->first();
                //return DB::table('network_info')->where('tenant_id', $tenant)->exists();
	}

	public function GetLastServiceId(){
                return DB::table('tenant')
                                ->select(DB::raw('MAX(tenant_name) as serviceid'))
                                ->first();

	}
        public function GetMachinesPubIp($uuid){
                return DB::table('machine_network_info')
                                ->where('machine_uuid',$uuid)
                                ->where('destroyed',NULL)
                                ->first();
                //return DB::table('network_info')->where('tenant_id', $tenant)->exists();
	}

        public function CheckUserSubscribe($user){
                return DB::table('users')
                                ->where('id',$user->id)
                                ->first();
                //return DB::table('network_info')->where('tenant_id', $tenant)->exists();
        }	
	

        public function GetLastPublicIP($prefixid){
                return DB::table('public_network_info')
                                ->select(DB::raw('MAX(INET_ATON(machine_public_network_info.public_address)) as public_address'))
                                ->join('machine_public_network_info', 'machine_public_network_info.public_network_info_id', '=', 'public_network_info.id')
                                ->where('public_network_info.netbox_prefix_id',$prefixid)
                                ->first();
        }

        /*Public function CreateTenantBMAAS($tenantname){
    //            DB::table('tenant')->insertGetId([
    //                    ['tenant_name' => $tenantname]
                ]);
	}*/
	Public function CreateTenantBMAAS($serviceid){
		$NetBoxController = new NetBoxController;
		$BCFController = new BCFController;
		//$tenantname = $request->input('tenantname');
		$BCFController->CreateTenant($serviceid);
		$NetBoxController->CreateNBTenant($serviceid);
		DB::table('tenant')->insert([
    			['tenant_name' => $serviceid]
		]);
		$tenants = DB::table('tenant')
				->where('tenant_name',$serviceid)
				->first();

		return $tenant;
	}
        Public function AddBMAASTenantNetwork($networkinfo){
                return DB::table('network_info')->insertGetId(
			['tenant_id' => $networkinfo[0],'netbox_prefix_id' => $networkinfo[1],'type' => $networkinfo[2]]
                );
        }
        Public function AddBMAASMachineAddr($networkinfo){
                DB::table('machine_network_info')->insert(
                        ['network_info_id' => $networkinfo[0],'ip_address' => "$networkinfo[1]",'machine_uuid' => "$networkinfo[2]", 'public_ip' => "$networkinfo[3]"]
                );
	}

        Public function BMAASQueue($request){
                DB::table('testqueue')->insert(
                        ['queue_name' => "$request->name",'queue_pass' => "$request->pass"]
                );
        }	



	Public function RemoveBMAASMachineAddr($uuid){
		$current_date = date('Y-m-d H:i:s');
		DB::table('machine_network_info')
			->where('machine_uuid', $uuid)
			->where('destroyed', NULL)
              		->update(['destroyed' => $current_date]);
	}

	public function GetBootDetail($uuid){
                return DB::table('machine_detail')
                                ->where('machines_uuid',$uuid)
                                ->first();

	}

	Public function SubscribeUserUpdate($serviceid,$user){
                $NetBoxController = new NetBoxController;
                $BCFController = new BCFController;
                //$tenantname = $request->input('tenantname');
                $BCFController->CreateTenant($serviceid);
                $NetBoxController->CreateNBTenant($serviceid);
                DB::table('tenant')->insert([
                        ['tenant_name' => $serviceid]
                ]);
                $tenants = DB::table('tenant')
                                ->where('tenant_name',$serviceid)
                                ->first();
		
                $current_date = date('Y-m-d H:i:s');
                DB::table('users')
                        ->where('id', $user->id)
                        ->update(['tenant_id' => $tenants->tenant_id]);
	}

	public function RemoveSSHKey($id){
                $current_date = date('Y-m-d H:i:s');
                DB::table('tenant_ssh_key')
                        ->where('id', $id)
                        ->where('destroyed', NULL)
                        ->update(['destroyed' => $current_date]);

	}	

        Public function RemoveBMAASPublicMachineAddr($uuid){
                $current_date = date('Y-m-d H:i:s');
                DB::table('machine_public_network_info')
                        ->where('machine_uuid', $uuid)
                        ->where('destroyed', NULL)
                        ->update(['destroyed' => $current_date]);
        }                        	

        Public function RemoveBMAASNetwork($tenant){
                $current_date = date('Y-m-d H:i:s');
                DB::table('network_info')
                        ->where('tenant_id', $tenant)
                        ->where('destroyed', NULL)
                        ->update(['destroyed' => $current_date]);
	}
        Public function RemoveBMAASPubNetwork($tenant){
                $current_date = date('Y-m-d H:i:s');
                DB::table('public_network_info')
                        ->where('tenant_id', $tenant)
                        ->where('destroyed', NULL)
                        ->update(['destroyed' => $current_date]);
	}
        Public function RemoveBMAASPubNetworkLBRange($profilename){
		$current_date = date('Y-m-d H:i:s');
                DB::table('machine_public_network_info')
                        ->where('machine_uuid', $profilename)
                        ->where('destroyed', NULL)
                        ->update(['destroyed' => $current_date]);
		
        }
	


        Public function AddBMAASTenantPublicNetwork($networkinfo){
                return DB::table('public_network_info')->insertGetId(
                        ['tenant_id' => $networkinfo[0],'netbox_prefix_id' => $networkinfo[1],'type' => $networkinfo[2]]
                );
        }
        Public function AddBMAASMachinePublicAddr($networkinfo){
                DB::table('machine_public_network_info')->insert(
                        ['public_network_info_id' => $networkinfo[0],'netbox_addr_id' => "$networkinfo[1]",'machine_uuid' => "$networkinfo[2]",'public_address' => $networkinfo[3]]
                );
        }


	public function GetMachinesIP($uuid){
		return  DB::table('machine_network_info')
			->select('machine_network_info.machine_uuid', 'machine_network_info.ip_address','machine_network_info.public_ip','machine_public_network_info.public_address')
			->leftJoin('machine_public_network_info', function ($join) {
				$join->on('machine_network_info.machine_uuid', '=', 'machine_public_network_info.machine_uuid')
					->whereNull('machine_public_network_info.destroyed');
					//->on('machine_public_network_info.destroyed','is',NULL);
			})
			//->leftJoin('machine_public_network_info', 'machine_network_info.machine_uuid', '=', 'machine_public_network_info.machine_uuid')
			->where('machine_network_info.machine_uuid',$uuid)
			->where('machine_network_info.destroyed',NULL)
			->first();
        }
	public function GetBMAASMachinesWF($uuid,$tenantid){
                return DB::table('machine_workflow_info')
			->where('tenant_id',$tenantid)
			->where('machine_uuid',$uuid)
                        ->where('destroyed',NULL)
                        ->first();
	}

        public function GetTenantList(){
		$tenants = DB::table('tenant')->get();
		return view('listtenant', ['tenants' => $tenants]);
	}

	public function GetSSHKey($tenant){
		//$tenant = request()->segment(1);
		return DB::table('tenant_ssh_key')
			->join('tenant', 'tenant.tenant_id', '=', 'tenant_ssh_key.tenant_id')
			->where('tenant_name',$tenant)
			->where('destroyed',NULL)
			->get();
                //return view('sshkey', ['sshkeys' => $sshkey]);
	}
        public function GetProfileLBRange($profilename){
                //$tenant = request()->segment(1);
                return DB::table('machine_public_network_info')
			->where('machine_uuid',$profilename)
			->where('destroyed', NULL)
                        ->get();
                //return view('sshkey', ['sshkeys' => $sshkey]);
        }
	

        public function GetTenantbyUser($userid){
		return DB::table('users')
			->select('users.id','users.name','tenant.tenant_id','tenant.tenant_name')
                        ->join('tenant', 'tenant.tenant_id', '=', 'users.tenant_id')
                        ->where('id',$userid)
                        ->first();
                //return view('sshkey', ['sshkeys' => $sshkey]);
        }	


        Public function AddBMAASSSHKey($sshkey){
                DB::table('tenant_ssh_key')->insert(
                        ['ssh_key_name' => $sshkey[0],'ssh_key' => "$sshkey[1]",'tenant_id' => $sshkey[2]]
                );
        }
        Public function AddBMAASWF($params){
                DB::table('machine_workflow_info')->insert(
                        ['machine_uuid' => $params[0],'workflow' => $params[1],'tenant_id' => $params[2]]
                );
	}

        Public function AddBMAASKubCluster($params){
                DB::table('tenant_kubernetes_cluster')->insert(
                        ['tenant_id' => $params[0],'profile_name' => $params[1]]
                );
	}

	Public function RemoveBMAASKubCluster($tenant_id){
                $current_date = date('Y-m-d H:i:s');
                DB::table('tenant_kubernetes_cluster')
                        ->where('tenant_id', $tenant_id)
                        ->where('destroyed', NULL)
                        ->update(['destroyed' => $current_date]);

        }
	
	Public function CheckBMAASKubCluster($tenant_id){
                return DB::table('tenant_kubernetes_cluster')
			->where('tenant_id',$tenant_id)
			->where('destroyed', NULL)
                        ->first();	
        }	

        Public function RemoveBMAASWF($uuid){
                $current_date = date('Y-m-d H:i:s');
                DB::table('machine_workflow_info')
                        ->where('machine_uuid', $uuid)
			->where('destroyed', NULL)
                        ->update(['destroyed' => $current_date]);
        }
}
