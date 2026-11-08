<input type="text" hidden value="{{ $ledg_cat_edit->inv_ledg_cat_cat_id }}" name="cat_id">
<div class="form-group">
    <label for="name">Name</label>
    <input type="text" value="{{ $ledg_cat_edit->inv_ledg_cat_category_name }}" class="form-control" name="cat_name">
</div>
<div class="form-group">
    <label for="status">Status</label><br>
    <input type="radio" name="status" value="1" {{ ($ledg_cat_edit->inv_ledg_cat_status==1)?'checked':'' }}> Active
    <input type="radio" name="status" value="0" {{ ($ledg_cat_edit->inv_ledg_cat_status==0)?'checked':'' }}> Deactive
</div>
