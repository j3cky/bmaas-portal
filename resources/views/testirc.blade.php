@section('css')
@parent
 <link rel="stylesheet" href="/adminlte/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
 <link rel="stylesheet" href="/adminlte/plugins/datatables-responsive/css/responsive.bootstrap4.min.css">
@stop

@extends('layouts.homepage')


@section('contentheader')
        SSH Keys
@endsection


@section('content')
<iframe src="https://bmaas.arch.biznetgio.xyz:4443/irc.html?gui=true&lang=en">Your browser isn't compatible</iframe>
<iframe src="https://www.w3schools.com" title="W3Schools Free Online Web Tutorials">
</iframe>
@endsection
@section('js')
@parent
<!-- DataTables -->
<script src="/adminlte/plugins/datatables/jquery.dataTables.min.js"></script>
<script src="/adminlte/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
<script src="/adminlte/plugins/datatables-responsive/js/dataTables.responsive.min.js"></script>
<script src="/adminlte/plugins/datatables-responsive/js/responsive.bootstrap4.min.js"></script>

<script src="/adminlte/plugins/jquery/jquery.min.js"></script>
<script src="/adminlte/dist/js/adminlte.min.js"></script>
<!-- page script -->
@stop	
