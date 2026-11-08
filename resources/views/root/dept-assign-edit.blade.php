@extends('layout.master')

@section('sds_menu_class','menu-open')
@section('department_class','menu-open')
@section('department_assign_class','active')
@section('content')
<section class="content">
        <section class="content-header">
          @if(session()->has('err'))
            <div class="alert alert-danger alert-dismissible" role="alert">
                {{ session('err') }}
              <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            @endif
            @if(session()->has('dept_add'))
            <div class="alert alert-success alert-dismissible" role="alert">
                {{ session('dept_add') }}
              <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            @endif
              <h1>
                Department
              </h1>       
            </section>
                   <div class="box">
                    <div class="box-header with-border">
                      <h3 class="box-title">Edit Department of {{ $comp->au_company_name }}</h3>
                    </div>
                    <!-- /.box-header -->
                    <!-- form start -->
                    {{Form::open(['route' => ['department-assign-update',$comp->au_id], 'method' => 'post' , 'class' => 'form-horizontal'])}}
                      <div class="box-body">
                      @php($departments = explode('-',$comp->au_department))
                      <div class="form-group">
                        <label for="" class="col-sm-2 control-label"> Department List</label>
                        <div class="col-sm-6">
                        <select class="duallistbox" name="dept[]" multiple="multiple">
                          @foreach ($department as $dept)
                              <option value="{{ $dept->id }}" {{ in_array($dept->id,$departments)?'selected':'' }}>{{ $dept->name }}</option>
                          @endforeach
                        </select>
                      </div>
                      @if($errors->has('dept'))
                            <p class="text-danger">{{ $message }}</p>
                        @endif
                      </div>
                        <!-- /.form-group -->
                        <!-- /.form-group -->
                      

                        

                         
                        
                        
                       
                        
                      </div>
                      <!-- /.box-body -->
                      <div class="box-footer">
                        <div class="col-sm-3">
                            <button type="submit" id="" class="btn btn-info pull-right">Add</button>
                         </div>
                      </div>
                      <!-- /.box-footer -->
                    {{ Form::close() }}
                  </div>
                  
                 </section>



@endsection
@section('custom_style')
<link rel="stylesheet" href="{{asset('asset/plugins/bootstrap-duallistbox.min.css')}}">
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
.select2-selection__choice{
  color: white;
  background-color: dodgerblue;
}
</style>

@endsection
@section('custom_script')
<script src="{{asset('asset/plugins/jquery.bootstrap-duallistbox.min.js')}}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.12.2/js/bootstrap-select.min.js"></script>
<script>
  $(document).ready(function(){

$( "#from4" ).datepicker({
       daysOfWeekHighlighted: "7",
        todayHighlight: true,
        
        autoclose: true,
     });
$( "#to" ).datepicker({
       daysOfWeekHighlighted: "7",
        todayHighlight: true,
     });
});
    




function remove_supplier(btn) {
  $(btn).parent().remove();
}
        $('.duallistbox').bootstrapDualListbox()

</script>
@endsection