@section('css')
@parent
 <link rel="stylesheet" href="adminlte/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
 <link rel="stylesheet" href="adminlte/plugins/datatables-responsive/css/responsive.bootstrap4.min.css">
@stop
@extends('layouts.homepage')


@section('contentheader')
        Create New Ticket
@endsection


@section('content')

    <div class="container">
        @if(session('success'))
        <div class="alert alert-success">
          {{ session('success') }}
        </div>
        @endif

        <form method="POST" action="/contact">
            @csrf
            <div class="form-group {{ $errors->has('name') ? 'has-error' : '' }}">
                <label for="email">Email address</label>
                <input name="email" type="email" class="form-control" id="email" aria-describedby="emailHelp"
                    placeholder="Enter your email">
                <span class="text-danger">{{ $errors->first('email') }}</span>
            </div> 
            <div class="form-group {{ $errors->has('name') ? 'has-error' : '' }}">
                <label for="name">Name</label>
                <input name="name" type="text" class="form-control" id="name" aria-describedby="name" placeholder="Your name">
                <span class="text-danger">{{ $errors->first('name') }}</span>

            </div>
            <div class="form-group {{ $errors->has('name') ? 'has-error' : '' }}">
                <label for="exampleInputPassword1">Problem Detail</label>
                <textarea name="comment" class="form-control" id="exampleFormControlTextarea1" rows="3"></textarea>
                <span class="text-danger">{{ $errors->first('comment') }}</span>
            </div>
            <button type="submit" class="btn btn-primary">Submit</button>
        </form>
    </div>
@endsection
