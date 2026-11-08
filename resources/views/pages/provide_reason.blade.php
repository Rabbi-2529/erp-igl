@extends('layout.master')
@section('sds_menu_class','menu-open')
@section('provide_reason_class','active')
@section('content')
<section class="content">
	<div class="box box-info">
		<div class="box-header with-border">
			<h3 class="box-title">Provide Reason</h3>
		</div>
		@if( session()->has('provide') )
		<div class="alert alert-success alert-dismissible" role="alert">
				{{ session('provide') }}
			<button type="button" class="close" data-dismiss="alert" aria-label="Close">
				<span aria-hidden="true">&times;</span>
			</button>
		</div>
		@endif
		<!-- /.box-header -->
		<!-- form start -->
		{{ Form::open(['action' => 'SdsController@provide_reason_submit' , 'method' => 'post' , 'class' => 'form-horizontal']) }}
		<div class="box-body">
			<div class="form-group">
				<label for="inputEmail3" class="col-sm-2 control-label">Company ID</label>
				<div class="col-sm-6">
					<select class="form-control select2" name="src_company_id" id="provide" required>
						<option value="">Select One</option>
						@foreach( $provide_reasons as $provide )
						<option value="{{ $provide->au_company_id }}">{{ $provide->au_company_name }}</option>
						@endforeach
					</select>
				</div>
			</div>
			<div class="box-body" style="margin-left: 30px;">
				@php($sl = 0)
				@foreach( $catagories as $catagory )
				<h1><u>{{ $catagory->sc_catagory }}</u></h1>
				<div class="form-group" style="margin-left: 30px;">
					@foreach( $catagory->reason_infos as $reason )
					<input type="checkbox" class="reason_info_checkbox" id="reason_info_{{ $reason->sr_slid }}" value="{{ $reason->sr_slid }}" name="src_reason_id[]">
					<label for="reason_info_{{ $reason->sr_slid }}">{{ $reason->sr_reason }}</label>
					@endforeach
				</div>
				@endforeach
			</div>
		</div>
		<!-- /.box-body -->
		<div class="box-footer" style="margin-left: 30px;">
			<button type="submit" class="btn btn-info">SAVE</button>
		</div>
		<!-- /.box-footer -->
		{{ Form::close() }}
	</div>
	<p id="all_list"></p>
</section>
@endsection
@section('custom_script')
<script type="text/javascript">
	// function company(){
	//   var x = document.getElementById("provide").value;
	//   document.getElementById("all_list").innerHTML = "You selected: " + x;
	// }
	$(document).ready(function(){
		$('#provide').on("change",function(){
			let com_id = $("#provide").val();
			let link = '{{ route('reason_selected') }}';
			$.ajax({
				type: "GET",
				url: link,
				data: { com_id: com_id},
				success: function (result) {
					$(".reason_info_checkbox").prop('checked', false);
					result.forEach(cus_function);
				}
			});
		});

		function cus_function(item, index) {
			$("#reason_info_" + item).prop('checked', true);
		}
	});
	
	$('form').submit(function() {
        $(this).find("button[type='submit']").prop('disabled',true);
      });
</script>
@endsection
