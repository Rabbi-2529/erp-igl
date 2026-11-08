@extends('layout.master')
@section('oms_class','menu-open')
@section('accounts_class','active')
@section('content')
<section class="content">
<section class="content-header">
      <h1>
        Accounts
      </h1>
      {{--@if(Auth::user()->au_user_type == 4)--}}
      <a href="" class="btn btn-info" style="margin-top: 10px;" data-toggle="modal" data-target="#oms_convence_pay">PAID</a>
      
        {{--@endif--}}
    </section>
	<div class="box">
            <div class="box-header">
              <h3 class="box-title">User Bill Details</h3>
            </div>
            <!-- /.box-header -->
            <div class="box-body">
              
            </div>
            <!-- /.box-body -->
          </div>
         </section>
@endsection
@section('custom_script')
<script type="text/javascript">
  function showUser(id){

    var link = "{{ route('accounts_user_amount') }}";
    console.log(id);
    $.ajax({
      url: link,
      data: {id : id},
      success: function(result){
        if(result == 0) {
          $("#balance").text('');
          $("#balance").text("No Due Balance!");
        } else {
          $("#balance").text('');
          $("#balance").text("Amount is: "+result);
        }
      }
    });
  }
</script>
@endsection
