<select class="form-control" id="qb_reason" name="qb_reason" required>
  <option value="">Select</option>
  @foreach($reasons as $reason)
  	<option value="{{ $reason->sr_slid }}">{{ $reason->sr_reason }}</option>
  @endforeach
</select>