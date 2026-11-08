<select name="engineer[]" class="duallistbox" multiple="multiple" required>
    @foreach ($users as $user)
        <option value="{{ $user->au_id }}">{{ $user->au_name }}</option>
    @endforeach
</select>

<link rel="stylesheet" href="{{asset('asset/plugins/bootstrap-duallistbox.min.css')}}">
<script>
     $('.duallistbox').bootstrapDualListbox()
</script>