<table id="example" class="display nowrap" style="width:100%">
    <thead>
        <tr>
            <th>SL</th>
            <th>User Name</th>
            <th>Date</th>
            <th>Amount</th>
        </tr>
    </thead>
    <tbody>
        @php($total = 0)
        @foreach ($userHistory as $history)
        <tr>
            <td>{{ $loop->iteration }}</td>
            <td>{{ $history->userDetails->au_name }}</td>
            <td>{{ $history->created_at }}</td>
            <td class="text-right">{{ $history->amount }}</td>
        </tr>
        @php($total = $total + $history->amount)
        @endforeach
       
    </tbody>
    <tfoot>
        <tr>
            <th colspan="3">Total</th>
            <th class="text-right">{{ number_format($total,2) }}</th>
        </tr>
    </tfoot>
</table>

<link rel="stylesheet" href="https://cdn.datatables.net/1.11.1/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/rowreorder/1.2.8/css/rowReorder.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.dataTables.min.css">





{{--<script src="https://code.jquery.com/jquery-3.5.1.js"></script>--}}
<script src="https://cdn.datatables.net/1.11.1/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/rowreorder/1.2.8/js/dataTables.rowReorder.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>

<script>
    $(document).ready(function() {
    var table = $('#example').DataTable( {
        rowReorder: {
            selector: 'td:nth-child(2)'
        },
        responsive: true
    } );
} );
</script>




