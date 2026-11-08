@extends('layout.master')
@section('sds_menu_class','menu-open')
@section('monthly_summery_class','active')
@section('content')
    <section class="content">
        <section class="content-header">
            <h1>
              Search Monthly Sell Summery
            </h1>
        </section>
        <div class="box">
            <div class="box-header">
                <h3 class="box-title">Customers List</h3><br>
                <b>N:B:</b> Total = User/Admin
                @if(session()->has('invalid_user'))
                    <p class='text-danger'>Selected User is Invalid!</p>
                @endif
            </div>

            <!-- /.box-header -->
            <div class="box-body">

                <div class="box-body">
                    <div class="row">
                        <form action="" method="get">
                            <div class="col-xs-3">
                                <select class="form-control" name="year">
                                    @for( $this_year = date('Y');$this_year>=2007; $this_year--)
                                        <option value="{{ $this_year }}" {{ (isset($request_year)? (($request_year == $this_year)? 'selected':''):'') }}>{{ $this_year }}</option>
                                    @endfor
                                </select>
                            </div>
                            <div class="col-xs-3">
                                <select class="form-control" name="month">
                                    <option value="1" {{ (isset($request_month)? (($request_month == 1)? 'selected':''):'') }}>January</option>
                                    <option value="2" {{ (isset($request_month)? (($request_month == 2)? 'selected':''):'') }}>February</option>
                                    <option value="3" {{ (isset($request_month)? (($request_month == 3)? 'selected':''):'') }}>March</option>
                                    <option value="4" {{ (isset($request_month)? (($request_month == 4)? 'selected':''):'') }}>April</option>
                                    <option value="5" {{ (isset($request_month)? (($request_month == 5)? 'selected':''):'') }}>May</option>
                                    <option value="6" {{ (isset($request_month)? (($request_month == 6)? 'selected':''):'') }}>June</option>
                                    <option value="7" {{ (isset($request_month)? (($request_month == 7)? 'selected':''):'') }}>July</option>
                                    <option value="8" {{ (isset($request_month)? (($request_month == 8)? 'selected':''):'') }}>August</option>
                                    <option value="9" {{ (isset($request_month)? (($request_month == 9)? 'selected':''):'') }}>September</option>
                                    <option value="10" {{ (isset($request_month)? (($request_month == 10)? 'selected':''):'') }}>October</option>
                                    <option value="11" {{ (isset($request_month)? (($request_month == 11)? 'selected':''):'') }}>November</option>
                                    <option value="12" {{ (isset($request_month)? (($request_month == 12)? 'selected':''):'') }}>December</option>
                                </select>
                            </div>
                            <div class="col-xs-3">
                                <select class="form-control" name="user">
                                    @foreach( $mon_users as $mon_user )
                                        <option value="{{ $mon_user->au_id }}"  {{ (isset($user)? (($user->au_id == $mon_user->au_id)? 'selected':''):'') }}>{{ $mon_user->au_name }}</option>
                                    @endforeach
                                </select>

                            </div>
                            <div class="col-xs-3">
                                <button type="submit" class="btn btn-info">Search</button>
                            </div>
                        </form>
                    </div>
                </div>
                <!-- /.box-body -->
                @if(isset($show_data))
                    <table id="dynamic-table1" class="table table-bordered table-striped">
                        <thead>
                        <tr>
                            @for($i = 1; $i <= 7; $i++)
                                <th>{{ Carbon\Carbon::make($request_year.'-'.$request_month.'-'.$i)->format('l') }}</th>
                            @endfor
                        </tr>
                        </thead>
                        <tbody>
                        @php($total_entry = 0)
                        @php( $total = 0 )
                        @php($total_entry_admin = 0)
                        <tr>
                            @for($j=1; $j<=$days_in_month; $j++)
                                @php($show_date = $request_year.'-'.$request_month.'-'.$j)
                                <td class="text-center"><button type="button" class="btn btn-primary btn-sm">
                                        {{ $j }} <br>
                                        {{--                            @php($created_customer = App\Models\Sds_query_book::user_monthly_sell($user->au_id, $user->au_company_id, $show_date)->count())</button><br>--}}
                                    @php( $total_sell = App\Models\Inv_product_inventory::where('inv_employee_id',$user->au_id)->whereDate('inv_pro_inv_issue_date',$show_date)->select('inv_pro_inv_debit')->get())</button><br>
                                    @php( $total_sell_sum = App\Models\Inv_product_inventory::where('inv_employee_id',$user->au_id)->whereDate('inv_pro_inv_issue_date',$show_date)->select('inv_pro_inv_debit')->sum('inv_pro_inv_debit'))</button><br>
                                    @php( $count_sell =  App\Models\Inv_product_inventory::where('inv_employee_id',$user->au_id)->whereDate('inv_pro_inv_issue_date',$show_date)->distinct('inv_pro_inv_invoice_no')->count() )
                                    {{ ($total_sell_sum == 0)?'0':$total_sell_sum }} = {{ ($count_sell == 0)?'0':$count_sell. ' /' }}
                                    @if ($total_sell !== 0)
                                        @foreach($total_sell as $sell)
                                            {{$sell->inv_pro_inv_debit }},<br>
                                        @endforeach

                                    @endif


                                </td>

                                @if($j%7 == 0)
                        </tr>
                        <tr>
                            @endif
                            @endfor

                        </tr>
                        </tbody>
                    </table>

                    <h4>Name:{{ $user->au_name }}</h4>
                    <h4>Total : {{ $total_entry + $total_entry_admin }} = {{ $total_entry }}/{{ $total_entry_admin }}</h4>
                @endif
            </div>
            <!-- /.box-body -->
        </div>
    </section>
