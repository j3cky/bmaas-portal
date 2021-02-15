<!DOCTYPE html>
<!--
This is a starter template page. Use this page to start your new project from
scratch. This page gets rid of all links and provides the needed markup only.
-->
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta http-equiv="x-ua-compatible" content="ie=edge">

  <title>NEO Metal Provisioning - PT Biznet Gio Nusantara</a></title>

   @section('css')
  <!-- Font Awesome Icons -->
  <!--link rel="stylesheet" href="{{ asset('adminlte/plugins/fontawesome-free/css/all.min.css') }}"-->
  <!-- Theme style -->
  <link rel="stylesheet" href="/adminlte/plugins/fontawesome-free/css/all.min.css">
  <link rel="stylesheet" href="/adminlte/dist/css/adminlte.min.css">
  <link rel="stylesheet" href="/adminlte/plugins/tempusdominus-bootstrap-4/css/tempusdominus-bootstrap-4.min.css">
  <link rel="stylesheet" href="/adminlte/plugins/icheck-bootstrap/icheck-bootstrap.min.css">
  <link rel="stylesheet" href="/adminlte/plugins/jqvmap/jqvmap.min.css">
  <link rel="stylesheet" href="/adminlte/plugins/overlayScrollbars/css/OverlayScrollbars.min.css">



  @show
  <!-- Google Font: Source Sans Pro -->
  <link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700" rel="stylesheet">
  <!--link href="{{ asset('/icheck/demo/css/custom.css?v=1.0.3') }}" rel="stylesheet">
  <link href="{{ asset('/icheck/skins/all.css?v=1.0.3') }}" rel="stylesheet">
  <script src="{{ asset('/icheck/demo/js/jquery.js') }}"></script>
  <script src="{{ asset('/icheck/icheck.js?v=1.0.3') }}"></script>
  <script src="{{ asset('/icheck/demo/js/custom.min.js?v=1.0.3') }}"></script-->

</head>
<body class="hold-transition sidebar-mini">

<div class="wrapper">

  <!-- Navbar -->
        @include('layouts.header')
  <!-- /.navbar -->

  <!-- Main Sidebar Container -->
  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1 class="m-0 text-dark">  @yield('contentheader')  </h1>
          </div><!-- /.col -->
        </div><!-- /.row -->
      </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->

    <!-- Main content -->
    <div class="content">
      <div class="container-fluid">
     <div class="modal fade" id="modal-subinfo">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <h4 class="modal-title">Subscription Info</h4>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <div class="modal-body">
		<form class="form-horizontal" action="/subscribeservice" method="POST" id="order" name="order">
		<input type = "hidden" name = "_token" value = "<?php echo csrf_token() ?>">
                User {{ Auth::user()->name }} is not subscribed to NEO Metal Service. Click OK to Subcribe.
            </div>
            <div class="modal-footer justify-content-between">
              <button type="submit" class="btn btn-primary">OK</button>
            </div>
            </form>
          </div>
          <!-- /.modal-content -->
        </div>
      </div>

        <!-- /.row -->
      </div><!-- /.container-fluid -->
    </div>
    <!-- /.content -->
  </div>
  <!-- /.content-wrapper -->

  <!-- Control Sidebar -->
  <aside class="control-sidebar control-sidebar-dark">
    <!-- Control sidebar content goes here -->
    <div class="p-3">
      <h5>Title</h5>
      <p>Sidebar content</p>
    </div>
  </aside>
  <!-- /.control-sidebar -->

  <!-- Main Footer -->
  @include('layouts.footer')
</div>
<!-- ./wrapper -->

<!-- REQUIRED SCRIPTS -->
@section('js')


<script src="https://bmaas.arch.biznetgio.xyz/adminlte/plugins/jquery/jquery.min.js"></script>
<!-- Bootstrap 4 -->
<script src="https://bmaas.arch.biznetgio.xyz/adminlte/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<!-- AdminLTE App -->
<script src="https://bmaas.arch.biznetgio.xyz/adminlte/dist/js/adminlte.min.js"></script>
<script src="https://bmaas.arch.biznetgio.xyz/adminlte/plugins/jquery/jquery.min.js"></script>
<!-- Bootstrap 4 -->
<script src="https://bmaas.arch.biznetgio.xyz/adminlte/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="https://bmaas.arch.biznetgio.xyz/adminlte/plugins/jquery-validation/jquery.validate.min.js"></script>
<script src="/adminlte/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script type="text/javascript">
        $('#modal-subinfo').modal('show');
</script>

<!-- AdminLTE App -->
  @show
</body>
</html>
