<select class="form-control select2" id="product_model" name="type" required>
        <option value="">Select</option>
        @foreach($types as $type)
          <option value="{{ $type->inv_pro_type_id }}">{{ $type->inv_pro_type_name }}</option>
        @endforeach
</select>
      