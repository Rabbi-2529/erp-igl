@extends('layout.master')
@section('content')
<section class="content">
          <div class="box box-info">
            <div class="box-header with-border">
              <h3 class="box-title">Update Reference</h3>
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
            @if(session()->has('msg'))
            <div class="alert alert-success alert-dismissible" role="alert">
                {{ session('msg') }}
              <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            @elseif( session()->has('msg2') )
            <div class="alert alert-danger alert-dismissible" role="alert">
                {{ session('msg2') }}
              <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            @endif
            @if(session()->has('error'))
            <div class="alert alert-danger alert-dismissible" role="alert">
              {{ session('error') }}
              <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            @endif
            <!-- /.box-header -->
            <!-- form start -->
            {{ Form::open([ 'action' => ['UserController@referenceUpdate',$ref->id] , 'method' => 'post' ,'class' => 'form-horizontal', 'id' => 'user_registration_form', 'files' => true ]) }}

              <div class="box-body">
                <div class="form-group">
                  <label for="inputEmail3" class="col-sm-2 control-label">Reference Name <span style="color: red"> *</span></label>
                  <div class="col-sm-6">
                    <input type="text" name="ref_name" value="{{ $ref->ref_name }}" class="form-control" id="inputEmail3" placeholder="Enter Reference Name" required>
                  </div>
                </div>
                <div class="form-group">
                  <label for="inputEmail3" class="col-sm-2 control-label">Reference Number <span style="color: red"> *</span></label>
                  <div class="col-sm-6">
                    <input type="text" name="ref_number" value="{{ $ref->ref_number }}" class="form-control" id="inputEmail3" placeholder="Place Reference Number" required>
                  </div>
                </div>
                <div class="form-group">
                  <label for="inputEmail3" class="col-sm-2 control-label">Company Name <span style="color: red"> *</span></label>
                  <div class="col-sm-6">
                    <input type="text"  name="company_name" value="{{ $ref->company_name }}" value="{{ old('company_name') }}" autocomplete="off" class="form-control" placeholder="Enter Company Name" required>
                  </div>
                </div>
                <div class="form-group">
                  <label for="inputEmail3" class="col-sm-2 control-label">Company Address <span style="color: red"> *</span></label>
                  <div class="col-sm-6">
                    <input type="text" name="company_address" value="{{ $ref->company_address }}" value="{{ old('company_address')}}" class="form-control" id="inputEmail3" placeholder="Enter Company Address" required>
                  </div>
                </div>
                <div class="form-group">
                  <label for="inputEmail3" class="col-sm-2 control-label">Designation <span style="color: red"> *</span></label>
                  <div class="col-sm-6">
                    <input type="text"  name="designation" value="{{ $ref->designation }}" autocomplete="off"  class="form-control" id="mobilenum" placeholder="Enter Designation" required>
                    
                  </div>
              </div>
              <!-- /.box-body -->
              <div class="box-footer">
                <div class="col-sm-3">
                    <button type="submit" onclick="return confirm('Are you sure?. Your all information is currect.')" id="user_register_submit" class="btn btn-info pull-right">Update</button>
                </div>
              </div>
              <!-- /.box-footer -->
            {{ Form::close() }}
          </div>
         </section> 
@endsection
@section('custom_script')
<script type="text/javascript">
//////// Check Number Availability/////////

   $(document).ready(function(){
       //reference add more 
    $('#reference_add_more').click(function(){ 
        $('.reference_add_more').append('<div class="reference_remove_ar">\
        <h3 class="box-title">Add New Reference</h3>\
                <div class="form-group">\
                  <label for="inputEmail3" class="col-sm-2 control-label">Reference Name <span style="color: red"> *</span></label>\
                  <div class="col-sm-6">\
                    <input type="text" name="ref_name[]" class="form-control" id="inputEmail3" placeholder="Enter Reference Name" required>\
                  </div>\
                </div>\
                <div class="form-group">\
                  <label for="inputEmail3" class="col-sm-2 control-label">Reference Number <span style="color: red"> *</span></label>\
                  <div class="col-sm-6">\
                    <input type="text" name="ref_number[]" class="form-control" id="inputEmail3" placeholder="Place Reference Number" required>\
                  </div>\
                </div>\
                <div class="form-group">\
                  <label for="inputEmail3" class="col-sm-2 control-label">Company Name <span style="color: red"> *</span></label>\
                  <div class="col-sm-6">\
                    <input type="text"  name="company_name[]" autocomplete="off" class="form-control" placeholder="Enter Company Name" required>\
                  </div>\
                </div>\
                <div class="form-group">\
                  <label for="inputEmail3" class="col-sm-2 control-label">Company Address <span style="color: red"> *</span></label>\
                  <div class="col-sm-6">\
                    <input type="text" name="company_address[]" class="form-control" id="inputEmail3" placeholder="Enter Company Address" required>\
                  </div>\
                </div>\
                <div class="form-group">\
                  <label for="inputEmail3" class="col-sm-2 control-label">Designation <span style="color: red"> *</span></label>\
                  <div class="col-sm-6">\
                    <input type="text"  name="designation[]" autocomplete="off"  class="form-control" id="mobilenum" placeholder="Enter Designation" required>\
                  </div>\
                  <div class="col-sm-2">\
                      <a href="javascript:void(0)" id="reference_remove" onclick="hclick(this)" class="btn btn-danger pull-right">Remove</a>\
                  </div>\
                  </div>\
                </div>'); //Add field html
    });


    //reference remove 
    // $('.reference_add_more').on('click', '#reference_remove', function(e){
    //     e.preventDefault();
    //     //$(this).parent('.reference_remove_ar').remove(); //Remove field html
    //     $('.reference_remove_ar').remove();
    // });

   });


   function hclick(e){ 
    //e.remove();
    e.closest('.reference_remove_ar').remove(); 
   }

  //

</script>
@endsection
