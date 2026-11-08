@extends('layout.master')
@section('money_class','menu-open')
@section('customer_class','menu-open')
@section('mex_customer_class','menu-open')
@section('customer_list','active')
@section('content')
<section class="content">
    <section class="content-header">
        @if(session()->has('cus_del'))
        <div class="alert alert-success alert-dismissible" role="alert">
          {{ session('cus_del') }}
          <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        @endif
        @if(session()->has('cus_up'))
        <div class="alert alert-success alert-dismissible" role="alert">
          {{ session('cus_up') }}
          <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        @endif
          <h1>
            Customer
          </h1>
        </section>
        <div class="box">
                <div class="box-header">
                  <h3 class="box-title">Customer List </h3>
                </div>
                <!-- /.box-header -->
                <div class="box-body">
                  <table id="example1" class="table table-bordered table-striped">
                    <thead>
                    <tr>
                      <th>SL</th>
                      <th>Cus Name</th>
                      <th>Company Name</th>
                      <th>Mobile</th>
                      <th>Phone</th>
                      <th>Address</th>
                      <th class="text-center">Action</th>
                    </tr>
                    </thead>
                    <tbody>
                    @php($sl=0)
                    @foreach ($customers as $cus)
                    <tr>
                        <td>{{ ++$sl }}</td>
                        <td>{{ $cus->name }}</td>
                        <td>{{ $cus->com_name }}</td>
                        <td>{{ $cus->mobile }}</td>
                        <td>{{ $cus->phone }}</td>
                        <td>{{ $cus->address }}</td>
                        <td align="center">
                            <div class="btn-group">
                                <button type="button" class="btn btn-info btn-xs">Action</button>
                                <button type="button" class="btn btn-info btn-xs dropdown-toggle" data-toggle="dropdown">
                                  <span class="caret"></span>
                                  <span class="sr-only">Toggle Dropdown</span>
                                </button>
                                <ul class="dropdown-menu" role="menu" style="margin-left: -40px;">
                                  <li>
                                    <a href="#" data-toggle="modal" data-target="#customer_info{{ $cus->id }}">Show</a>
                                  </li>
                                  <li class="divider"></li>
                                  <li>
                                    <a href="{{ route('money-exchange.customer.delete',['id' => $cus->id]) }}" onclick="return deletesup()">Delete</a>
                                  </li>
                                  <li class="divider"></li>
                                  <li>
                                  <a href="#" data-toggle="modal" data-target="#customer_edit{{$cus->id}}">Edit</a>
                                  </li>
                                </ul>
                          </div>
                        </td>
                      </tr>
                    @endforeach
                    </tbody>
                  </table>
                </div>
                <!-- /.box-body -->
              </div>

              @foreach ($customers as $cus)
              <div class="modal fade" id="customer_edit{{$cus->id}}" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                <div class="modal-dialog" role="document">
                  <div class="modal-content">
                    <div class="modal-header">
                      <h5 class="modal-title" id="exampleModalLabel">Update Customer Info</h5>
                      <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                      </button>
                    </div>

                    <div class="modal-body">
                      {{ Form::open(['route' => ['money-exchange.customer.edit', $cus->id], 'method' => 'post']) }}
                      <div class="form-group">
                          <label for="exampleInputEmail1">Name</label>
                          <input type="name" name="name" value="{{ $cus->name }}" class="form-control" id="" aria-describedby="emailHelp" placeholder="Enter email" required>
                          @if($errors->has('name'))
                              <p class="text-danger">{{ $errors->first('name') }}</p>
                          @endif
                        </div>
                        <div class="form-group">
                          <label for="exampleInputPassword1">Company Name</label>
                          <input type="text" name="com_name" value="{{ $cus->com_name }}" class="form-control" id="" placeholder="Company Name">
                        </div>
                        <div class="form-group">
                            <label for="exampleInputPassword1">Mobile</label>
                            <input type="text" name="mobile" value="{{ $cus->mobile }}" class="form-control" id="" placeholder="Mobile" required>
                            @if($errors->has('mobile'))
                              <p class="text-danger">{{ $errors->first('mobile') }}</p>
                            @endif
                        </div>
                        <div class="form-group">
                          <label for="phone">Phone</label>
                          <input type="text" name="phone" autocomplete="off" class="form-control" value="{{$cus->phone}}" placeholder="Enter Customer Phone Number">
                        </div>
                        <div class="form-group">
                            <label for="exampleInputPassword1">Email</label>
                            <input type="text" name="email" value="{{ $cus->email }}" class="form-control" id="" placeholder="Email">
                        </div>
                        <div class="form-group">
                            <label for="exampleInputPassword1">Address</label>
                            <input type="text" name="address" value="{{ $cus->address }}" class="form-control" id="" placeholder="Address">
                        </div>
                        <div class="form-group">
                            <label for="exampleInputPassword1">Type</label>
                            <label class="radio-inline">
                                <input type="radio" {{ ($cus->type==1)?'checked':'' }} name="type" value="1" required>Agency
                            </label>
                            <label class="radio-inline">
                                <input type="radio" {{ ($cus->type==2)?'checked':'' }} name="type" value="2" required>Bank
                            </label>
                            <label class="radio-inline">
                                <input type="radio" {{ ($cus->type==3)?'checked':'' }} name="type" value="3" required>Regular
                            </label>
                            @if($errors->has('type'))
                              <p class="text-danger">{{ $errors->first('type') }}</p>
                            @endif
                        </div>
                        <div class="form-group">
                            <label for="exampleInputPassword1">Balance Type</label>
                            <label class="radio-inline">
                                <input type="radio" {{ ($cus->bal_type ==1)?'checked':'' }} name="bal_type" value="1" required>Debit
                              </label>
                              <label class="radio-inline">
                                  <input type="radio" {{ ($cus->bal_type == 2)?'checked':'' }} name="bal_type" value="2" required>Credit
                              </label>
                              @if($errors->has('bal_type'))
                              <p class="text-danger">{{ $errors->first('bal_type') }}</p>
                              @endif
                        </div>
                        <div class="form-group">
                            <label for="exampleInputPassword1">Identity</label>
                            <input type="text" name="identity" value="{{ $cus->identity }}" class="form-control" id="" placeholder="Enter Customer NID">
                        </div>

                        <div class="form-group text-right" style="margin-top: 28px;">
                          <button type="submit" class="btn btn-success">Update</button>
                      </div>

                    {{ Form::close() }}
                    </div>
                    <div class="modal-footer">
                      <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    </div>

                  </div>
                </div>
              </div>
              @endforeach

              @foreach ($customers as $cus)
              <div class="modal fade" id="customer_info{{$cus->id}}" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                <div class="modal-dialog" role="document">
                  <div class="modal-content">
                    <div class="modal-header">
                      <h5 class="modal-title" id="exampleModalLabel">Customer Info</h5>
                      <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                      </button>
                    </div>
                    <div class="modal-body">
                      <table class="table table-bordered table-striped">
                      <tr>
                        <th>Nane</th>
                        <td>{{ $cus->name }}</td>
                      </tr>
                      <tr>
                          <th>Company Nane</th>
                          <td>{{ $cus->com_name }}</td>
                      </tr>
                      <tr>
                          <th>Mobile</th>
                          <td>{{ $cus->mobile }}</td>
                      </tr>
                      <tr>
                          <th>Phone</th>
                          <td>{{ $cus->phone }}</td>
                      </tr>
                      <tr>
                          <th>Email</th>
                          <td>{{ $cus->email }}</td>
                      </tr>
                      <tr>
                          <th>Address</th>
                          <td>{{ $cus->address }}</td>
                      </tr>
                      <tr>
                          <th>Customer Type</th>
                          <td>
                              @if($cus->type == 1)
                              Agency
                              @elseif($cus->type == 2)
                              Bank
                              @elseif($cus->type == 3)
                              Regular
                              @endif
                          </td>
                      </tr>
                      </table>
                    </div>
                    <div class="modal-footer">
                      <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    </div>

                  </div>
                </div>
              </div>
              @endforeach
             </section>
@endsection
@section('custom_style')
<style>
    .action_btn{
        border: 1px solid;
        padding: 5px;
    }
    .btn_show{
        background-color: green;
        color: white;
    }
    .btn_delete{
        background-color: red;
        color: white;
    }
    .btn_edit{
        background-color: cornflowerblue;
        color: white;
    }
</style>
@endsection
@section('custom_script')
<script type="text/javascript">
  function deletesup(){
    let clickDel = confirm("Are you sure want to delete this?");
    if (clickDel == true) {
      return true;
    }else{
      return false;
    }
  }


</script>
@endsection
