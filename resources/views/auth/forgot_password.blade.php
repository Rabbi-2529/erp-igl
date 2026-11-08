<!DOCTYPE html>
<html lang="en">

<head>
    <title>{{ config('app.name') }} | Forgot Password</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('assets/img/icons/favicon.ico') }}">
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />

    <link href="{{ asset('assets/css/jquery-ui.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/css/bootstrap.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/css/font-awesome.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/css/pe-icon-7-stroke.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/css/themify-icons.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/css/custom.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/css/style.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/js/sweetalert/sweetalert.css') }}" rel="stylesheet" type="text/css" />
    <style>
        body {
            background: url(assets/images/ui/auth-bg.jpg) no-repeat center center;
        }
    </style>
</head>

<body>
    <div class="container-center">
        <div class="panel panel-bd">
            <div class="panel-heading">
                <div class="view-header">
                    <div class="header-icon">
                        <i class="pe-7s-unlock"></i>
                    </div>
                    <div class="header-title">
                        <a href="https://iglweb.com" target="_blank">
                            <img src="{{ asset('assets/img/icons/logo.png') }}" alt="IGL ERP" height="50" />
                        </a>
                    </div>
                </div>
            </div>
            <div class="panel-body">

                <form action="{{ route('password.send') }}" method="post" class="login100-form validate-form">
                    @csrf

                    <div class="row">
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul>
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        @if(session('status'))
                            <div class="alert alert-success">
                                {{ session('status') }}
                            </div>
                        @endif
                    </div>

                    <div class="form-group">
                        <label for="au_mobile">Mobile Number</label>
                        <input type="text" name="au_mobile" id="au_mobile" class="form-control" placeholder="Enter your registered mobile number" required />
                    </div>

                    <div>
                        <button type="submit" class="btn btn-success">Send New Password</button>
                    </div>

                    <div class="mt-3">
                        <a href="{{ route('login') }}">Back to Login</a>
                    </div>
                </form>

            </div>
        </div>
    </div>

    <script src="{{ asset('assets/js/jquery-ui.min.js') }}"></script>
    <script src="{{ asset('assets/js/bootstrap.min.js') }}"></script>
</body>

</html>
