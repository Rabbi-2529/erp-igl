@extends('layout.master')
@section('sds_menu_class','menu-open')
@section('weekly_summery_class','active')
@section('content')
<section class="content">
<section class="content-header">
      <h1>
        Weekly Summery
      </h1>
    </section>
	<div class="box">
            <div class="box-header">
              <h3 class="box-title">Customers List</h3><br>
              <b>N:B:</b> Total = User/Admin | Entry Followup/Duplicate Followup
            </div>
            <!-- /.box-header -->
            <div class="box-body">
              <div class="box-footer">
                <form action="" method="get">
                  <div class="input-group">
                    <input type="text" id="from3" autocomplete="off" name="from" placeholder="From Date..." class="form-control" style="width: 250px;" data-date-format="yyyy-mm-dd">
                          <button type="submit" class="btn btn-info btn-flat">Search</button>  </div>
                </form>
              </div>

              @if(isset($show_data))
                <table id="dynamic-table1" class="table table-bordered table-striped">
                  <thead>
                  <tr>
                    <th>Date</th>
                    <th>{{ $weekly_dates[0] }}</th>
                    <th>{{ $weekly_dates[1] }}</th>
                    <th>{{ $weekly_dates[2] }}</th>
                    <th>{{ $weekly_dates[3] }}</th>
                    <th>{{ $weekly_dates[4] }}</th>
                    <th>{{ $weekly_dates[5] }}</th>
                    <th>Total</th>
                  </tr>
                  </thead>
                  <tbody>
                  <tr>
                    <th></th>
                    <th align="center">
                    {{ Carbon\Carbon::make($weekly_dates[0])->format('l') }}
                    </th>
                    <th align="center">
                    {{ Carbon\Carbon::make($weekly_dates[1])->format('l') }}
                    </th>
                    <th align="center">
                    {{ Carbon\Carbon::make($weekly_dates[2])->format('l') }}
                    </th>
                    <th align="center">
                    {{ Carbon\Carbon::make($weekly_dates[3])->format('l') }}
                    </th>
                    <th align="center">
                    {{ Carbon\Carbon::make($weekly_dates[4])->format('l') }}
                    </th>
                    <th align="center">
                    {{ Carbon\Carbon::make($weekly_dates[5])->format('l') }}
                    </th>
                    <th></th>
                  </tr>
                   @php( $total = 0 )
                   @php( $total_user_day_entry = 0 )
                   @php( $total_user_entry = 0 )
                   @php( $total_admin_entry = 0 )
                  @foreach( $week_customers as $week_customer)
                   @php( $user_weekly_entry = 0 )
                   @php( $user_day_entry = 0 )
                  @php( $user_weekly_staff_entry = 0 )

                  <tr>
                    <td>{{ $week_customer->au_name }}</td>
                    <td align="center">
                    @php( $day1_cus_entry = App\Models\Sds_query_book::user_weekly_cus($week_customer->au_id, $week_customer->au_company_id, $weekly_dates[0])->count())
                    @php( $day1_cus_staff_entry = App\Models\Sds_query_book::user_weekly_cus_staff($week_customer->au_id, $week_customer->au_company_id, $weekly_dates[0])->count())
                    @php( $user_day_entry = $day1_cus_entry + $day1_cus_staff_entry )
                    {{ $user_day_entry }} = {{ $day1_cus_entry }} / {{ $day1_cus_staff_entry }} |
                    <a href="{{ route('user-follow-up-list',[$week_customer->au_id,$weekly_dates[0]]) }}"  style="width: 20px;">{{ App\Models\Client_feedback::userFollowUpWeekly($week_customer->au_id,$week_customer->au_company_id,$weekly_dates[0]) }}</a>/
                    <a href="" class="btn btn-success btn-xs" style="width: 20px;">D</a>
                    @php( $user_weekly_entry = $user_weekly_entry + $day1_cus_entry )
                    @php( $user_weekly_staff_entry = $user_weekly_staff_entry + $day1_cus_staff_entry )

                    </td>
                    <td align="center">
                    @php( $day2_cus_entry = App\Models\Sds_query_book::user_weekly_cus($week_customer->au_id, $week_customer->au_company_id, $weekly_dates[1])->count() )
                    @php( $day2_cus_staff_entry = App\Models\Sds_query_book::user_weekly_cus_staff($week_customer->au_id, $week_customer->au_company_id, $weekly_dates[1])->count())
                    @php( $user_day_entry = $day1_cus_entry + $day2_cus_staff_entry )
                    {{ $user_day_entry }} = {{ $day2_cus_entry }} / {{ $day2_cus_staff_entry }} |
                    <a href="{{ route('user-follow-up-list',[$week_customer->au_id,$weekly_dates[1]]) }}"  style="width: 20px;">{{ App\Models\Client_feedback::userFollowUpWeekly($week_customer->au_id,$week_customer->au_company_id,$weekly_dates[1]) }}</a>/
                    <a href="" class="btn btn-success btn-xs" style="width: 20px;">D</a>
                    @php( $user_weekly_entry = $user_weekly_entry + $day2_cus_entry )
                    @php( $user_weekly_staff_entry = $user_weekly_staff_entry + $day2_cus_staff_entry )
                    </td>
                    <td align="center">
                    @php( $day3_cus_entry = App\Models\Sds_query_book::user_weekly_cus($week_customer->au_id, $week_customer->au_company_id, $weekly_dates[2])->count() )
                    @php( $day3_cus_staff_entry = App\Models\Sds_query_book::user_weekly_cus_staff($week_customer->au_id, $week_customer->au_company_id, $weekly_dates[2])->count())
                    @php( $user_day_entry = $day3_cus_entry + $day3_cus_staff_entry )
                    {{ $user_day_entry }} = {{ $day3_cus_entry }} / {{ $day3_cus_staff_entry }} |
                    <a href="{{ route('user-follow-up-list',[$week_customer->au_id,$weekly_dates[2]]) }}"  style="width: 20px;">{{ App\Models\Client_feedback::userFollowUpWeekly($week_customer->au_id,$week_customer->au_company_id,$weekly_dates[2]) }}</a>/
                    <a href="" class="btn btn-success btn-xs" style="width: 20px;">D</a>
                    @php( $user_weekly_entry = $user_weekly_entry + $day3_cus_entry )
                    @php( $user_weekly_staff_entry = $user_weekly_staff_entry + $day3_cus_staff_entry )
                    </td>
                    <td align="center">
                    @php( $day4_cus_entry = App\Models\Sds_query_book::user_weekly_cus($week_customer->au_id, $week_customer->au_company_id, $weekly_dates[3])->count() )
                    @php( $day4_cus_staff_entry = App\Models\Sds_query_book::user_weekly_cus_staff($week_customer->au_id, $week_customer->au_company_id, $weekly_dates[3])->count() )
                    @php( $user_day_entry = $day4_cus_entry + $day4_cus_staff_entry )
                    {{ $user_day_entry }} = {{ $day4_cus_entry }} / {{ $day4_cus_staff_entry }} |
                    <a href="{{ route('user-follow-up-list',[$week_customer->au_id,$weekly_dates[3]]) }}"  style="width: 20px;">{{ App\Models\Client_feedback::userFollowUpWeekly($week_customer->au_id,$week_customer->au_company_id,$weekly_dates[3]) }}</a>/
                    <a href="" class="btn btn-success btn-xs" style="width: 20px;">D</a>
                    @php( $user_weekly_entry = $user_weekly_entry + $day4_cus_entry )
                    @php( $user_weekly_staff_entry = $user_weekly_staff_entry + $day4_cus_staff_entry )
                    </td>
                    <td align="center">
                    @php( $day5_cus_entry =  App\Models\Sds_query_book::user_weekly_cus($week_customer->au_id, $week_customer->au_company_id, $weekly_dates[4])->count() )
                    @php( $day5_cus_staff_entry =  App\Models\Sds_query_book::user_weekly_cus_staff($week_customer->au_id, $week_customer->au_company_id, $weekly_dates[4])->count() )
                    @php( $user_day_entry = $day5_cus_entry + $day5_cus_staff_entry )
                    {{ $user_day_entry }} = {{ $day5_cus_entry }} / {{ $day5_cus_staff_entry }} |
                    <a href="{{ route('user-follow-up-list',[$week_customer->au_id,$weekly_dates[4]]) }}"  style="width: 20px;">{{ App\Models\Client_feedback::userFollowUpWeekly($week_customer->au_id,$week_customer->au_company_id,$weekly_dates[4]) }}</a>/
                    <a href="" class="btn btn-success btn-xs" style="width: 20px;">D</a>
                    @php( $user_weekly_entry = $user_weekly_entry + $day5_cus_entry )
                    @php( $user_weekly_staff_entry = $user_weekly_staff_entry + $day5_cus_staff_entry )
                    </td>
                    <td align="center">
                    @php( $day6_cus_entry = App\Models\Sds_query_book::user_weekly_cus($week_customer->au_id, $week_customer->au_company_id, $weekly_dates[5])->count() )
                    @php( $day6_cus_staff_entry = App\Models\Sds_query_book::user_weekly_cus_staff($week_customer->au_id, $week_customer->au_company_id, $weekly_dates[5])->count() )
                    @php( $user_day_entry = $day6_cus_entry + $day6_cus_staff_entry )
                    {{ $user_day_entry }} = {{ $day6_cus_entry }} / {{ $day6_cus_staff_entry }} |
                    <a href="{{ route('user-follow-up-list',[$week_customer->au_id,$weekly_dates[5]]) }}"  style="width: 20px;">{{ App\Models\Client_feedback::userFollowUpWeekly($week_customer->au_id,$week_customer->au_company_id,$weekly_dates[5]) }}</a>/
                    <a href="" class="btn btn-success btn-xs" style="width: 20px;">D</a>
                    @php( $user_weekly_entry = $user_weekly_entry + $day6_cus_entry )
                    @php( $user_weekly_staff_entry = $user_weekly_staff_entry + $day6_cus_staff_entry )
                    </td>
                    <td>
                    {{ $user_weekly_entry + $user_weekly_staff_entry }} = {{ $user_weekly_entry }}/{{ $user_weekly_staff_entry }}

                    @php( $total = $total +$user_weekly_entry + $user_weekly_staff_entry)
                    @php( $total_user_entry = $total_user_entry + $user_weekly_entry )
                    @php( $total_admin_entry =  $total_admin_entry + $user_weekly_staff_entry )

                    </td>
                  </tr>
                  @endforeach
                  </tbody>
                  <tfoot>
                      <tr>
                          <td colspan="6"></td>
                          <td>Total:</td>
                          <td> {{ $total }} = {{ $total_user_entry }}/{{ $total_admin_entry }}</td>
                      </tr>
                  </tfoot>

                </table>
              @endif
            </div>
            <!-- /.box-body -->
          </div>
         </section>
