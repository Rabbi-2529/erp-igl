<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>IGL Web Solution</title>
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
  <link rel="stylesheet" href="{{ asset('asset/bower_components/bootstrap/dist/css/bootstrap.min.css')}}">
  <link rel="stylesheet" href="{{ asset('asset/bower_components/font-awesome/css/font-awesome.min.css')}}">
  <link rel="stylesheet" href="{{ asset('asset/bower_components/Ionicons/css/ionicons.min.css')}}">
    <link rel="stylesheet" href="{{ asset('asset/css/richtext.min.css')}}">
  <link rel="stylesheet" href="{{ asset('asset/dist/css/AdminLTE.min.css')}}">
  <link rel="stylesheet" href="{{ asset('asset/dist/css/skins/_all-skins.min.css')}}">
  <link rel="stylesheet" href="{{ asset('asset/bower_components/morris.js/morris.css')}}">
  <link rel="stylesheet" href="{{ asset('asset/bower_components/jvectormap/jquery-jvectormap.css')}}">
  <link rel="stylesheet" href="{{ asset('asset/bower_components/bootstrap-datepicker/dist/css/bootstrap-datepicker.min.css')}}">
  <link rel="stylesheet" href="{{ asset('asset/bower_components/datatables.net-bs/css/dataTables.bootstrap.min.css')}}">
  <!-- <link rel="stylesheet" href="{{ asset('asset/css/jquery.dataTables.min.css') }}"> -->
  <link rel="stylesheet" href="{{ asset('asset/css/rowReorder.dataTables.min.css') }}">
  <link rel="stylesheet" href="{{ asset('asset/css/responsive.dataTables.min.css') }}">
  <link rel="stylesheet" href="{{ asset('asset/bower_components/select2/dist/css/select2.min.css')}}">
  <link rel="stylesheet" href="{{ asset('asset/bower_components/bootstrap-daterangepicker/daterangepicker.css')}}">
  <link rel="stylesheet" href="{{ asset('asset/plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.min.css')}}">
  <link rel="stylesheet" type="text/css" href="{{ asset('asset/css/bootstrap.css') }}">
    <link rel="icon" href="https://iglweb.com/web/img/IGL_Group_logo.png" type="image/gif" sizes="20x20">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700,300italic,400italic,600italic">
  <style type="text/css">
    .menu-open>.treeview-menu {
      display: block !important;
    }
    .buy-sell-button {
      display: block;
      width: 90%;
      margin: 0 auto;
      font-size: 25px;
      color: #fff !important;
      overflow: hidden;
      padding: 0;
      text-shadow: 3px 2px 3px green;
    }
    /* Chrome, Safari, Edge, Opera */
    input::-webkit-outer-spin-button,
    input::-webkit-inner-spin-button {
      -webkit-appearance: none;
      margin: 0;
    }
    
    /* Firefox */
    input[type=number] {
      -moz-appearance: textfield;
    }
  </style>
    @laravelPWA
  @yield('custom_style')
</head>

<body class="hold-transition skin-blue sidebar-mini {{ (\Request::route()->getName() == 'buy.buy-product-new')? 'sidebar-collapseavcd':'' }}">
<div class="wrapper">

  @include('layout.header')
  <!-- Left side column. contains the logo and sidebar -->
  @if(auth()->user()->au_user_type == 1)
    @include('layout.sidebar_root')
  @elseif(auth()->user()->au_user_type == 4)
    @include('layout.sidebar_admin')
  @else
    @include('layout.sidebar_leader_user')
  @endif
  <div class="content-wrapper">
    @yield('content')
  </div>
  @include('layout.footer')
  </div>


@stack('scripts')

  @yield('custom_script')
  <script>
    $(document).ready(function () {
        $('#search-input').on('input', function () {
            var q = $(this).val();
            if (q.length == 11) {
                $.ajax({
                    type: 'GET',
                    url: '{{ route('search') }}',
                    data: {q: q},
                    success: function (data) {
                        if (data.status == 'success') {
                            var url = data.url;
                            var button = '<a href="' + url + '" class="btn btn-success" target="_blank">Follow Up</a>';
                            $('#search-result').html(button);
                        } else if (data.status == 'error') {
                            var message = data.message;
                            if (message == 'No matching records found') {
                                var url = data.url;
                                var button = '<a href="' + url + '" class="btn btn-primary" target="_blank">Add</a>';
                                $('#search-result').html(button);
                            } else {
                                alert(message);
                            }
                        }
                    },
                    error: function (data) {
                        alert('An error occurred while searching');
                    }
                });
            } else {
                $('#search-result').empty();
            }
        });
    });
</script>
</body>
</html>
