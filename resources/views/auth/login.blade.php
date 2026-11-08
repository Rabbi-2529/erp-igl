<!DOCTYPE html>
<html lang="en">

<head>
    <title>{{ config('app.name') }} | </title>
    <link rel="icon" type="image/x-icon" href="{{ asset('assets/img/icons/favicon.ico')}}">

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="{{ asset('assets/css/jquery-ui.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/css/bootstrap.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/css/font-awesome.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/css/pe-icon-7-stroke.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/css/themify-icons.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/css/custom.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/css/style.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/js/sweetalert/sweetalert.css') }}" rel="stylesheet" />

    <style>
        body {
            background: url(assets/images/ui/auth-bg.jpg) no-repeat center center;
        }

        /* Eye icon adjustment */
        #togglePassword {
            position: absolute;
            right: 15px;
            top: 32px;
            cursor: pointer;
            color: #777;
        }
    </style>
</head>

<body>
    <!-- Content Wrapper -->
    <div class="container-center">
        <div class="panel panel-bd">
            <div class="panel-heading">
                <div class="view-header">
                    <div class="header-icon">
                        <i class="pe-7s-unlock"></i>
                    </div>
                    <div class="header-title">
                        <a href="https://iglweb.com" target="_blank">
                            <img src="{{ asset('assets/img/icons/logo.png') }}" alt="IGL ERP" height="50">
                        </a>
                    </div>
                </div>
            </div>

            <div class="panel-body">

                {{-- FORM START --}}
                <form class="login100-form validate-form" action="" method="post">
                    @csrf

                    <!-- alert message -->
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

                        @if(session()->has('login'))
                        <div class="alert alert-danger">
                            {{ session()->get('login') }}
                        </div>
                        @endif
                    </div>
                    {{-- end alert section --}}

                    <div class="form-group">
                        <label class="control-label" for="username">Email or Phone</label>
                        <input type="text" placeholder="Enter Login ID" required name="au_mobile" id="username"
                            class="form-control">
                    </div>

                    {{-- PASSWORD FIELD WITH EYE ICON --}}
                    <div class="form-group" style="position: relative;">
                        <label class="control-label" for="password">Password</label>

                        <input type="password" 
                               placeholder="Enter Password" 
                               required 
                               name="au_password" 
                               id="password" 
                               class="form-control">

                        <span id="togglePassword">
                            <i class="fa fa-eye"></i>
                        </span>

                        <span>
                            Your strong password 
                            <a href="{{ route('password.request') }}" 
                               data-toggle="modal" 
                               data-target="#passwordrecoverymodal">
                                <b class="text-right">Forgot Password ??</b>
                            </a>
                        </span>
                    </div>

                    <div>
                        <button class="btn btn-success">Login</button>
                    </div>

                </form>
                {{-- FORM END --}}

            </div>
        </div>
    </div>

    <script src="{{ asset('assets/js/jquery-ui.min.js') }}"></script>
    <script src="{{ asset('assets/js/bootstrap.min.js') }}"></script>

    {{-- PASSWORD TOGGLE SCRIPT --}}
    <script>
        document.getElementById('togglePassword').addEventListener('click', function () {
            let pass = document.getElementById('password');
            let icon = this.querySelector('i');

            if (pass.type === 'password') {
                pass.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                pass.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
    </script>

</body>

</html>
