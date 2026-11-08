@extends('layout.master')
@section('oms_class','menu-open')
@section('oms_lunch_class','menu-open')
@section('lunch_add_new_bill','active')
@section('custom_style')
    <link rel="stylesheet" href="{{asset('asset/plugins/bootstrap-duallistbox.min.css')}}">
@endsection
@section('content')
<section class="content">
<section class="content-header">
      <h1>
        Meal
      </h1>
    </section>

          
         </section>
@endsection
@section( 'custom_script' )
    <script src="{{asset('asset/plugins/jquery.bootstrap-duallistbox.min.js')}}"></script>
    <script>
        $('.duallistbox').bootstrapDualListbox()
    </script>
    <script>

$(document).ready(function(){
     $("#guest").keyup (function (){
         let staffs = $('#custPerson').val()
         let guest = $('#guest').val()
         let total  = parseInt(guest) + staffs.length
         $("#total").html(total)
         $("#totalDiv").css('display','')
     })




    $(".itemSearch").select2({
        tags: false,
        multiple: false,
        tokenSeparators: [',', ' '],
        minimumInputLength: 3,
        minimumResultsForSearch: 10,
        placeholder: "Select a Customer",
        ajax: {
            url: '/convence_get_customer',
            dataType: "json",
            type: "GET",
            data: function (params) {

                var queryParameters = {
                    term: params.term
                }
                return queryParameters;
            },
            processResults: function (data) {
                return {
                    results: $.map(data, function (item) {
                        // $('.itemSearch').html('<option value=' + item.qb_id + '>' + item.qb_company_name  + '</option>')


                        // alert(item.qb_company_name)
                        return {

                            text: item.qb_company_name+' ('+item.qb_mobile+')',
                            id: item.qb_id,
                        }
                    })
                };
            }
        }
    });

$( "#from5" ).datepicker({
       daysOfWeekHighlighted: "7",
        todayHighlight: true,
        endDate: "today",
        startDate: "-6d",
        autoclose: true,
     });
$( "#to" ).datepicker({
       daysOfWeekHighlighted: "7",
        todayHighlight: true,
     });
});

      $('form').submit(function() {
        $(this).find("button[type='submit']").prop('disabled',true);
      });
</script>
@endsection
