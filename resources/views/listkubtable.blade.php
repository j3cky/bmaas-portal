	<form class="form-horizontal" action="" method="POST" id="kubaction" name="kubaction">
        <table id="kubmachines" class="table table-bordered table-striped">
        <thead>
        <tr><td colspan="8">Kubernetes Cluster
                <select name='actionkub' id='actionkub' onchange='ActionKub()'>
			<option selected>Select Action</option>
			<option value="unsubkubserver">Unsubscribe Server</option>
			<option value="unsubkub">Unsubscribe Cluster</option>
			<option value="addworker">Add New Worker</option>			

                </select>
                <input type = "hidden" name = "_token" value = "<?php echo csrf_token() ?>">
        </td>
        </tr>
        <tr>
                <th></th>
                <th>Name</th>
                <th>UUID</th>
                <th>Private Address</th>
                <th>Public Address</th>
                <th>Workflow</th>
                <th>Profiles</th>
                <th>Status</th>
        </tr>
        </thead>
	<tbody>
        @foreach ($listmachineskub as $key => $row)
        <tr>
                <td><input name=checkkub[] type=checkbox value="{{ $row->Uuid  }}" /></td>
                <td style="font-size: 14px">{{$row->Name}}</td>
                <td style="font-size: 14px">{{$row->Uuid}}</td>
                <td style="font-size: 14px">{{$addr[$row->Uuid]['private'] }}</td>
                <td style="font-size: 14px">{{$addr[$row->Uuid]['public']}}</td>
                <td style="font-size: 14px">{{$addr[$row->Uuid]['workflow']}}</td>
                <td style="font-size: 14px">
                @foreach ($row->Profiles as $profile)
                {{$profile}}
                @endforeach
                <input type="hidden" id="kubprofiles" name="kubprofiles" value="{{$profile}}"/>
                </td>
                <td style="font-size: 14px">{{$row->Stage}}</td>

        </tr>
        @endforeach
        </tbody>
        </table>
        </form>
    <div class="modal fade" id="modal-addworker">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Add New Worker</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
		</div>
                <div class="modal-body">
                    <form class="form-horizontal" action="/listmachines/action/addworker" method="POST" id="frmaddworker" name="frmaddworker">
                        <input type="hidden" name="_token"
                            value="<?php echo csrf_token(); ?>">
                        <div class="form-group row">
                            <label for="tenantname" class="col-sm-4 col-form-label">Select Cluster</label>
			    <div class="col-8">
                            	<select class="form-control select2"  name="selectcluster">
                                @foreach ($profilenames as $propfilename => $row)
                             		<option value="{{ $row->profile_name  }}">{{ $row->profile_name  }}</option>
                              	@endforeach
                                </select>

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
