@extends('layouts.master')
@section('title', 'Leave Application')

@section('human_resource_class','active')
@section('leave','active')
@section('leave_aplication','active')

@section('content')
<div class="row">
    <div class="col-sm-12">
        <div class="panel panel-bd lobidrag">
            <div class="panel-heading">
                <div class="panel-title">
                    <h4>Apply for Leave</h4>
                </div>
            </div>

            @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
              @endif

              @if(session()->has('update'))
                    <div class="alert alert-success alert-dismissible" role="alert">
                        {{ session('update') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
              @endif
                
              @include('layout.session_message')

            <div class="panel-body">
                <div class="box">
                    <!-- form start -->
                    {{Form::open(['route' => 'leaveApplicationSubmit', 'id' => 'form' , 'method' => 'post' , 'class' => 'form-horizontal', 'id' => 'customer_create_form', 'enctype' => 'multipart/form-data'])}}
                      <div class="box-body">
                        <div id="others-supplier"></div>
                        <div class="form-group">
                          <label for="inputEmail3" class="col-sm-2 control-label"> Title
                              <b class="text-danger">*</b>
                          </label>
                          <div class="col-sm-4">
                            <input type="text" name="leave_title" autocomplete="off" value="{{ old('leave_title') }}" class="form-control" id="" placeholder="Enter Title" required>
                          </div>
                            @if($errors->has('leave_title'))
                                <p class="text-danger">{{ $errors->first('leave_title') }}</p>
                            @endif
                        </div>
                        <div class="form-group">
                            <label for="inputEmail3" class="col-sm-2 control-label"> Type
                                <b class="text-danger">*</b>
                            </label>
                            <div class="col-sm-3">
                                <select name="leave_type_id" id="" class="form-control">
                                    <option value="">Select a Type</option>
                                    @foreach ($leaveType as $item)
                                    <option value="{{ $item->id}}">{{ $item->leave_type}}</option> 
                                    @endforeach
                                </select>
                            </div>

                        </div>
                        <div class="form-group">
                            <label for="inputEmail3" class="col-sm-2 control-label"> Date
                                <b class="text-danger">*</b>
                            </label>
                            <div class="col-sm-3">
                              <label for="">Start Date*</label>
                              <input type="text" placeholder="Start Date" name="date_1" class="form-control" autocomplete="off" id="startDate" data-date-format="yyyy-mm-dd" required>
                            </div>

                            
                            <div class="col-sm-2">
                              <label for="">Days*</label>
                              <input type="text" name="total_day_request" onkeyup="selectEndDate(this.value)" class="form-control" autocomplete="off" id="totalDay" placeholder="Day" data-date-format="yyyy-mm-dd" required>
                            </div>
                            <div class="col-sm-3"> 
                              <label for="">End Date*</label>
                              <input type="text" placeholder="End Date" name="date_2" class="form-control" autocomplete="off" data-date-format="yyyy-mm-dd" id="endDate" readonly>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="user_desc" class="col-sm-2 control-label"> Description
                                <b class="text-danger">*</b>
                            </label>
                            <div class="col-sm-8">
                              <textarea name="user_desc" id="textEditor" class="form-control" cols="30" rows="10" required></textarea>
                            </div>
                          </div>
                          <div class="form-group">
                            <label for="inputEmail3" class="col-sm-2 control-label"> Attach File
                            </label>
                            <div class="col-sm-8">
                              <input type="file" name="file"  class="form-control">
                            </div>
                          </div>
                      </div>
                      <!-- /.box-body -->
                      <div class="box-footer">
                        <div class="col-sm-3">
                            <button type="submit" id="" class="btn btn-info pull-right">Submit</button>
                         </div>
                      </div>
                        
                      <!-- /.box-footer -->
                    {{ Form::close() }}
                  </div>
            </div>
        </div>
    </div>
</div>

  <style type="text/css">
      #rating > span {
        display: inline-block;
        position: relative;
        width: 1.1em;
      
      }
      #rating > span:hover:before,
      #rating > span:hover ~ span:before {
        content: "\2605";
        position: absolute;
        color: orange;
      }
      .form-control::-webkit-inner-spin-button,
      .form-control::-webkit-calendar-picker-indicator {
          display: none;
          -webkit-appearance: none;
      }
  </style>

  <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>

  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css">
  <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"></script>


  <script>

    $(document).ready(function(){
        $('#startDate').datepicker({
            format: 'yyyy-mm-dd',
            autoclose: true
        });
        
    });

    function selectEndDate(value) {
        var day = $("#startDate").val();
        $.ajax({
          type: "GET",
          url: "{{ route('leave.end-date-ajax') }}",
          data: {value:value, day:day},
          success:function(response){
            $("#endDate").val(response);
            if(response == null){

            }
          }
        })
      }

  </script>


@endsection