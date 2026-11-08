<select name="type" id="product_model" class="form-control basic-single">
    <option value="">Select One</option>
    @foreach($types as $type)
        <option value="{{ $type->inv_pro_type_id }}">
            {{ $type->inv_pro_type_name }}
        </option>
    @endforeach
</select>