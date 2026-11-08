@if (session()->has('message'))
    <div class="x_content bs-example-popovers">
        <div class="alert alert-{{ session()->get('type') }} alert-dismissible " role="alert">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span>
            </button>
            <strong>{{ session()->get('message') }}</strong>
        </div>
    </div>
@endif