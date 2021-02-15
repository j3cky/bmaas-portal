@section('css')
@parent
@stop

@extends('layouts.app')




@section('content')
        <div class="row">
          <div class="col-12 col-sm-6 col-md-3">
            <div class="info-box">
              <span class="info-box-icon bg-info elevation-1"><i class="fas fa-cloud"></i></span>

              <div class="info-box-content">
                <span class="info-box-text">Active Organization</span>
                <span class="info-box-number">
                  {{$orgcount  }}
                </span>
              </div>
              <!-- /.info-box-content -->
            </div>
            <!-- /.info-box -->
          </div>
          <div class="col-12 col-sm-6 col-md-3">
            <div class="info-box">
              <span class="info-box-icon bg-info elevation-1"><i class="fas fa-building"></i></span>

              <div class="info-box-content">
                <span class="info-box-text">Active Organization VDC</span>
                <span class="info-box-number">
                  {{$orgvdccount  }}
                </span>
              </div>
              <!-- /.info-box-content -->
            </div>
            <!-- /.info-box -->
          </div>
          <div class="col-12 col-sm-6 col-md-3">
            <div class="info-box">
              <span class="info-box-icon bg-info elevation-1"><i class="fas fa-server"></i></span>

              <div class="info-box-content">
                <span class="info-box-text">Memory Allocated</span>
                <span class="info-box-number">
                  {{ Helper::bytesToHuman($totalmem)  }}
                </span>
              </div>
              <!-- /.info-box-content -->
            </div>
            <!-- /.info-box -->
          </div>
          <div class="col-12 col-sm-6 col-md-3">
            <div class="info-box">
              <span class="info-box-icon bg-info elevation-1"><i class="fas fa-server"></i></span>

              <div class="info-box-content">
                <span class="info-box-text">vCPU Allocated</span>
                <span class="info-box-number">
                  {{ Helper::hzToHuman($totalvcpu)  }}
                </span>
              </div>
              <!-- /.info-box-content -->
            </div>
            <!-- /.info-box -->
          </div>

	</div>

        <div class="row">
          <div class="col-12 col-sm-6 col-md-3">
            <div class="info-box">
              <span class="info-box-icon bg-info elevation-1"><i class="fas fa-cloud"></i></span>

              <div class="info-box-content">
                <span class="info-box-text">Deployed VM</span>
                <span class="info-box-number">
                  {{$deployedvm  }}
                </span>
              </div>
              <!-- /.info-box-content -->
            </div>
            <!-- /.info-box -->
          </div>

          <div class="col-12 col-sm-6 col-md-3">
            <div class="info-box">
              <span class="info-box-icon bg-info elevation-1"><i class="fas fa-cloud"></i></span>

              <div class="info-box-content">
                <span class="info-box-text">PowerOn VM</span>
                <span class="info-box-number">
                  {{$poweronvm  }}
                </span>
              </div>
              <!-- /.info-box-content -->
            </div>
            <!-- /.info-box -->
          </div>
	</div>
        <div class="row">
          <div class="col-md-3">
            <div class="card">
              <div class="card-header">
		
                 <h3 class="card-title">Basic Storage Policy</h3>
              </div>
              <!-- /.card-header -->
              <div class="card-body p-0">
                <table class="table table-sm">
                  
                  <tbody>
                    <tr>
                      <td>Capacity</td>
                      <td>{{ Helper::bytesToHuman($sp['Basic']['capacity'])  }}</td>
                    </tr>
                    <tr>
                      <td>Used</td>
                      <td>{{ Helper::bytesToHuman($sp['Basic']['used'])  }}</td>
                    </tr>
                    <tr>
                      <td>Provisioned</td>
                      <td>{{ Helper::bytesToHuman($sp['Basic']['provisioned'])  }}</td>
                    </tr>
                  </tbody>
                </table>
              </div>
              <!-- /.card-body -->
            </div>

	  </div>

	  <!-- div ssd -->
          <div class="col-md-3">
            <div class="card">
              <div class="card-header">

                 <h3 class="card-title">SSD Storage Policy</h3>
              </div>
              <!-- /.card-header -->
              <div class="card-body p-0">
                <table class="table table-sm">

                  <tbody>
                    <tr>
                      <td>Capacity</td>
                      <td>{{ Helper::bytesToHuman($sp['SSD']['capacity'])  }}</td>
                    </tr>
                    <tr>
                      <td>Used</td>
                      <td>{{ Helper::bytesToHuman($sp['SSD']['used'])  }}</td>
                    </tr>
                    <tr>
                      <td>Provisioned</td>
                      <td>{{ Helper::bytesToHuman($sp['SSD']['provisioned'])  }}</td>
                    </tr>
                  </tbody>
                </table>
              </div>
              <!-- /.card-body -->
            </div>

          </div>
	  <!-- end div ssd -->

 	</div>

        <div class="row">
          <div class="col-md-6">
            <div class="card">
              <!-- /.card-header -->
              <div class="card-body p-0">
                <table class="table table-sm">
                  <thead>
                    <tr>
                      <th style="width: 10px">No</th>
                      <th>CIDR</th>
                      <th>Total IP</th>
                      <th>Used IP</th>
                    </tr>
                  </thead>
                  <tbody>
		  <?php $i=1; ?>
		  @foreach($pubips as $pubipval)
                    <tr>
                      <td><?php echo $i++;?></td>
                      <td>{{ $pubipval['gateway']  }}</td>
		      <td>{{ $pubipval['numberip']  }}</td>
		      <td>{{ $pubipval['usedip']  }}</td>
                    </tr>
		  @endforeach
                  </tbody>
                </table>
              </div>
              <!-- /.card-body -->
            </div>
	  </div>
	</div>
@endsection
@section('js')
@parent
@stop	
