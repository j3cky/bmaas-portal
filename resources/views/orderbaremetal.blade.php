@section('css')
@parent
 <link rel="stylesheet" href="adminlte/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
 <link rel="stylesheet" href="adminlte/plugins/datatables-responsive/css/responsive.bootstrap4.min.css">
@stop
@extends('layouts.app')

@section('contentheader')
        Order Gio Private
@endsection


@section('content')
            <div class="card card-info">
              <div class="card-header">
                <h3 class="card-title">Deployment Profile</h3>
              </div>
              <!-- /.card-header -->
              <!-- form start -->
	      <form class="form-horizontal" action="orderbaremetal/process" method="post">
		<input type = "hidden" name = "_token" value = "<?php echo csrf_token() ?>">
		<div class="card-body">
                  <div class="form-group row">
                    <label for="vcenterpass" class="col-sm-2 col-form-label">Baremetal Profile</label>
                  </div>
                  <div class="form-group row">
		    <label for="vcenterpass" class="col-sm-2 col-form-label">Linux OS</label>
			&nbsp;&nbsp;
                    	<select name="selectos" class="form-control select2" data-placeholder="Select OS" style="width: 25%;">
                    		<option>centos7-base</option>
                    		<option>debian-base</option>
                    		<option>ubuntu-base</option>
                  	</select> 
		  </div>
                  <div class="form-group row">
                    <label for="sshkey" class="col-sm-2 col-form-label">SSH Key</label>
                    <div class="col-3">
                       	<textarea class="form-control" name="sshkey" id="sshkey" rows="5" placeholder="Enter SSH Key" style="width: 100%;"></textarea>
                    </div>
		  </div>
                  <div class="form-group row">
                    <label for="vcenterpass" class="col-sm-2 col-form-label">Network Profile</label>
		  </div>
                  <div class="form-group">
                    <div class="col-3">
                        &nbsp;&nbsp;&nbsp;<input type="checkbox" class="form-check-input" id="pubipcheck" name="pubipcheck">
                        <label class="form-check-label" for="pubipcheck">Deploy With Public IP</label>
                    </div>
                  </div>
		
                  <div class="form-group row">
                    <label for="vcenterpass" class="col-sm-2 col-form-label">Private IP</label>
		    <div class="col-3">
                      <input type="text" class="form-control" id="privateip" name="privateip" placeholder="Private IP" placeholder=".col-3">
                    </div>
		  </div>
                  <div class="form-group row">
		    <label for="subnet" class="col-sm-2 col-form-label">Subnet</label>
			&nbsp;&nbsp;
                        <select name="selectsubnet" class="form-control select2" data-placeholder="Select Subnet" style="width: 10%;">
                                <option>/24</option>
                                <option>/28</option>
                                <option>/30</option>
                        </select>
                  </div>
                </div>
                <!-- /.card-body -->
                <div class="card-footer">
                  <button type="submit" class="btn btn-info">Submit</button>
                  <button type="submit" class="btn btn-default float-right">Cancel</button>
                </div>
                <!-- /.card-footer -->
              </form>
	    </div>
@endsection
@section('js')
@parent
<!-- DataTables -->
<script src="{{url('adminlte/plugins/datatables/jquery.dataTables.min.js') }}"></script>
<script src="{{url('adminlte/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
<script src="{{url('adminlte/plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
<script src="{{url('adminlte/plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>

<script>
  $(function () {
    $("#license").DataTable({
      "responsive": true,
      "autoWidth": false,
    });
    
  });
$(document).ready(function(){
  $("#pubipcheck").toggle(this.checked){
    $("p").hide();
  });
});
</script>
<!-- page script -->
@stop	