@endsection
@section('custom_script')
<script type="text/javascript">
$(document).ready(function(){

$( "#from3" ).datepicker({
       daysOfWeekHighlighted: "7",
        todayHighlight: true,
        autoclose: true,
     });
$( "#to" ).datepicker({
       daysOfWeekHighlighted: "7",
        todayHighlight: true,
     });
});
  jQuery(function($) {
            //initiate dataTables plugin
            var myTable = $('#dynamic-table1').DataTable();


            setTimeout(function() {
                $($('.tableTools-container')).find('a.dt-button').each(function() {
                    var div = $(this).find(' > div').first();
                    if (div.length == 1) div.tooltip({
                        container: 'body',
                        title: div.parent().text()
                    });
                    else $(this).tooltip({
                        container: 'body',
                        title: $(this).text()
                    });
                });
            }, 500);

            myTable.on('select', function(e, dt, type, index) {
                if (type === 'row') {
                    $(myTable.row(index).node()).find('input:checkbox').prop('checked', true);
                }
            });
            myTable.on('deselect', function(e, dt, type, index) {
                if (type === 'row') {
                    $(myTable.row(index).node()).find('input:checkbox').prop('checked', false);
                }
            });

            /////////////////////////////////
            //table checkboxes
            $('th input[type=checkbox], td input[type=checkbox]').prop('checked', false);

            //select/deselect all rows according to table header checkbox
            $('#dynamic-table > thead > tr > th input[type=checkbox], #dynamic-table_wrapper input[type=checkbox]').eq(0).on('click', function() {
                var th_checked = this.checked; //checkbox inside "TH" table header

                $('#dynamic-table').find('tbody > tr').each(function() {
                    var row = this;
                    if (th_checked) myTable.row(row).select();
                    else myTable.row(row).deselect();
                });
            });

            //select/deselect a row when the checkbox is checked/unchecked
            $('#dynamic-table').on('click', 'td input[type=checkbox]', function() {
                var row = $(this).closest('tr').get(0);
                if (this.checked) myTable.row(row).deselect();
                else myTable.row(row).select();
            });

            $(document).on('click', '#dynamic-table .dropdown-toggle', function(e) {
                e.stopImmediatePropagation();
                e.stopPropagation();
                e.preventDefault();
            });

            //And for the first simple table, which doesn't have TableTools or dataTables
            //select/deselect all rows according to table header checkbox
            var active_class = 'active';
            $('#simple-table > thead > tr > th input[type=checkbox]').eq(0).on('click', function() {
                var th_checked = this.checked; //checkbox inside "TH" table header

                $(this).closest('table').find('tbody > tr').each(function() {
                    var row = this;
                    if (th_checked) $(row).addClass(active_class).find('input[type=checkbox]').eq(0).prop('checked', true);
                    else $(row).removeClass(active_class).find('input[type=checkbox]').eq(0).prop('checked', false);
                });
            });

            //select/deselect a row when the checkbox is checked/unchecked
            $('#simple-table').on('click', 'td input[type=checkbox]', function() {
                var $row = $(this).closest('tr');
                if ($row.is('.detail-row ')) return;
                if (this.checked) $row.addClass(active_class);
                else $row.removeClass(active_class);
            });

            /********************************/
            //add tooltip for small view action buttons in dropdown menu
            $('[data-rel="tooltip"]').tooltip({
                placement: tooltip_placement
            });

            //tooltip placement on right or left
            function tooltip_placement(context, source) {
                var $source = $(source);
                var $parent = $source.closest('table')
                var off1 = $parent.offset();
                var w1 = $parent.width();

                var off2 = $source.offset();
                //var w2 = $source.width();

                if (parseInt(off2.left) < parseInt(off1.left) + parseInt(w1 / 2)) return 'right';
                return 'left';
            }

            /***************/
            $('.show-details-btn').on('click', function(e) {
                e.preventDefault();
                $(this).closest('tr').next().toggleClass('open');
                $(this).find(ace.vars['.icon']).toggleClass('fa-angle-double-down').toggleClass('fa-angle-double-up');
            });
            /***************/

            /**
            //add horizontal scrollbars to a simple table
            $('#simple-table').css({'width':'2000px', 'max-width': 'none'}).wrap('<div style="width: 1000px;" />').parent().ace_scroll(
              {
              horizontal: true,
              styleClass: 'scroll-top scroll-dark scroll-visible',//show the scrollbars on top(default is bottom)
              size: 2000,
              mouseWheelLock: true
              }
            ).css('padding-top', '12px');
            */

        })
</script>
@endsection
