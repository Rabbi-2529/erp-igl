@extends('layout.master')
@section('content')
<section class="content">
  <meta name="" http-equiv="refresh" content="300">
<section class="content-header">
      <h1>
        Todays Follow Up
      </h1>
    </section>
	<div class="box">
            <div class="box-header">
              <h3 class="box-title">Followup Customers List ({{ date('d-m-Y') }})</h3>
            </div>
            <!-- /.box-header -->
            <div class="box-body">
              <table id="example" class="table table-bordered table-striped">
                <thead>
                <tr>
                  <th>SL</th>
                  <th>Follow Up</th>
                  <th>Cus ID</th>
                  <th>Mobile</th>
                  <th>Name</th>
                  <th>Company Name</th>
                  <th>Reason</th>
                  <th>Type</th>
                  <th>Result</th>
                  <th>Feedback</th>
                 <!--<th class="text-center">Action</th>-->
                </tr>
                </thead>
                <tbody>
                @php($sl=0)
                @foreach( $todays as $today )
                <tr>
                  <td>{{ ++$sl }}</td>
                  @if( ($today->cus_mobile['qb_staff_id'] == '') ||  ($today->cus_mobile['qb_staff_id'] == 0))
                  <td>{{ $today->cus_mobile->entry_info['au_name'] }}</td>
                  @else
                  <td>{{ $today->cus_mobile->entry_staff['au_name'] }}<br>( Admin Reference )</td>
                  @endif

                  <td>{{ $today->cus_mobile['qb_serial'] }}</td>
                  <td>
                    <a href="{{ url('/client_feedback',['id' => $today->cus_mobile['qb_id']]) }}" title="{{ $today->cus_mobile['qb_company_name'] }}" class="opener" target="_blank">
                      {{ $today->cus_mobile['qb_mobile'] }}
                    </a>
                  </td>
                  <td>{{ $today->cus_mobile['qb_name'] }}</td>
                  <td>{{ $today->cus_mobile['qb_company_name'] }}</td>
                  <td>{{ @$today->cus_mobile->reas_info['sr_reason'] }}</td>
                  <td>
                      @if ($today->cus_mobile['qb_cus_type'] == 1 || $today->cus_mobile['qb_cus_type'] == 0)
                        <span class="text-success">Potential</span>
                     @elseif($today->cus_mobile['qb_cus_type'] == 2)
                        <span class="text-warning">Pipeline</span>
                     @elseif($today->cus_mobile['qb_cus_type'] == 3)
                        <span class="text-primary">Regular</span>
                     @endif
                  </td>
                  <td>
                    @for( $i=1; $i<=$today->cf_result; ++$i )
                      <b style="color: green; font-size: 20px;">*</b>
                    @endfor
                  </td>
                  <td>{{ $today->feedbackinfo['fm_msg'] }}</td>
                  <!-- <td align="center">
                    <a href="{{ url('/client_feedback',['id' => $today->cus_mobile['qb_id']]) }}" class="btn btn-info btn-sm opener" target="_blank">
                      Follow Up
                    </a>
                  </td> -->
                </tr>
                @endforeach
                </tbody>
              </table>
            </div>
            <!-- /.box-body -->
          </div>
         </section>
@endsection
@section('custom_script')
<script type="text/javascript">
  window.onload = function(){
  var a = document.querySelectorAll('.opener'), w = [], url, random, i;
  for(i = 0; i < a.length; i++){
    (function(i){
      a[i].onclick = function(e) {
        if (!w[i] || w[i].closed) {
          url = this.href;
          random = Math.floor((Math.random() * 100) + 1);
          w[i] = window.open(url);
        } else {
          console.log('window ' + url + ' is already opened');
        }
        e.preventDefault();
        w[i].focus();
      };
    })(i);
  }
  };

  $(document).ready(function() {
    var table = $('#example').DataTable( {
        rowReorder: {
            selector: 'td:nth-child(2)'
        },
        responsive: true
    } );
} );
</script>
@endsection
