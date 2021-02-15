@section('css')
@parent
 <link rel="stylesheet" href="adminlte/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
 <link rel="stylesheet" href="adminlte/plugins/datatables-responsive/css/responsive.bootstrap4.min.css">
@stop
@extends('layouts.homepage')


@section('contentheader')
        Your Baremetal Server
@endsection


@section('content')



@if (!empty(session('KubMessageDuration')))
         <div class="alert alert-info">
             <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
             Kubernetes Cluster Deployment in Progress, you will be notified when deployment has complete or check status on 
<a class="nav-link" href="#container" data-toggle="tab">Container Tab</a>
         </div>
@endif


<div class="nav-tabs-custom">
	<ul class="nav nav-tabs">
      		<li class="nav-item"><a class="nav-link active" href="#linux" data-toggle="tab"><img src="icon-set/linux.png" width="16"> Linux</a></li>
                <li class="nav-item"><a class="nav-link" href="#windows" data-toggle="tab"><img src="icon-set/Windows_logo_-_2012.png" width="16"> Windows</a></li>
                <li class="nav-item"><a class="nav-link" href="#container" data-toggle="tab"><img src="icon-set/kube.png" width="18"> Container</a></li>
                <li class="nav-item"><a class="nav-link" href="#vsphere" data-toggle="tab"><img src="icon-set/vmware.png" width="18"> VMware vSphere</a></li>
       	</ul>
</div>
</p>
<div class="tab-content">
	<div class="tab-pane active" id="linux">
	<!-- <div id="lintable" class="jsgrid" style="position: relative; height: 100%; width: 100%;"> -->
		<div id="lintable">
			@include('listlintable')
        	</div>
	</div>
	<div class="tab-pane" id="windows">
		<div id="wintable" >
			@include('listwintable')
        	</div>
	</div>
	<div class="tab-pane" id="container">
        	<div id="kubtable" >
        		@include('listkubtable')
        	</div>
	</div>
</div>


@endsection

@section('js')
@parent
<!-- DataTables -->

<script src="{{url('adminlte/plugins/datatables/jquery.dataTables.min.js') }}"></script>
<script src="{{url('adminlte/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
<script src="{{url('adminlte/plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
<script src="{{url('adminlte/plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>

<script src="/adminlte/plugins/jquery/jquery.min.js"></script>
<script src="/adminlte/dist/js/adminlte.min.js"></script>
<script src="/adminlte/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>


</script>
<script language=javascript>
function Action(){
    var s = window.location.href; 
    var splitByForwardSlash = s.split('/');
    var tenant = splitByForwardSlash[splitByForwardSlash.length-2];
    var action = document.getElementById('action').value;
    var checked=false;
    var elements = document.getElementsByName("check[]");
    for(var i=0; i < elements.length; i++){
	if(elements[i].checked) {
		checked = true;
	}
    }
    if (checked) {
        if(action == "redeploy"){
                var action = "/listmachines/action/redeploy";
		if(confirm("Confirm to Redeploy?")){
                //alert (action);
                	document.getElementById("machineaction").action = action;
                	document.getElementById("machineaction").submit();
		}else{
			return false;
		}
        }else if(action == "unsubbare"){
		if(confirm("Confirm to Terminate?")){
                	var action = "/listmachines/action/unsubbare";
                	document.getElementById("machineaction").action = action;
			document.getElementById("machineaction").submit();
		}else{
			return false;
		}
        }	
    }

	//return checked;    
    //if(action == "redeploy"){
	//var action = "/listmachines/action/redeploy";
	//alert (action);
       	//document.getElementById("machineaction").action = action;    
	//document.getElementById("machineaction").submit();
    //}else if(action == "unsubbare"){
        //var action = "/listmachines/action/unsubbare";
        //document.getElementById("machineaction").action = action;
        //document.getElementById("machineaction").submit();	
    //}
    
}
function ActionWin(){
	//alert("test");
    var s = window.location.href;
    var splitByForwardSlash = s.split('/');
    var tenant = splitByForwardSlash[splitByForwardSlash.length-2];
    var action = document.getElementById('actionwin').value;
    var checked=false;
    var elements = document.getElementsByName("checkwin[]");
    for(var i=0; i < elements.length; i++){
        if(elements[i].checked) {
                checked = true;
        }
    }
    if (checked) {
        if(action == "redeploy"){
                var action = "/listmachines/action/redeploy";
                if(confirm("Confirm to Redeploy?")){
                //alert (action);
                        document.getElementById("machineactionwin").action = action;
                        document.getElementById("machineactionwin").submit();
		}else{
                        return false;
                }
        }else if(action == "unsubbare"){
                if(confirm("Confirm to Terminate?")){
                        var action = "/listmachines/action/unsubbare";
                        document.getElementById("machineactionwin").action = action;
                        document.getElementById("machineactionwin").submit();
                }else{
                        return false;
                }
        }
    }
    
    
    //alert(action);
    //if(action == "redeploy"){
    //    var action = "/listmachines/action/redeploy";
       /// alert (action);
    //    document.getElementById("machineactionwin").action = action;
    //    document.getElementById("machineactionwin").submit();
    //}else if(action == "unsubbare"){
    //    var action = "/listmachines/action/unsubbare";
    //    document.getElementById("machineactionwin").action = action;
    //    document.getElementById("machineactionwin").submit();
    //}

}


