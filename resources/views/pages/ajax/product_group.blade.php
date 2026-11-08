<select class="form-control select2" id="product_model" name="type"  onchange="get_products(this.value)">
  <option value="">Select</option>
  @foreach($types as $type)
    <option value="{{ $type->inv_pro_type_id }}">{{ $type->inv_pro_type_name }}</option>
  @endforeach
</select>

<script>
    $('.select2').select2();
</script>