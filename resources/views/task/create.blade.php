@extends('layout')


@section('styles')
<link rel="stylesheet" href="{{ asset('css/bootstrap-datepicker.min.css') }}">
@stop

@section('content')

@include('includes.errors')

<form id="task_form" action="{{ route('task.store') }}" method="POST" enctype="multipart/form-data">
    {{ csrf_field() }}

    <div class="col-md-8">
        <label>Create new task <span class="glyphicon glyphicon-plus" aria-hidden="true"></span></label>

        <div class="form-group">
            <input type="text" class="form-control" placeholder="Enter Task Title" name="task_title">
        </div>

        <label>Add Project Files (png,gif,jpeg,jpg,txt,pdf,doc) <span class="glyphicon glyphicon-file"
                aria-hidden="true"></span></label>
        <div class="form-group">
            <input type="file" class="form-control" name="photos[]" multiple>
        </div>

        <div class="form-group">
            <textarea class="form-control my-editor" rows="10" id="task" name="task"></textarea>
        </div>

    </div>

    <div class="col-md-4">
        <div class="form-group">
            <label>Assign to Project <span class="glyphicon glyphicon-pushpin" aria-hidden="true"></span></label>
            <select name="project_id" class="selectpicker" data-style="btn-primary" style="width:100%;">
                @foreach( $projects as $project )
                <option value="{{ $project->id }}">{{ $project->project_name }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label>Assign to: <span class="glyphicon glyphicon-user" aria-hidden="true"></span></label>
            <select id="user" name="user" class="selectpicker" data-style="btn-info" style="width:100%;">
                @foreach ( $users as $user)
                <option value="{{ $user->id }}">{{ $user->name }}</option>
                @endforeach

            </select>
        </div>

        <div class="form-group">
            <label>Select Priority <span class="glyphicon glyphicon-warning-sign" aria-hidden="true"></span></label>
            <select name="priority" class="selectpicker" data-style="btn-info" style="width:100%;">
                <option value="0">Normal</option>
                <option value="1">High</option>
            </select>
        </div>

        <div class="form-group">
            <label>Select Due Date <span class="glyphicon glyphicon-calendar" aria-hidden="true"></span></label>
            <div class='input-group date' id='datetimepicker1'>
                <input type='text' class="form-control" name="duedate">
                <span class="input-group-addon">
                    <span class="glyphicon glyphicon-calendar"></span>
                </span>
            </div>
        </div>

        <div class="btn-group">
            <input class="btn btn-primary" type="submit" value="Submit" onclick="return validateForm()">
            <a class="btn btn-default" href="{{ redirect()->getUrlGenerator()->previous() }}">Go Back</a>
        </div>

    </div>

</form>

@stop




@section('scripts')

<script src="{{ asset('js/moment.js') }}"></script>

<script src="{{ asset('js/bootstrap-datepicker.min.js') }}"></script>

<script src="https://cdn.tiny.cloud/1/0xxib31wkc7o83w1b52h1hjhad815erokk42rej17mdafbn1/tinymce/6/tinymce.min.js"
    referrerpolicy="origin"></script>

<script>
jQuery(document).ready(function() {

    jQuery(function() {
        jQuery('#datetimepicker1').datetimepicker({
            defaultDate: 'now', // defaults to today
            format: 'YYYY-MM-DD hh:mm:ss', // YEAR-MONTH-DAY hour:minute:seconds
            minDate: new Date() // Disable previous dates, minimum is todays date
        });
    });
});
</script>

<script>
var editor_config = {
    selector: 'textarea',
    plugins: 'anchor autolink charmap codesample emoticons image link lists media searchreplace table visualblocks wordcount checklist mediaembed casechange export formatpainter pageembed linkchecker a11ychecker tinymcespellchecker permanentpen powerpaste advtable advcode editimage tinycomments tableofcontents footnotes mergetags autocorrect typography inlinecss',
    toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | link image media table mergetags | addcomment showcomments | spellcheckdialog a11ycheck typography | align lineheight | checklist numlist bullist indent outdent | emoticons charmap | removeformat',
    tinycomments_mode: 'embedded',
    tinycomments_author: 'Author name',
    mergetags_list: [{
            value: 'First.Name',
            title: 'First Name'
        },
        {
            value: 'Email',
            title: 'Email'
        },
    ]
};

tinymce.init(editor_config);
</script>


@stop