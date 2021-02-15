@section('css')
@parent
 <link rel="stylesheet" href="adminlte/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
 <link rel="stylesheet" href="adminlte/plugins/datatables-responsive/css/responsive.bootstrap4.min.css">
@stop

@extends('layouts.app')




@section('contentheader')
        License Usage
@endsection


@section('content')

	<table id="license" class="table table-bordered table-striped">
	<thead>
	<tr>
    		<th>Organization Name</th>
    		<th>Win 2012 STD</th>
    		<th>Win 2012 DC</th>
    		<th>Win 2016 STD</th>
    		<th>Win 2016 DC</th>
    		<th>Win 2019 STD</th>
    		<th>Win 2019 DC</th>
		<th>RedHat 8</th>
		<th>Redhat 7</th>
		<th>Redhat 6</th>
	</tr>
	<?php  $win12std = 0 ; $win12dc = 0 ; $win16std = 0 ; $win16dc = 0 ; $win19std = 0 ; $win19dc = 0 ; $redhat8 = 0 ; $redhat7 = 0 ; $redhat6 = 0 ; ?>
	</thead>
	<tbody>
	@foreach($orglicense as $orglicenseval)
	
	<tr>
		<?php
		 $win12std = $win12std + $orglicenseval['Windows2012STD'] ;
		 $win12dc = $win12dc + $orglicenseval['Windows2012DC'] ;
		 $win16std = $win16std + $orglicenseval['Windows2016STD'] ;
		 $win16dc = $win16dc + $orglicenseval['Windows2016DC'] ;
		 $win19std = $win19std + $orglicenseval['Windows2019STD'] ;
		 $win19dc = $win19dc + $orglicenseval['Windows2019DC'] ;
		 $redhat8 = $redhat8 + $orglicenseval['Redhat8'] ;
		 $redhat7 = $redhat7 + $orglicenseval['Redhat7'] ;
		 $redhat6 = $redhat6 + $orglicenseval['Redhat6'] ;
		?>
		<td>{{$orglicenseval['name']}}</td>
		<td>{{$orglicenseval['Windows2012STD']}}</td>
		<td>{{$orglicenseval['Windows2012DC']}} </td>
		<td>{{$orglicenseval['Windows2016STD']}}</td>
		<td>{{$orglicenseval['Windows2016DC']}}</td>
		<td>{{$orglicenseval['Windows2019STD']}}</td>
		<td>{{$orglicenseval['Windows2019DC']}}</td>
		<td>{{$orglicenseval['Redhat8']}}</td>
		<td>{{$orglicenseval['Redhat7']}}</td>
		<td>{{$orglicenseval['Redhat6']}}</td>
	</tr>
	
	@endforeach
	</tbody>
	<tr>
		<td style="font-weight:bold">Total</td>
		<td style="font-weight:bold">{{ $win12std}}</td>
		<td style="font-weight:bold">{{$win12dc}}</td>
		<td style="font-weight:bold">{{$win16std}}</td>
		<td style="font-weight:bold">{{$win16dc}}</td>
		<td style="font-weight:bold">{{$win19std}}</td>
		<td style="font-weight:bold">{{$win19dc}}</td>
		<td style="font-weight:bold">{{$redhat8}}</td>
		<td style="font-weight:bold">{{$redhat7}}</td>
		<td style="font-weight:bold">{{$redhat6}}</td>		
	</tr>
	</table>
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
