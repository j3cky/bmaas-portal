@extends('layouts.homepage')

@section('contentheader')
            Activity Audit
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <!-- /.card-header -->
                <div class="card-body table-responsive p-0">
                    <table class="table table-hover text-nowrap">
                        <thead>
                            <tr>
                                <th>UUID</th>
                                <th>Action</th>
                                <th>Start Time</th>
                                <th>User ID</th>
                                <th>Message</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($logs as $log )
                            <tr>
                                <td>{{ $log ->uuid }}</td>
                                <td>{{ $log ->action }}</td>
                                <td>{{ $log ->start_time }}</td>
                                <td>{{ $log ->tenant_id }}</span></td>
                                <td>{{ $log ->message }}</td>
                            </tr>
                                
                            @empty
                            
                            <td colspan="6" style="text-align: center">empty logs</td>
                            
                            @endforelse
                            <tr>
                                <td>Create Linux Server</td>
                                <td>Create Linux Server</td>
                                <td>Feb. 24, 2021, 1:29 a.m</td>
                                <td>be1b643ad8201c89f9e681361ba5ca8cb11ed52a4c90396e7fd8fbdba142a400</span></td>
                                <td>Success</td>
                            </tr>


                        </tbody>
                    </table>
                </div>
                <!-- /.card-body -->
            </div>
            <!-- /.card -->
        </div>
    </div>

@endsection
