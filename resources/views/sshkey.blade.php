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


    <form class="form-horizontal" action="" method="POST" id="sshkeyaction" name="sshkeyaction">
        <input type="hidden" name="_token" value="<?php echo csrf_token(); ?>">
        <button type="button" class="btn btn-info" data-toggle="modal" data-target="#modal-sshkey">
            Add SSH Key
        </button>

        <button type="button" class="btn btn-info" value="deletessh" onclick="ActionDelete()">
            Delete SSH Key
        </button>
        </p>

        <table id="sshkey" class="table table-bordered table-striped" style="width:750px">
            <thead>
                <tr>
                    <th width="50px"></th>
                    <th width="350px">Name</th>
                    <th width="350px">Created</th>
                </tr>
            </thead><img src="icon-set/key2.png" width="16"> SSH Key added:
            <tbody>
                {{ count($sshkeys) }}
                @foreach ($sshkeys as $key => $row)
                    </p>
                    <tr>
                        <td><input name=checkssh[] type=checkbox value="{{ $row->id }}" /></td>
                        <td style="font-size: 13px">{{ $row->ssh_key_name }}</td>
                        <td style="font-size: 13px">{{ $row->created }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </form>
    <div class="modal fade" id="modal-sshkey">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Add SSH Key</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <script>
                    function validateForm() {
                        var keyname = document.forms["order"]["sshkeyname"].value;
                        var keyvalue = document.forms["order"]["sshkeycreate"].value;
                        if (keyname == "" || keyname == null) {
                            alert("Name must be filled out");
                            return false;
                        } else if (keyvalue == "" || keyvalue == null) {
                            alert("Public key must be filled out");
                            return false;
                        }
                    }

                </script>

                <div class="modal-body">
                    @error('sshkeycreate')
                        <div class="alert alert-danger">
                            Public key must be filled out
                        </div>
                    @enderror
                    @error('sshkeyname')
                        <div class="alert alert-danger">
                            Name must be filled out
                        </div>
                    @enderror


                    <form class="form-horizontal" action="/sshkey/create" method="POST" id="sshkey" name="sshkey">
                        <input type="hidden" name="_token"
                            value="<?php echo csrf_token(); ?>">
                        <div class="form-group row">
                            <label for="tenantname" class="col-sm-4 col-form-label">SSH Key Name</label>
                            <div class="col-8">
                                <input type="text" id="sshkeyname" name="sshkeyname" class="form-control"
                                    placeholder="Enter Key Name" value=" {{ old('sshkeyname') }}" />
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="tenantname" class="col-sm-4 col-form-label">SSH Key</label>
                            <div class="col-8">
                                <textarea class="form-control" name="sshkeycreate" id="sshkeycreate" rows="5"
                                    placeholder="Enter SSH Public Key"
                                    style="width: 100%;">{{ old('sshkeycreate') }}</textarea>
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
@endsection
@section('js')
    @parent
    <!-- DataTables -->
    <!--
    <script src="/adminlte/plugins/datatables/jquery.dataTables.min.js"></script>
    <script src="/adminlte/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
    <script src="/adminlte/plugins/datatables-responsive/js/dataTables.responsive.min.js"></script>
    <script src="/adminlte/plugins/datatables-responsive/js/responsive.bootstrap4.min.js"></script>
    <script src="/adminlte/plugins/jquery/jquery.min.js"></script>
    <script src="/adminlte/dist/js/adminlte.min.js"></script>
    -->
    <script src="/adminlte/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
    @if (count($errors) > 0)
        <script type="text/javascript">
            $(document).ready(function() {
                $('#modal-sshkey').modal('show');
            });

        </script>
    @endif
    <script language=javascript>
        function ActionDelete() {
            var checked = false;
            var elements = document.getElementsByName("checkssh[]");
            for (var i = 0; i < elements.length; i++) {
                if (elements[i].checked) {
                    checked = true;
                }
            }
            if (checked) {
                var action = "/sshkey/delete";
                //var result = confirm("Confirm to Delete?");
                if (confirm("Confirm to Delete")) {
                    document.getElementById("sshkeyaction").action = action;
                    document.getElementById("sshkeyaction").submit();
                } else {
                    return false;
                }
                if (action == "redeploy") {
                    var action = "/listmachines/action/redeploy";
                    var result = confirm("Confirm to Redeploy?");
                    //alert (action);
                    document.getElementById("machineaction").action = action;
                    document.getElementById("machineaction").submit();
                } else if (action == "unsubbare") {
                    var result = confirm("Confirm to Terminate?");
                    var action = "/listmachines/action/unsubbare";
                    document.getElementById("machineaction").action = action;
                    document.getElementById("machineaction").submit();
                }
            }
        }

    </script>
    <!--script>
      $(function () {
        $("#sshkey").DataTable({
          "responsive": false,
          "autoWidth": true,
        });
        
      });
    </script-->
    <!-- page script -->
@stop
