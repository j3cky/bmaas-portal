	<form class="form-horizontal" action="" method="POST" id="frmprivaction" name="frmprivaction">
        <select name='privaction' id='privaction' onchange='ActionPriv()'>
                <option selected>Select Action</option>
                <option value="redeploy">Redeploy</option>
                <option value="unsubbare">Unsubscribe</option>
	</select>
        <input type = "hidden" name = "_token" value = "<?php echo csrf_token() ?>">
        <table id="privtable" class="table table-bordered table-striped">
        <thead>
        <tr>
                <th></th>
                <th>Name</th>
                <th>UUID</th>
                <th>Private Address</th>
		<th>Public Address</th>
		<th>vCenter Address</th>
                <th>Workflow</th>
                <th>Profiles</th>
                <th>Status</th>
        </tr>
        </thead>
        <tbody>
        @foreach ($listmachinespriv as $key => $row)
        <tr>
                <td><input name=checkpriv[] type=checkbox value="{{ $row->Uuid  }}" /></td>
                <td style="font-size: 14px">{{$row->Name}}</td>
                <td style="font-size: 14px">{{$row->Uuid}}</td>
                <td style="font-size: 14px">{{$addr[$row->Uuid]['private'] }}</td>
		<td style="font-size: 14px">{{$addr[$row->Uuid]['public']}}</td>
		<td style="font-size: 14px">{{$addr[$row->Uuid]['vcenter']}}</td>
                <td style="font-size: 14px">{{$addr[$row->Uuid]['workflow']}}</td>
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
