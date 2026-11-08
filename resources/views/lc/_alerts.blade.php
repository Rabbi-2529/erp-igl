{{-- Error messages shared by the LC pages (success messages, session 'msg', are shown by layouts.message) --}}
@if(Session::has('errmsg'))
<div class="alert alert-danger alert-dismissible" role="alert">
    {{ session('errmsg') }}
    <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
</div>
@endif
@if($errors->any())
<div class="alert alert-danger alert-dismissible" role="alert">
    <ul style="margin-bottom:0;">
        @foreach($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
    <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
</div>
@endif
