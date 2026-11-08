<select class="form-control select2" id="product_model" name="type">
  <option value="">Select</option>
  @foreach($notes as $note)
    <option value="{{ $note->id }}">
      ({{ $note->country_name['currency_symbol'] }}){{ $note->currency_note }}
    </option>
  @endforeach
</select>

<script>
    $('.select2').select2();
</script>