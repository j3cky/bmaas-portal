@section('css')
@parent
 <link rel="stylesheet" href="adminlte/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
 <link rel="stylesheet" href="adminlte/plugins/datatables-responsive/css/responsive.bootstrap4.min.css">
@stop

@extends('layouts.homepage')




@section('contentheader')
        Tenant List
@endsection


@section('content')
	<button type="button" class="btn btn-default" data-toggle="modal" data-target="#modal-tenant">
                  Add Tenant
	</button>
	</br>
	<table id="license" class="table table-bordered table-striped">
	<thead>
	<tr>
    		<th>Tenant Name</th>
    		<th>Date Created</th>
	</tr>
	</thead>
	<tbody>
	@foreach ($tenants as $key => $row)
	<tr>
		<td style="font-size: 14px"><a href=https://bmaas.arch.biznetgio.xyz/{{$row->tenant_name}}/listmachines> {{$row->tenant_name}} </a></td>
		<td style="font-size: 14px">{{$row->created}}</td>
	</tr>
	@endforeach
	</tbody>
	</table>
      <div class="modal fade" id="modal-tenant">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <h4 class="modal-title">Add Tenant</h4>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <div class="modal-body">
		<form class="form-horizontal" action="/createtenant" method="POST" id="order" name="order">
			<input type = "hidden" name = "_token" value = "<?php echo csrf_token() ?>">
			<div class="form-group row">
				<label for="tenantname" class="col-sm-4 col-form-label">Tenant Name</label>
				<div class="col-3">
				<input type="text" id="tenantname" name="tenantname"/>	
				</div>
			</div>
            </div>
            <div class="modal-footer justify-content-between">
              <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
              <button type="submit" class="btn btn-primary">Save</button>
	    </div>
	    </form>
          </div>
          <!-- /.modal-content -->
        </div>
        <!-- /.modal-dialog -->
      </div>
      <!-- /.modal -->
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
