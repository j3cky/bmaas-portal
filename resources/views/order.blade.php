@section('css')
@parent

<link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
  <!-- Google Font -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700,300italic,400italic,600italic">
  <style>
    .color-palette {
      height: 35px;
      line-height: 35px;
      text-align: center;
    }

    .color-palette-set {
      margin-bottom: 15px;
    }

    .color-palette span {
      display: none;
      font-size: 12px;
    }

    .color-palette:hover span {
      display: block;
    }

    .color-palette-box h4 {
      position: absolute;
      top: 100%;
      left: 25px;
      margin-top: -40px;
      color: rgba(255, 255, 255, 0.8);
      font-size: 12px;
      display: block;
      z-index: 7;
    }



.qty .count {
    color: #000;
    display: inline-block;
    vertical-align: top;
    font-size: 25px;
    font-weight: 700;
    line-height: 30px;
    padding: 0 2px
    ;min-width: 35px;
    text-align: center;
}
.qty .plus {
    cursor: pointer;
    display: inline-block;
    vertical-align: top;
    color: white;
    width: 30px;
    height: 30px;
    font: 30px/1 Arial,sans-serif;
    text-align: center;
    border-radius: 50%;
    }
.qty .minus {
    cursor: pointer;
    display: inline-block;
    vertical-align: top;
    color: white;
    width: 30px;
    height: 30px;
    font: 30px/1 Arial,sans-serif;
    text-align: center;
    border-radius: 50%;
    background-clip: padding-box;
}
.qty .countds {
    color: #000;
    display: inline-block;
    vertical-align: top;
    font-size: 25px;
    font-weight: 700;
    line-height: 30px;
    padding: 0 2px
    ;min-width: 35px;
    text-align: center;
}
.qty .plusds {
    cursor: pointer;
    display: inline-block;
    vertical-align: top;
    color: white;
    width: 30px;
    height: 30px;
    font: 30px/1 Arial,sans-serif;
    text-align: center;
    border-radius: 50%;
    }
.qty .minusds {
    cursor: pointer;
    display: inline-block;
    vertical-align: top;
    color: white;
    width: 30px;
    height: 30px;
    font: 30px/1 Arial,sans-serif;
    text-align: center;
    border-radius: 50%;
    background-clip: padding-box;
}
/*div {
    text-align: center;
}
.minus:hover{
    background-color: #717fe0 !important;
}
.plus:hover{
    background-color: #717fe0 !important;
}
/*Prevent text selection*/
/*span{
    -webkit-user-select: none;
    -moz-user-select: none;
    -ms-user-select: none;
}
input{  
    border: 0;
    width: 2%;
}
nput::-webkit-outer-spin-button,
input::-webkit-inner-spin-button {
    -webkit-appearance: none;
    margin: 0;
}
input:disabled{
    background-color:white;
}*/
         


  </style> 
@stop
@extends('layouts.homepage')


@section('contentheader')
	
@endsection


@section('content')
@if (!empty(session('errorMessageDuration')))
         <div class="alert alert-danger">
             <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
             {{ session('errorMessageDuration') }}
         </div>
@endif
@error ('region')
    <div class="alert alert-danger">
        Please Select Region
    </div>
@enderror
@error ('serverspec')
    <div class="alert alert-danger">
        Please Select Server Specification
    </div>
@enderror

@error ('sshkeybare')
    <div class="alert alert-danger">
	Please Select SSH Key
    </div>
@enderror

