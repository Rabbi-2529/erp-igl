
<div class="row">
	<div class="col-sm-12">
		<b>Expense Name: </b> 
		{{ App\Inv_acc_expense::getExpenseNameById(request()->exp_id)}}
		 <br>
		
		
	</div>
</div>

<div class="row" style="margin-top: 20px;">
	<div class="col-sm-12">

	

		<table id="example1" class="table table-bordered table-striped">
			<thead>
				<tr>
					<th style="text-align: center;">SL</th>
					<th style="text-align: center;">Date</th>
					<th style="text-align: center;">Reference</th>
                    <th style="text-align: center;">Amount</th>
                   
                </tr>
               
			</thead>
			<tbody>
				@php($total=0)

				@foreach($expenses as $expense)
				@php($total+=$expense->inv_abs_debit)
				<tr>
					<td style="text-align: center;">{{ $loop->iteration }}</td>
                    <td> 
                    	{{ \Carbon\Carbon::parse($expense->inv_abs_transaction_date)->format('Y-m-d') }}</td>
                    <td>
                   		{{$expense->inv_abs_description}} 		
                   	</td>
                  
                    <td class="text-right">
                      {{ number_format($expense->inv_abs_debit,2)}}
                    </td>
				</tr>
				@endforeach
			</tbody>
			<tfoot>
				<tr>
					<td style="text-align: center; font-weight: bolder;">#</td>
					<td style="text-align: right; font-weight: bolder;" colspan="2">Total:</td>
					<td style="text-align: right; font-weight: bolder;">
						{{number_format($total,2)}}
					</td>
				</tr>
			</tfoot>
		</table>
	</div>
</div>
