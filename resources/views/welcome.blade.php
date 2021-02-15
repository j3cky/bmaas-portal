@extends('layouts.app')

@section('contentheader')
	Test Page
@endsection

@section('content')

<section class="content">

      <!-- Default box -->
      <div class="box">
        <div class="box-header with-border">
          <h3 class="box-title">Account Information</h3>

          <div class="box-tools pull-right">
            <button type="button" class="btn btn-box-tool" data-widget="collapse" data-toggle="tooltip"
                    title="Collapse">
              <i class="fa fa-minus"></i></button>
            <button type="button" class="btn btn-box-tool" data-widget="remove" data-toggle="tooltip" title="Remove">
              <i class="fa fa-times"></i></button>
          </div>
        </div>
        <div class="box-body">
	<a href="https://web.neo.id/account">Manage My Account</a>
		</p>
		</p>
		</p>
        </div>
        <!-- /.box-body -->
        <div class="box-footer">
	<div id="back" class="btn btn-info" type="button">
	<a href="/">Back</a> </div>
        </div>
        <!-- /.box-footer-->
      </div>
      <!-- /.box -->

    </section>

@endsection
