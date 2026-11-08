<select class="form-control" id="expense" name="expense" required>
  <option value="">-- Select A Category First --</option>
  @foreach($expenses as $expense)
  	<option value="{{ $expense->inv_acc_exp_id }}">{{ $expense->inv_acc_exp_expense_name }}</option>
  @endforeach
</select>