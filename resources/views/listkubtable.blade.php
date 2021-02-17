	<form class="form-horizontal" action="" method="POST" id="kubaction" name="kubaction">
        <table id="kubmachines" class="table table-bordered table-striped">
        <thead>
        <tr><td colspan="8">Kubernetes Cluster
                <select name='actionkub' id='actionkub' onchange='ActionKub()'>
			<option selected>Select Action</option>
			<option value="unsubkubserver">Unsubscribe Server</option>
			<option value="unsubkub">Unsubscribe Cluster</option>
			

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