@endsection
@section('custom_script')
    <script type="text/javascript">
        jQuery(function($) {
            //initiate dataTables plugin
            var myTable =
                $('#dynamic-table')
                    //.wrap("<div class='dataTables_borderWrap' />")   //if you are applying horizontal scrolling (sScrollX)
                    .DataTable({
                        bAutoWidth: false,
                        "aoColumns": [{
                            "bSortable": false
                        },
                            null, null, null, null, null, {
                                "bSortable": false
                            }
                        ],
                        "aaSorting": [],

                        //"bProcessing": true,
                        //"bServerSide": true,
                        //"sAjaxSource": "http://127.0.0.1/table.php" ,

                        //,
                        //"sScrollY": "200px",
                        //"bPaginate": false,

                        //"sScrollX": "100%",
                        //"sScrollXInner": "120%",
                        //"bScrollCollapse": true,
                        //Note: if you are applying horizontal scrolling (sScrollX) on a ".table-bordered"
                        //you may want to wrap the table inside a "div.dataTables_borderWrap" element

                        //"iDisplayLength": 50

                        select: {
                            style: 'multi'
                        }
                    });

            $.fn.dataTable.Buttons.defaults.dom.container.className = 'dt-buttons btn-overlap btn-group btn-overlap';

            new $.fn.dataTable.Buttons(myTable, {
                buttons: [{
                    "extend": "colvis",
                    "text": "<i class='fa fa-search bigger-110 blue'></i> <span class='hidden'>Show/hide columns</span>",
                    "className": "btn btn-white btn-primary btn-bold",
                    columns: ':not(:first):not(:last)'
                }, {
                    "extend": "copy",
                    "text": "<i class='fa fa-copy bigger-110 pink'></i> <span class='hidden'>Copy to clipboard</span>",
                    "className": "btn btn-white btn-primary btn-bold"
                }, {
                    "extend": "csv",
                    "text": "<i class='fa fa-database bigger-110 orange'></i> <span class='hidden'>Export to CSV</span>",
                    "className": "btn btn-white btn-primary btn-bold"
                }, {
                    "extend": "excel",
                    "text": "<i class='fa fa-file-excel-o bigger-110 green'></i> <span class='hidden'>Export to Excel</span>",
                    "className": "btn btn-white btn-primary btn-bold"
                }, {
                    "extend": "pdf",
                    "text": "<i class='fa fa-file-pdf-o bigger-110 red'></i> <span class='hidden'>Export to PDF</span>",
                    "className": "btn btn-white btn-primary btn-bold"
                }, {
                    "extend": "print",
                    "text": "<i class='fa fa-print bigger-110 grey'></i> <span class='hidden'>Print</span>",
                    "className": "btn btn-white btn-primary btn-bold",
                    autoPrint: false,
                    message: 'This print was produced using the Print button for DataTables'
                }]
            });
            myTable.buttons().container().appendTo($('.tableTools-container'));

            //style the message box
            var defaultCopyAction = myTable.button(1).action();
            myTable.button(1).action(function(e, dt, button, config) {
                defaultCopyAction(e, dt, button, config);
                $('.dt-button-info').addClass('gritter-item-wrapper gritter-info gritter-center white');
            });

            var defaultColvisAction = myTable.button(0).action();
            myTable.button(0).action(function(e, dt, button, config) {

                defaultColvisAction(e, dt, button, config);

                if ($('.dt-button-collection > .dropdown-menu').length == 0) {
                    $('.dt-button-collection')
                        .wrapInner('<ul class="dropdown-menu dropdown-light dropdown-caret dropdown-caret" />')
                        .find('a').attr('href', '#').wrap("<li />")
                }
                $('.dt-button-collection').appendTo('.tableTools-container .dt-buttons')
            });

            ////

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
