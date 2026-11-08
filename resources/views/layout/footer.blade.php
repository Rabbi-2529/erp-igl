<footer class="main-footer">
    <strong>Copyright &copy; {{date('Y')}} <a href="https://iglweb.com/web/" target="_blank">IGL Web</a><br>
    Version 2.0.2
</footer>


<script src="{{ asset('asset/bower_components/jquery/dist/jquery.min.js')}}"></script>
<script src="{{ asset('asset/bower_components/jquery-ui/jquery-ui.min.js')}}"></script>
<script src="{{ asset('asset/bower_components/raphael/raphael.min.js')}}"></script>
<script src="{{ asset('asset/bower_components/morris.js/morris.min.js')}}"></script>
<script src="{{ asset('asset/bower_components/jquery-sparkline/dist/jquery.sparkline.min.js')}}"></script>
<script>
  $.widget.bridge('uibutton', $.ui.button);
</script>
<script src="{{ asset('asset/plugins/jvectormap/jquery-jvectormap-1.2.2.min.js')}}"></script>
<script src="{{ asset('asset/plugins/jvectormap/jquery-jvectormap-world-mill-en.js')}}"></script>
<script src="{{ asset('asset/bower_components/jquery-knob/dist/jquery.knob.min.js')}}"></script>
<script src="{{ asset('asset/bower_components/moment/min/moment.min.js')}}"></script>
<script src="{{ asset('asset/bower_components/bootstrap-daterangepicker/daterangepicker.js')}}"></script>
<script src="{{ asset('asset/bower_components/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js')}}"></script>
<script src="{{ asset('asset/plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.all.min.js')}}"></script>
<script src="{{ asset('asset/bower_components/jquery-slimscroll/jquery.slimscroll.min.js')}}"></script>
<script src="{{ asset('asset/dist/js/pages/dashboard.js')}}"></script>
<!-- Bootstrap 3.3.7 -->
<script src="{{ asset('asset/bower_components/bootstrap/dist/js/bootstrap.min.js')}}"></script>
<script src="{{ asset('asset/bower_components/datatables.net/js/jquery.dataTables.min.js')}}"></script>
<script src="{{ asset('asset/bower_components/datatables.net-bs/js/dataTables.bootstrap.min.js')}}"></script>
<script type="text/javascript" src="{{ asset('asset/js/dataTables.rowReorder.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('asset/js/dataTables.responsive.min.js') }}"></script>

<!-- FastClick -->
<script src="{{ asset('asset/bower_components/fastclick/lib/fastclick.js')}}"></script>
<!-- AdminLTE App -->
<script src="{{ asset('asset/dist/js/adminlte.min.js')}}"></script>
<script src="{{ asset('asset/bower_components/select2/dist/js/select2.min.js')}}"></script>
<!--Datatable-->
<!-- AdminLTE for demo purposes -->
<script src="{{ asset('asset/dist/js/demo.js')}}"></script>
<!-- jQuery 3 -->
<script type="text/javascript">
$('.select2').select2();
$(document).ready(function() {
    var table = $('#example1').DataTable( {
        rowReorder: {
            selector: 'td:nth-child(2)'
        },
        responsive: true
    } );
} );
</script>
<script type="text/javascript">

            function show_terget(value){
            // alert(e);
            if(value == '5'){
                $('#team_leader').show();
                $('#user').hide();
                // $('.img-withdraw').attr('src','img/admin.png');
            }
            else if(value == '6'){
                $('#user').show();
                $('#team_leader').hide();

            } else if(value == '3'){
                $('#target_time').show();
            }

						function insertId(id)
						{
							alert(id);
								document.getElementById('number').value = id;
						}
        }
       </script>
    <script>

$(document).ready(function(){
  var date = new Date();
  date.setDate(date.getDate());
    $( "#from" ).datepicker({
           daysOfWeekHighlighted: "7",
           startDate: date,
           autoclose: true,
           todayHighlight: true,
         });
    $( "#to" ).datepicker({
           daysOfWeekHighlighted: "7",
           todayHighlight: true,
         });
});
</script>
<script type="text/javascript">
       $(document).ready(function() {
        $(".chosen").chosen();
           $("#cf_showDate").datepicker({
            todayBtn:'linked',
        format: "yyyy-mm-dd",
        autoclose: true,
        todayHighlight: true,


           });
         } );
    </script>
