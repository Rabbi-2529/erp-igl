<select class="form-control select2" id="product_name" name="product" required>
        <option value="">Select</option>
        @foreach($types as $type)
          <option value="{{ $type->inv_pro_det_id }}">{{ $type->inv_pro_det_pro_id }}</option>
        @endforeach
</select>
      

<script>
    $('.select2').select2();
</script>