{{Form::open(['route' => ['payroll.shift.shift-update',$shifts->id] , 'id' => 'form' , 'method' => 'post' , 'class' => 'form-horizontal', 'id' => 'customer_create_form'])}}
    <div class="box-body">
    <div id="others-supplier"></div>
    <div class="form-group">
        <label for="inputEmail3" class="col-sm-2 control-label"> Shift Name
            <b class="text-danger">*</b>
        </label>
        <div class="col-sm-6">
        <input type="text" name="shift_name" autocomplete="off" value="{{ $shifts->name }}" class="form-control" id="" placeholder="Enter Shift Name" required>
        </div>
       
    </div>
    @php($workShift = explode(',',$shifts->working_hour))
    <div class="form-group">
        <label for="inputEmail3" class="col-sm-2 control-label"> Working Hour
            <b class="text-danger">*</b>
        </label>
        <div class="col-sm-3">
            <input type="text"  name="working_shift_begin" autocomplete="off" value="{{ $workShift[0] }}" class="form-control" id="startdatetime" placeholder="Entry Time" required>
        </div>
        <div class="col-sm-3">
            <input type="text" name="working_shift_end" autocomplete="off" value="{{ $workShift[0] == '' ? '' : $workShift[1] }}" class="form-control" id="enddatetime" placeholder="End Time" required>
            </div>
            
        </div>
    <div class="form-group">
        <label for="inputEmail3" class="col-sm-2 control-label"> Shift Hour
            <b class="text-danger">*</b>
        </label>
        <div class="col-sm-2">
            <input type="number" name="shift_h" autocomplete="off" value="{{ number_format($shifts->shift_hour/60,2) }}" class="form-control" placeholder="Enter Shift Hour" required>
        </div>
    </div>
    <div class="form-group">
            <label for="inputEmail3" class="col-sm-2 control-label">Late Entry</label>
            <div class="col-sm-2">
                <input type="number" name="late" maxlength="50" autocomplete="off" value="{{ $shifts->late_entry }}" class="form-control" placeholder="Late Entry" required>
            </div> 
            
    </div>
    <div class="form-group">
        <label for="inputEmail3" class="col-sm-2 control-label">Early Leave</label>
        <div class="col-sm-2">
            <input type="number" name="e_leave" maxlength="50" autocomplete="off" value="{{ $shifts->e_leave }}" class="form-control" placeholder="Early Leave" required>
        </div> 
        
    </div>
    </div>
    <!-- /.box-body -->
    <div class="box-footer">
    <div class="col-sm-3">
        <button type="submit" id="" class="btn btn-info pull-right">Update</button>
        </div>
    </div>
    <!-- /.box-footer -->
{{ Form::close() }}


<link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.37/css/bootstrap-datetimepicker.min.css" rel="stylesheet">
<style type="text/css">

  
    .form-control::-webkit-inner-spin-button,
    .form-control::-webkit-calendar-picker-indicator {
        display: none;
        -webkit-appearance: none;
    }
    </style>



{{-- <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.js"></script> --}}
{{-- <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"></script> --}}
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.9.0/moment.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.37/js/bootstrap-datetimepicker.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.12.2/js/bootstrap-select.min.js"></script>
<script>
     $('#startdatetime').datetimepicker({
            format: 'hh:mm:ss A'
        });
        $('#enddatetime').datetimepicker({
            format: 'hh:mm:ss A'
        });

</script>


 