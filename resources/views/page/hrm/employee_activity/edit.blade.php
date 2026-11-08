@extends('layouts.master')

@section('title', 'Edit Employee Activity')

@section('human_resource_class', 'active')
@section('employee_activity', 'active')
@section('employee_activity_edit', 'active')

@section('content')
<div class="row">
    <div class="col-sm-12">
        <h1>Employee Activity Management</h1>
        <div class="panel panel-bd lobidrag">
            <div class="panel-heading">
                <div class="panel-title">
                    <h4>Edit Employee Activity</h4>
                </div>
            </div>

            <div class="panel-body">
                {{ Form::model($activity, ['route' => ['employee-activity.update', $activity->id], 'method' => 'put', 'class' => 'form-horizontal', 'id' => 'activity_edit_form']) }}
                <div class="box-body">
                    
                    <div class="form-group">
                        <label for="activity_date" class="col-sm-2 control-label">Activity Date <b class="text-danger">*</b></label>
                   <div class="col-sm-6">
                        <input type="date" name="activity_date" value="{{ old('activity_date', $activity->activity_date) }}" class="form-control" required readonly>
                        @if($errors->has('activity_date'))
                            <p class="text-danger">{{ $errors->first('activity_date') }}</p>
                        @endif
                    </div>

                    </div>

                    <div class="form-group">
                        <label for="w_title" class="col-sm-2 control-label">Activity Title <b class="text-danger">*</b></label>
                        <div class="col-sm-6">
                            <input type="text" name="w_title" autocomplete="off" value="{{ old('w_title', $activity->w_title) }}" class="form-control" placeholder="Enter Activity Title" required>
                            @if($errors->has('w_title'))
                            <p class="text-danger">{{ $errors->first('w_title') }}</p>
                            @endif
                        </div>
                    </div>

             <div class="form-group">
    <label for="w_d" class="col-sm-2 control-label">Working Description</label>
    <div class="col-sm-6">
        <textarea name="w_d" class="form-control" id="w_d" placeholder="Enter Working Description">{{ old('w_d', strip_tags($activity->w_d)) }}</textarea>
        @if($errors->has('w_d'))
        <p class="text-danger">{{ $errors->first('w_d') }}</p>
        @endif
    </div>
</div>


                    <!-- Adding the Sell Amount Field -->
                    <div class="form-group">
                        <label for="sell_amount" class="col-sm-2 control-label">Sell Amount</label>
                        <div class="col-sm-6">
                            <input type="number" name="sell_amount" step="0.01" value="{{ old('sell_amount', $activity->sell_amount) }}" class="form-control" placeholder="Enter Sell Amount">
                            @if($errors->has('sell_amount'))
                            <p class="text-danger">{{ $errors->first('sell_amount') }}</p>
                            @endif
                        </div>
                    </div>

                </div>

                <div class="box-footer">
                    <div class="col-sm-3">
                        <button type="submit" class="btn btn-info pull-right">Update</button>
                    </div>
                </div>

                {{ Form::close() }}
            </div>
        </div>
    </div>
</div>
@endsection
@section('custom_script')
<script src="https://cdn.ckeditor.com/ckeditor5/36.0.1/classic/ckeditor.js"></script>
<script>
ClassicEditor
    .create(document.querySelector('#w_d'), {
        // 1. Remove formatting capabilities
        removePlugins: [
            'Heading', 'Bold', 'Italic', 'Underline', 'Strikethrough',
            'FontFamily', 'FontSize', 'FontColor', 'FontBackgroundColor'
        ],
        
        // 2. Minimal toolbar (only paragraph-friendly tools)
        toolbar: {
            items: [
                '|',
                'bulletedList',
                'numberedList',
                '|',
                'blockQuote',
                '|',
                'undo',
                'redo'
            ]
        },

        // 3. Force paragraph-only content
        heading: {
            options: [
                { 
                    model: 'paragraph', 
                    title: 'Paragraph', 
                    class: 'ck-heading_paragraph',
                    isDefault: true 
                }
            ]
        },

        // 4. Strict paste handling
        pastePlainText: true,
        htmlSupport: {
            allow: [
                {
                    name: 'p', // Only allow paragraph tags
                    attributes: true,
                    classes: true
                },
                {
                    name: ['ul', 'ol', 'li'], // Allow basic lists
                    attributes: true,
                    classes: true
                }
            ]
        },

        // 5. Disable media
        image: { toolbar: [], upload: { types: [] } },
        mediaEmbed: { removeProviders: ['*'], previewsInData: false }
    })
    .catch(error => {
        console.error('CKEditor error:', error);
    });
</script>
@endsection