@error ('esxipass')
    <div class="alert alert-danger">
        vCenter and ESXiPasswords must have min 8 characters from at least three character classes. (Uppercase characters (A-Z), Lowercase characters (a-z), Digits (0-9), special characters @$!%*#?&.
    </div>
@enderror


<form class="form-horizontal" action="" method="POST" id="order" name="order">
	<input type = "hidden" name = "_token" value = "<?php echo csrf_token() ?>">
	
	<div class="card card-primary card-outline">
        	<div class="card-header">
        		<h5 class="card-title">Select Region</h5>
        	</div>
	
		<div class="card-body">
			<div class="btn-toolbar" role="toolbar" aria-label="Toolbar with button groups">
				<label class="btn btn-info btn-lg" style="width: 200px;"> 
				<img src="icon-set/West_Java.png" width="60" alt="Technovillage DC" /></a></br>
                		<input type="radio" id="region" name="region" value=https://103.93.128.194:8092/api/v3"" autofocus="true" onclick="regiontv()"/>  TechnoVillage
				</label>&nbsp;&nbsp;
                		<label class="btn btn-info btn-lg" style="width: 200px;">
				<img src="icon-set/Region_Jakarta.png" width="60" alt="Midplaza DC" /></a></br>
                		<input type="radio" id="region" name="region" value="https://103.93.128.190:8092/api/v3" autofocus="true" onclick="regionmid()"/> Midplaza
				</label>&nbsp;&nbsp;
                		<label class="btn btn-info btn-lg" style="width: 200px;">
				<img src="icon-set/Third_Data_Center.png" width="60" alt="Teknovatus DC" /></a></br>
                        	<input type="radio" id="region" name="region" value="cbn" autofocus="true" onclick="regioncbn()"/> Teknovatus
                		</label>&nbsp;&nbsp;
        	<!--button type="button" class="btn btn-info btn-lg" style="width: 150px; height: 100px;">TechnoVillage</button>&nbsp;&nbsp;
                <button type="button" class="btn btn-info btn-lg" style="width: 150px; height: 100px;">Midplaza</button>&nbsp;&nbsp;
                <button type="button" class="btn btn-info btn-lg" style="width: 150px; height: 100px;">CBN</button>&nbsp;&nbsp;-->
			</div>
			<div id="regionlabel"></div>
		</div>
	</div>
        <div class="card card-primary card-outline">
                <div class="card-header">
                        <h5 class="card-title">Select Your Server</h5>
                </div>
	
		<div class="card-body">
	<!--div class="btn-toolbar" role="toolbar" aria-label="Toolbar with button groups"-->
			<div class="btn-group">
				<label class="btn btn-info blue" style="width: 180px;">
				<p style="text-align:left;">
				<input type="radio" id="serverspek" name="serverspek" value="nm.g3.small.x86" autofocus="true" onclick="neosmall()"/> 
				nm.g3.small.x86<br>
				Xeon E-2278G 8C<br>
				RAM 32GB<br>
				2x480GB SATA SSD<br>
				<img src="icon-set/best_seller2.png" width="150"/></a></br>
				</p>
                		</label>&nbsp;&nbsp;
				<label class="btn btn-info" style="width: 180px;">
				<p style="text-align:left;">
				<input type="radio" id="serverspek" name="serverspek" value="nm.g3.medium.x86" autofocus="true" onclick="neomed()"/> 
				nm.g3.medium.x86<br>
				AMD EPYC 7402P 24C<br>
				RAM 64GB<br>
				2x480GB SATA SSD<br>
				<img src="icon-set/best_seller2.png" width="150"/></a></br>
				</p>
                		</label>&nbsp;&nbsp;
				<label class="btn btn-info" style="width: 180px;">
				<p style="text-align:left;">
				<input type="radio" id="serverspek" name="serverspek" value="nm.g3.large.x86" autofocus="true" onclick="neolarge()"/> 
				nm.g3.large.x86<br>
				AMD EPYC 7502P 32C<br>
				RAM 128GB<br>
				2x480GB SATA SSD<br>
				<img src="icon-set/best_seller2.png" width="150"/></a></br>
				</p>
				</label>&nbsp;&nbsp;
				<label class="btn btn-info" style="width: 180px;">
				<p style="text-align:left;">
				<input type="radio" id="serverspek" name="serverspek" value="gm.g3.small.x86" autofocus="true" onclick="giosmall()"/> 
				gm.g3.small.x86<br>
				Xeon  Silver 4214 12C<br>
				RAM 192GB <br>
				2x960GBSATA-SSD<br>
				2x3.84TB Nvme<br>
				6x2TB HDD SATA</p>
                		</label>&nbsp;&nbsp;
				<label class="btn btn-info" style="width: 180px;">
				<p style="text-align:left;">
				<input type="radio" id="serverspek" name="serverspek" value="gm.g3.medium.x86" autofocus="true" onclick="giomed()"/> 
				gm.g3.medium.x86<br>
				Xeon Gold 5220 18C<br>
				RAM 256GB<br>
				2x960GBSATA-SSD<br>
				2x3.84TB Nvme<br>
				6x2TB HDD SATA</p>
                		</label>&nbsp;&nbsp;
				<label class="btn btn-info" style="width: 180px;">
				<p style="text-align:left;">
				<input type="radio" id="serverspek" name="serverspek" value="gm.g2.large.x86" autofocus="true" onclick="giolarge()"/> 
				gm.g2.large.x86<br>
				AMD EPYC 7702 64C
				RAM 384GB
				2x960GBSATA-SSD
				2x3.84TB Nvme
				6x8TB HDD-SATA</p>
                		</label><br>
			</div>
			<div id="spek"></div>
		</div>
	</div>
        <div class="card card-primary card-outline">
        	<div class="card-header">
        		<h5 class="card-title m-0">Select Operating System</h5>
	  	</div>
          	<div class="card-body">
			<div class="row">
  	  			<div class="col-12">
            <!-- Custom Tabs -->
    	    				<div class="nav-tabs-custom">
						<ul class="nav nav-tabs">
          	  				<li class="nav-item"><a class="nav-link active" href="#linux" data-toggle="tab" onclick="myFunctionlin()"><img src="icon-set/linux.png" width="16"> Linux</a></li>
          	  				<li class="nav-item"><a class="nav-link" href="#windows" data-toggle="tab"><img src="icon-set/Windows_logo_-_2012.png" width="16"> Windows</a></li>
          	  				<li class="nav-item"><a class="nav-link" href="#container" data-toggle="tab" onclick="myFunctionkub()"><img src="icon-set/kube.png" width="18"> Container</a></li>
          	  				<li class="nav-item"><a class="nav-link" href="#vsphere" data-toggle="tab" onclick="myFunctionvm()"><img src="icon-set/vmware.png" width="18"> VMware vSphere</a></li>
        					</ul>
	    				</div>
	    				<div class="tab-content">
	      					<div class="tab-pane active" id="linux">
							<input type="hidden" id="selectos" name="selectos"/>
                					<label class="btn btn-info" style="width: 180px;">
                       					<p style="text-align:left;"> <input type="radio" id="ostype" name="ostype" value="centos" autofocus="true"/>
							Centos<br></p>
							<p style="text-align:left;"> 
							OS Version
			<!--div class="form-group"-->
			
							<select class="form-control select2" style="width: 100%;" name="selectcentos">
                                				<option value="centos8-base">Centos 8</option>
                                				<option value="centos7-base">Centos 7</option>
                					</select>
              		<!--/div-->			</p>
							</label>
                					<label class="btn btn-info" style="width: 180px;">
                        				<p style="text-align:left;"><input type="radio" id="ostype" name="ostype" value="redhat" autofocus="true"/>
							Redhat<br></p>
							<p style="text-align:left;">
                        				OS Version
                        <!--div class="form-group"-->

                        				<select class="form-control select2" style="width: 100%;" name="selectrhel">
                                				<option value="rhel-server-8-dvd-installation">Redhat 8</option>
                                				<option value="rhel-server-7-installation">Redhat 7</option>
                        				</select>
                        <!--/div-->			</p>
							</label>
                					<label class="btn btn-info" style="width: 180px;">
                        				<p style="text-align:left;"><input type="radio" id="ostype" name="ostype" value="ubuntu" autofocus="true"/>
							Ubuntu<br></p>
							<p style="text-align:left;">
                        				OS Version
                        <!--div class="form-group"-->

                        				<select class="form-control select2" style="width: 100%;" name="selectubuntu">
                                				<option value="ubuntu-20">Ubuntu 20.04 LTS</option>
								<option value="ubuntu-18">Ubuntu 18.04 LTS</option>
                        				</select>
                        <!--/div-->			</p>
							</label>
                					<label class="btn btn-info" style="width: 180px;">
                        				<p style="text-align:left;"><input type="radio" id="ostype" name="ostype" value="debian" autofocus="true"/>
							Debian<br></p>
							<p style="text-align:left;">
                        				OS Version
                        <!--div class="form-group"-->

                        				<select class="form-control select2" style="width: 100%;" name="selectdeb">
                                				<option value="debian10-base">Debian 10</option>
                                				<option value="debian9-base">Debian 9</option>
                        				</select>
                        <!--/div-->			</p>
							</label>
                                                        <div class="form-group row">
                                                                <label for="dataraid" class="col-sm-2 col-form-label"><img src="icon-set/raid.png" width="20"/></a> Data Disk RAID</label>
                                                                <div class="col-3">
                                                                        <!--textarea class="form-control" name="sshkeybare" id="sshkeybare" rows="5" placeholder="Enter SSH Key" style="width: 100%;"></textarea-->
                                                                        <select class="form-control select2" style="width: 100%;" name="raidbare" id="raidbare">

										<option value="raid0">RAID0</option>
										<option value="raid10">RAID10</option>
										<option value="raid5">RAID5</option>
                                                                        </select>

                                                                </div>
                                                        </div>

                  					<div class="form-group row">
                    						<label for="sshkey" class="col-sm-2 col-form-label"><img src="icon-set/key2.png" width="20"/></a> SSH Key</label>
                    						<div class="col-3">
									<!--textarea class="form-control" name="sshkeybare" id="sshkeybare" rows="5" placeholder="Enter SSH Key" style="width: 100%;"></textarea-->
									<select class="form-control select2" style="width: 100%;" name="sshkeybare" id="sshkeybare">
									
									@foreach ($sshkeys as $key => $row)
                		                                                <option value="{{ $row->ssh_key  }}">{{ $row->ssh_key_name  }}</option>
									@endforeach
                                                		        </select>
								
                    						</div>
                  					</div>
                  					<!-- <div class="form-group row">
                    						<label for="vcenterpass" class="col-sm-2 col-form-label">Network Profile</label>
                  					</div> -->
                  					<div class="form-group">
                    						<div class="col-3">
                        					&nbsp;&nbsp;&nbsp;&nbsp;<input type="checkbox" class="form-check-input" id="pubipcheckbare" name="pubipcheckbare">
                        					<label class="form-check-label" for="pubipcheckbare"><img src="icon-set/ip-public.png" width="20"/></a> Deploy With Public IP</label>
                    						</div>
                  					</div>

	      					</div>
              					<div class="tab-pane" id="windows">
                					<label class="btn btn-info" style="width: 180px;">
                        				<p style="text-align:left;"><input type="radio" id="ostype" name="ostype" value="windows" autofocus="true"/>
                        				Windows<br></p>
                        				<p style="text-align:left;">
                        				OS Version
                        <!--div class="form-group"-->

                        				<select class="form-control select2" style="width: 100%;" name="selectoswin" data-placeholder="Select OS">
                                				<option value="Win2019uefi.img.xz">Windows 2019</option>
                                				<option value="Win2016uefi.img.xz">Windows 2016</option>
                        				</select>
                        <!--/div-->			</p>
							</label>
                                                        <div class="form-group row">
                                                                <label for="dataraid" class="col-sm-2 col-form-label"><img src="icon-set/raid.png" width="20"/></a> Data Disk RAID</label>
                                                                <div class="col-3">
                                                                        <!--textarea class="form-control" name="sshkeybare" id="sshkeybare" rows="5" placeholder="Enter SSH Key" style="width: 100%;"></textarea-->
                                                                        <select class="form-control select2" style="width: 100%;" name="raidwin" id="raidwin">

                                                                                <option value="raid0">RAID0</option>
                                                                                <option value="raid10">RAID10</option>
                                                                                <option value="raid5">RAID5</option>
                                                                        </select>

                                                                </div>
                                                        </div>

                                                        <div class="form-group row">
                                                                <label for="sshkey" class="col-sm-2 col-form-label"><img src="icon-set/key2.png" width="20"/></a> Password</label>
                                                                <div class="col-3">
                                                                        <input type="password" class="form-control" name="adminpass" id="adminpass" placeholder="Enter Administrator Password" style="width: 100%;"/>
								</div>

                                                        </div>

                  					<div class="form-group">
                    						<div class="col-3">
                        						&nbsp;&nbsp;&nbsp;&nbsp;<input type="checkbox" class="form-check-input" id="pubipcheckwin" name="pubipcheckwin">
                        						<label class="form-check-label" for="pubipcheckwin"><img src="icon-set/ip-public.png" width="20"/></a> Deploy With Public IP</label>
                    						</div>
                  					</div>

	      					</div>
              					<div class="tab-pane" id="container">
                					<label class="btn btn-info" style="width: 250px;">
                        				<p style="text-align:left;"><input type="radio" id="ostype" name="ostype" value="container" autofocus="true"/>
                        				Kubernetes<br></p>
                        				<p style="text-align:left;">
                      	  				OS Version
                        <!--div class="form-group"-->

                        				<select class="form-control select2" style="width: 100%;" name="selectkubha">
                                				<option value="kubha">Kubernetes HA (3 Master Node)</option>
                                				<option value="kubnoha">Kubernetes No HA</option>
                        				</select>
                        <!--/div-->			</p>
							</label>
							<div class="form-group row">
		     						<label for="sshkey" class="col-sm-2 col-form-label"><img src="icon-set/kube.png" width="20"/></a> Worker Count</label>
		     						<div class="qty col-3">
									<input type=button value='-' onclick='javascript:process(-1)'>
									<input type=text size=5 id='workernum' name='workernum' value='1'>
									<input type=button value='+' onclick='javascript:process(1)'>
								</div>
							</div>

                                                        <div class="form-group row">
								<label for="selectcluster" class="col-sm-2 col-form-label"><img src="icon-set/kube.png" width="20"/></a>Select Cluster</label>
							<div class="col-3">
							<select class="form-control select2"  name="selectcluster">
                                                                @foreach ($profilenames as $propfilename => $row)
                                                                        <option value="{{ $row->profile_name  }}">{{ $row->profile_name  }}</option>
                                                                @endforeach

								<option value="newcluster">New Cluster</option>
                                                        </select>
							</div>
                                                        </div>

                  					<div class="form-group row">
                    						<label for="sshkey" class="col-sm-2 col-form-label"><img src="icon-set/key2.png" width="20"/></a> SSH Key</label>
								<div class="col-3">
                                                                        <select class="form-control select2" style="width: 100%;" name="sshkeycon" id="sshkeycon">
                                                                        @foreach ($sshkeys as $key => $row)
                                                                                <option value="{{ $row->ssh_key  }}">{{ $row->ssh_key_name  }}</option>
                                                                        @endforeach
                                                                        </select>

                        						<!--textarea class="form-control" name="sshkeycon" id="sshkeycon" rows="5" placeholder="Enter SSH Key" style="width: 100%;"></textarea-->
                    						</div>
                  					</div>
                  					<div class="form-group">
                    						<div class="col-3">
                        						&nbsp;&nbsp;&nbsp;&nbsp;<input type="checkbox" class="form-check-input" id="pubipcheckcon" name="pubipcheckcon" checked disabled>
                        						<label class="form-check-label" for="pubipcheck"><img src="icon-set/ip-public.png" width="20"/></a> Deploy With Public IP</label>
								</div>
							</div>
                  				</div>

	      				<!--/div-->
	      					<div class="tab-pane" id="vsphere">
							<div class="form-group row">
                						<label class="btn btn-info" style="width: 250px;">
                        					<p style="text-align:left;"><input type="radio" id="ostype" name="ostype" value="vsphere" autofocus="true"/>
                        					VMware vSphere<br></p>
                        					<p style="text-align:left;">
                        					OS Version
                        <!--div class="form-group"-->

                        					<select class="form-control select2" style="width: 100%;" name="selectvsphere">
                                					<option value="vsphere70">vSphere 7</option>
                                					<!--option value="vsphere67">vSphere 6.7</option-->
                        					</select>
                        <!--/div-->				</p>
								</label>
							</div>
              						<div class="card card-primary card-outline">
              							<div class="card-header">
                    							<h5 class="card-title m-0">Select Datastore</h5>
	        						</div>
	      							<div class="card-body">
                  							<div class="form-group row">
                    								<label class="btn btn-info" style="width: 250px;">
                        							<p style="text-align:left;"><input type="checkbox" id="dstype" name="ostype" value="standard" autofocus="true"/>
                        							Standard Datastore Size GB<br></p>
                        							<p style="text-align:left;">
                        							<!--div class="qty"-->
                                							<!--span class="minusds bg-dark">-</span>
                                							<input type="number" class="countds" name="dssize" value="500" style="width:100px; text-align: center;">
											<span class="plusds bg-dark">+</span-->
                                                                <div class="qty">
                                                                        <input type=button value='-' onclick='javascript:processstdds(-100)'>
                                                                        <input type=text size=5 id='stdds' name='stdds' value='100'>
                                                                        <input type=button value='+' onclick='javascript:processstdds(100)'>
                                                                </div>

                        							<!--/div-->
                        							</p>
                    								</label>&nbsp;&nbsp;&nbsp;
                    								<label class="btn btn-info" style="width: 250px;">
                        							<p style="text-align:left;"><input type="checkbox" id="dstype" name="ostype" value="ssd" autofocus="true"/>
                        							SSD Datastore Size GB<br></p>
                        							<p style="text-align:left;">
                        							<!--div class="qty">
                                							<span class="minusds bg-dark">-</span>
                                							<input type="number" class="countds" name="ssdsize" value="500" style="width:100px; text-align: center;">
                                							<span class="plusds bg-dark">+</span>
										</div-->
                                                                <div class="qty">
                                                                        <input type=button value='-' onclick='javascript:process_ssd_ds(-100)'>
                                                                        <input type=text size=5 id='ssd_ds' name='ssd_ds' value='100'>
                                                                        <input type=button value='+' onclick='javascript:process_ssd_ds(100)'>
                                                                </div>

                        							</p>
                    								</label>
		  							</div>
                						</div>
	      						</div>
                					<div class="form-group row">
                     						<label for="esxicount" class="col-sm-2 col-form-label"><img src="icon-set/vmware.png" width="20"/></a> ESXi Count</label>
                     						<!--div class="qty col-3">
                        						<span class="minus bg-dark">-</span>
                        						<input type="number" class="count" name="esxicount" value="1" style="width:50px; text-align: center;">
                        						<span class="plus bg-dark">+</span>
								</div-->
                                                                <div class="qty">
                                                                        <input type=button value='-' onclick='javascript:process_esxi(-1)'>
                                                                        <input type=text size=5 id='esxicount' name='esxicount' value='1'>
                                                                        <input type=button value='+' onclick='javascript:process_esxi(1)'>
                                                                </div>
							</div>
                                                        <div class="form-group row">
                                                                <label for="sshkey" class="col-sm-2 col-form-label"><img src="icon-set/key2.png" width="20"/></a>vCenter Password</label>
                                                                <div class="col-3">
                                                                        <input type="password" class="form-control" name="vcenterpass" id="vcenterpass" placeholder="Enter vCenter Password" style="width: 100%;"/>
                                                                </div>
                                                        </div>

							<div class="form-group row">
								<label for="sshkey" class="col-sm-2 col-form-label"><img src="icon-set/key2.png" width="20"/></a> Password</label>
                                                                <div class="col-3">
                                                                        <input type="password" class="form-control" name="esxipass" id="esxipass" placeholder="Enter Root Password" style="width: 100%;"/>
                                                                </div>
							</div>
              					</div>
            				</div>
	  			</div>
			</div>
		</div>
	</div>
	<button type="button" onclick="Checksubmit()"  class="btn btn-info">Submit</button>
</form>
</p>
@endsection

@section('js')
@parent
<!-- DataTables -->
<!--script src="/adminlte/plugins/datatables/jquery.dataTables.min.js"></script>
<script src="/adminlte/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
<script src="/adminlte/plugins/datatables-responsive/js/dataTables.responsive.min.js"></script>
<script src="/adminlte/plugins/datatables-responsive/js/responsive.bootstrap4.min.js"></script---------------------->

<!--
<script src="/adminlte/plugins/jquery/jquery.min.js"></script>
<script src="/adminlte/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="/adminlte/dist/js/adminlte.min.js"></script>
<script src="/adminlte/dist/js/demo.js"></script>
-->



<script>
function Checksubmit() {
	var region = document.forms["order"]["region"].value;
	var serverspek = document.forms["order"]["serverspek"].value;
	var os = document.forms["order"]["ostype"].value;
	var s = window.location.href;
	//alert(s);
var splitByForwardSlash = s.split('/');

// To get 14aD9Uxp
var tenant = splitByForwardSlash[splitByForwardSlash.length-2];
//alert(tenant);
//var action = "/"+tenant+"/orderbaremetal/process"; 
//alert(action);
	//alert(os);
	if(os=="centos" || os=="redhat" || os =="ubuntu" || os=="debian"){
		//var action = "/"+tenant+"/orderpage/orderbaremetal/process";
		var action = "/orderpage/orderbaremetal/process";
		var centos = document.forms["order"]["selectcentos"].value;
		var ubuntu = document.forms["order"]["selectubuntu"].value;
		var rhel = document.forms["order"]["selectrhel"].value;
		var deb = document.forms["order"]["selectdeb"].value;
		if(os=="centos"){
			document.getElementById("selectos").value = centos;
		}else if (os=="ubuntu"){
			document.getElementById("selectos").value = ubuntu;
		}else if (os == "redhat"){
			document.getElementById("selectos").value = rhel;
		}else if(os == "debian"){
			document.getElementById("selectos").value = deb;
		}
		document.getElementById("order").action = action;
		document.getElementById("order").submit();
		//alert(document.forms["order"]["selectos"].value);
		//document.getElementById("order").submit();
	}else if (os=="windows"){
		//var action = "/"+tenant+"/orderpage/windows/process";
		var action = "/orderpage/windows/process";
		document.getElementById("order").action = action;
		document.getElementById("order").submit();
		//document.getElementById("order").submit();
		//alert("Windows");
	}else if (os=="container"){
		//var action = "/"+tenant+"/orderpage/kubernetes/process";
		var action = "/orderpage/kubernetes/process";
		document.getElementById("order").action = action;
		document.getElementById("order").submit();
		//document.getElementById("order").submit();
		//alert(document.forms["order"]["workernum"].value);
	}else if (os=="vsphere"){
		//var action = "/"+tenant+"/orderpage/ordergioprivate/process";
		var action = "/orderpage/ordergioprivate/process";
		document.getElementById("order").action = action;
		document.getElementById("order").submit();
		//document.getElementById("order").submit();
		//alert("vSphere");
	}

//alert(os);
}


		$(document).ready(function(){
		    $('.count').prop('disabled', true);
   			$(document).on('click','.plus',function(){
				$('.count').val(parseInt($('.count').val()) + 1 );
    		});
        	$(document).on('click','.minus',function(){
    			$('.count').val(parseInt($('.count').val()) - 1 );
    				if ($('.count').val() == 0) {
						$('.count').val(1);
					}
    	    	});
		});

		$(document).ready(function(){
		    $('.countds').prop('disabled', true);
   			$(document).on('click','.plusds',function(){
				$('.countds').val(parseInt($('.countds').val()) + 500 );
    		});
        	$(document).on('click','.minusds',function(){
    			$('.countds').val(parseInt($('.countds').val()) - 500 );
    				if ($('.countds').val() == 0) {
						$('.countds').val(500);
					}
    	    	});
 		});
</script>


<script>

function neosmall() {
  var x = document.getElementById("spek");
    x.innerHTML = "You have selected: <strong>nm.g3.small.x86</strong>";
}
function neomed() {
  var x = document.getElementById("spek");
    x.innerHTML = "You have selected: <strong>nm.g3.medium.x86</strong>";
}
function neolarge() {
  var x = document.getElementById("spek");
    x.innerHTML = "You have selected: <strong>nm.g3.large.x86</strong>";
}
function giosmall() {
  var x = document.getElementById("spek");
    x.innerHTML = "You have selected: <strong>gm.g3.small.x86</strong>";
}
function giomed() {
  var x = document.getElementById("spek");
    x.innerHTML = "You have selected: <strong>gm.g3.medium.x86</strong>";
}
function giolarge() {
  var x = document.getElementById("spek");
    x.innerHTML = "You have selected: <strong>gm.g3.large.x86</strong>";
}
function regiontv() {
  var x = document.getElementById("regionlabel");
    x.innerHTML = "You have selected: <strong>TechnoVillage Data Center</strong>";
}
function regionmid() {
  var x = document.getElementById("regionlabel");
    x.innerHTML = "You have selected: <strong>Midplaza Data Center</strong>";
}
function regioncbn() {
  var x = document.getElementById("regionlabel");
    x.innerHTML = "You have selected: <strong>Teknovatus Data Center</strong>";
}

</script>


<!--script>
  $(function () {
    $("#license").DataTable({
      "responsive": true,
      "autoWidth": false,
    });
    
  });
</script-->

<script>



function myFunctionlin() {
  var x = document.getElementById("myDIV");
    x.innerHTML = "Order Linux Baremetal";
}
function myFunctionwin() {
  var x = document.getElementById("myDIV");
    x.innerHTML = "Order Windows Baremetal";
}
function myFunctionvm() {
  var x = document.getElementById("myDIV");
    x.innerHTML = "Order GIO Enterprise vSphere";
}
function myFunctionkub() {
  var x = document.getElementById("myDIV");
    x.innerHTML = "Order GIO Enterprise Kubernetes";
}

</script>
<script language=javascript>
function process(v){
    var value = parseInt(document.getElementById('workernum').value);
    value+=v;
    document.getElementById('workernum').value = value;
}
function processstdds(v){
    var value = parseInt(document.getElementById('stdds').value);
    value+=v;
    if(value >= 100){
	    document.getElementById('stdds').value = value;
    }
}
function process_ssd_ds(v){
    var value = parseInt(document.getElementById('ssd_ds').value);
    value+=v;
    if(value >= 100){
            document.getElementById('ssd_ds').value = value;
    }
}
function process_esxi(v){
    var value = parseInt(document.getElementById('esxicount').value);
    value+=v;
    if(value >= 1){
            document.getElementById('esxicount').value = value;
    }
}


</script>
<!-- page script -->
@stop	