function ActionKub(){
    var action = document.getElementById('actionkub').value;
    var checked=false;
    var elements = document.getElementsByName("checkkub[]");
    for(var i=0; i < elements.length; i++){
        if(elements[i].checked) {
                checked = true;
        }
    }
    if(action == "unsubkub"){
	    var action = "/listmachines/action/unsubkub";
	    if(confirm("This will unsubscribe Kubernetes Cluster and unsubscribe all machines. Confirm Unsubscribe Cluster?")){
        	document.getElementById("kubaction").action = action;
        	document.getElementById("kubaction").submit();
	    }else{
            	return false;
            }
    }
    if (checked) {    
    	if(action == "unsubkubserver"){
	    var action = "/listmachines/action/unsubkubserver";
            if(confirm("Confirm to terminate selected server?")){
        	document.getElementById("kubaction").action = action;
		document.getElementById("kubaction").submit();
	    }else{
	    	return false;
	    }
    	}
    }

}

</script>


@if (!empty(session('InfoSubscribtion')))
<script type="text/javascript">
        $('#modal-subinfo').modal('show');
</script>
@endif

<script>
        $(document).ready(function() {
         setInterval(function() {
	   var page = window.location.href;
           $.ajax({
           url: 'https://bmaas.arch.biznetgio.xyz/listmachineskub',
           success:function(data)
           {
            $('#kubtable').html(data);
           }
           });
         }, 20000);
       });
</script>
<script>
        $(document).ready(function() {
         setInterval(function() {
           var page = window.location.href;
           $.ajax({
           url: 'https://bmaas.arch.biznetgio.xyz/listmachineswin',
           success:function(data)
           {
            $('#wintable').html(data);
           }
           });
         }, 20000);
       });
</script>
<script>
        $(document).ready(function() {
         setInterval(function() {
           var page = window.location.href;
           $.ajax({
           url: 'https://bmaas.arch.biznetgio.xyz/listmachineslin',
           success:function(data)
           {
            $('#lintable').html(data);
           }
           });
         }, 20000);
       });
</script>



<!--script>
  $(function () {
    $("#linuxtable").DataTable({
      "responsive": true,
      "autoWidth": false,
    });
    
  });
</script>
<script>
  $(function () {
    $("#kubmachines").DataTable({
      "responsive": true,
      "autoWidth": false,
    });

  });
</script>
<script>
  $(function () {
    $("#wintable").DataTable({
      "responsive": true,
      "autoWidth": false,
    });

  });
</script-->
<!-- page script -->
@stop	
