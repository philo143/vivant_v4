@extends('layouts.app')

@section('content')
<div class="container">
    <div class="col-md-6 col-md-offset-3">
        <form class="form-horizontal" role="form" method="POST" action="{{ url('/login') }}">
            {{ csrf_field() }}
            <fieldset>
            <legend>Login</legend>
            <div class="form-group">
                <label for="email" class="col-lg-2 control-label">Email</label>
                <div class="col-md-10">
                    <input type="text" class="form-control input-sm" id="email" name="email" value="{{ old('email') }}" required placeholder="Email">
                    @if ($errors->has('email'))
                        <small class="text-danger">{{ $errors->first('email') }}</small>
                    @endif
                </div>
            </div>
            <div class="form-group">
                <label for="password" class="col-lg-2 control-label">Password</label>
                <div class="col-md-10">
                    <input type="password" class="form-control input-sm" id="password" name="password"  required="" placeholder="Password">
                    <div class="checkbox">
                        <label>
                            <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : ''}}> Remember Me
                        </label>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <div class="col-lg-10 col-lg-offset-2">
                    <button type="submit" class="btn btn-primary btn-sm">Login</button>
                </div>
            </div>
            <hr>
            <h6>If you forgot your password, contact your system admin to request for a reset. Thank you.</h6>
            <hr>
            </fieldset>
        </form>
    </div>
</div>

<br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br>
@endsection
