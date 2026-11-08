<select class="form-control select2" id="currency_model" name="currency" required>
    <option value="">Select</option>
      <option value="{{ $country->id }}">{{ $country->currency_code }}</option>
</select>