<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Form with Laravel and SendGrid</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css"
        integrity="sha384-JcKb8q3iqJ61gNV9KGb8thSsNjpSL0n8PARn9HuZOnIxN0hoP+VmmDGMN5t9UJ0Z" crossorigin="anonymous">
</head>

<body>

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
                <label for="exampleInputPassword1">Comment</label>
                <textarea name="comment" class="form-control" id="exampleFormControlTextarea1" rows="3"></textarea>
                <span class="text-danger">{{ $errors->first('comment') }}</span>
            </div>
            <button type="submit" class="btn btn-primary">Submit</button>
        </form>
    </div>

</body>

</html>
