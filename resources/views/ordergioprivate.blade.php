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
	      <form class="form-horizontal" action="ordergioprivate/process" method="post">
		<input type = "hidden" name = "_token" value = "<?php echo csrf_token() ?>">
		<div class="card-body">
                  <div class="form-group row">
		    <label for="vcenterpass" class="col-sm-2 col-form-label">Credential Profile</label>
                  </div>
                  <div class="form-group row">
                    <label for="vcenterpass" class="col-sm-2 col-form-label">vCenter Password</label>
                    <div class="col-3">
                      <input type="password" class="form-control" id="vcenterpass" name="vcenterpass" placeholder="vCenter Password" placeholder=".col-3">
                    </div>
                  </div>
                  <div class="form-group row">
                    <label for="esxipass" class="col-sm-2 col-form-label">Password</label>
                    <div class="col-3">
                      <input type="password" class="form-control" id="esxipass" name="esxipass" placeholder="Esxi Password" placeholder=".col-3">
                    </div>
		  </div>
                  <div class="form-group row">
                    <label for="vcenterpass" class="col-sm-2 col-form-label">vCenter Profile</label>
                  </div>
                  <div class="form-group row">
                    <label for="vcenterpass" class="col-sm-2 col-form-label">vCenter Datacenter</label>
                    <div class="col-3">
                      <input type="text" class="form-control" id="vcdatacenter" name="vcdatacenter" placeholder="vCenter Datacenter" placeholder=".col-3">
                    </div>
                  </div>
                  <div class="form-group row">
                    <label for="esxipass" class="col-sm-2 col-form-label">vCenter Cluster</label>
                    <div class="col-3">
                      <input type="text" class="form-control" id="vccluster" name="vccluster" placeholder="vCenter Cluster" placeholder=".col-3">
                    </div>
		  </div>
                  <div class="form-group row">
                    <label for="vcenterpass" class="col-sm-2 col-form-label">Datastore Profile</label>
                  </div>
                  <div class="form-group row">
                    <label for="vcenterpass" class="col-sm-2 col-form-label">Datastore Size</label>
                    <div class="col-3">
                      <input type="text" class="form-control" id="datastoresize" name="datastoresize" placeholder="Datastore size" placeholder=".col-3">
                    </div>
		  </div>
                  <div class="form-group row">
                    <label for="vcenterpass" class="col-sm-2 col-form-label">ESXi Host Number</label>
                    <div class="col-3">
                      <input type="text" class="form-control" id="esxicount" name="esxicount" placeholder="ESXi Host Number" placeholder=".col-3">
                    </div>
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
</script>
<!-- page script -->
@stop	
