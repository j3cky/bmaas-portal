	<form class="form-horizontal" action="" method="POST" id="machineaction" name="machineaction">
	<input type = "hidden" name = "typeos" value = "linux">
        <select name='action' id='action' onchange='Action()'>
                <option selected>Select Action</option>
                <option value="redeploy">Redeploy</option>
                <option value="unsubbare">Unsubscribe</option>
	</select>
        <input type = "hidden" name = "_token" value = "<?php echo csrf_token() ?>">
        <table id="linuxtable" class="table table-bordered table-striped">
        <thead>
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
        @foreach ($listmachineslin as $key => $row)
        <tr>
                <td><input name=check[] type=checkbox value="{{ $row->Uuid  }}" /></td>
                <td style="font-size: 14px">{{$row->Name}}</td>
                <td style="font-size: 14px">{{$row->Uuid}}</td>
                <td style="font-size: 14px">{{$addr[$row->Uuid]['private'] }}</td>
                <td style="font-size: 14px">{{$addr[$row->Uuid]['public']}}</td>
                <td style="font-size: 14px">{{$row->Workflow}}</td>
                <td style="font-size: 14px">
                @foreach ($row->Profiles as $profile)
                {{$profile}}
                @endforeach
                </td>
                <td style="font-size: 14px">{{$row->Stage}}</td>
        </tr>
        @endforeach
        </tbody>
        </table>
	</form>
