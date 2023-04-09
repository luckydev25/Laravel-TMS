@extends('layout')

@section('styles')
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/dt/dt-1.10.12/datatables.min.css" />
@stop

@section('content')

<div class="row">
    <div class="col-md-6">
        <h1>ALL TASKS</h1>
    </div>

    <div class="col-md-6 flex-center">
        <div>
            <form action="{{ route('task.search') }}" class="navbar-form" role="search" method="GET">
                <div class="input-group">
                    <input type="text" class="form-control" placeholder="Search in Tasks..." name="search_task">
                    <span class="input-group-btn">
                        <button type="submit" class="btn btn-default">
                            <span class="glyphicon glyphicon-search">
                                <span class="sr-only">Search...</span>
                            </span>
                        </button>
                    </span>
                </div>
            </form>
        </div>
        <div>
            <select name="project_id" class="selectpicker" id="project_tasks" data-style="btn-primary">
                <option value="0">All</option>
                @foreach( $projects as $project )
                <option value="{{ $project->id }}">{{ $project->project_name }}</option>
                @endforeach
            </select>
        </div>
    </div>
</div>

<div class="table-responsive">
    <table id="table" class="table table-striped">
        <thead>
            <tr>
                <th>No</th>
                <th>Created At</th>
                <th><a href="{{ route('task.sort', [ 'key' => 'task' ]) }}">Task Title <span
                            class="glyphicon glyphicon-sort-by-alphabet" aria-hidden="true"></span> </a></th>
                <th>Assigned To / Project</th>
                <th><a href="{{ route('task.sort', [ 'key' => 'priority' ]) }}">Priority <span
                            class="glyphicon glyphicon-sort-by-alphabet" aria-hidden="true"></span> </a></th>
                <th><a href="{{ route('task.sort', [ 'key' => 'completed' ]) }}">Status <span
                            class="glyphicon glyphicon-sort-by-alphabet" aria-hidden="true"></span> </a></th>
                <th>Actions</th>
            </tr>
        </thead>

        @if ( !$tasks->isEmpty() )
        <tbody id="tablecontents">
            @foreach ( $tasks as $task)
            <tr class="row1" data-id="{{ $task->id }}">
                <td>{{ $task->id }}</td>
                <td>{{ Carbon\Carbon::parse($task->created_at)->format('m-d-Y') }}</td>
                <td>{{ $task->task_title }} </td>

                <td>

                    @foreach( $users as $user)
                    @if ( $user->id == $task->user_id )
                    <a href="{{ route('user.list', [ 'id' => $user->id ]) }}">{{ $user->name }}</a>
                    @endif
                    @endforeach
                    <span class="label label-jc">{{ $task->project->project_name }}</span>

                </td>

                <td>
                    @if ( $task->priority == 0 )
                    <span class="label label-info">Normal</span>
                    @else
                    <span class="label label-danger">High</span>
                    @endif
                </td>
                <td>
                    @if ( !$task->completed )
                    <a href="{{ route('task.completed', ['id' => $task->id]) }}" class="btn btn-warning"> Mark as
                        completed</a>
                    <span class="label label-danger">{{ ( $task->duedate < Carbon\Carbon::now() )  ? "!" : "" }}</span>
                    @else
                    <span class="label label-success">Completed</span>
                    @endif
                </td>
                <td>
                    <a href="{{ route('task.view', ['id' => $task->id]) }}" class="btn btn-primary"><span
                            class="glyphicon glyphicon-eye-open" aria-hidden="true"></span></a>
                    @if(Auth::user()->admin)
                    <a href="{{ route('task.delete', ['id' => $task->id]) }}" class="btn btn-danger"><span
                            class="glyphicon glyphicon-trash" aria-hidden="true"></span></a>
                    @endif

                </td>
            </tr>

            @endforeach
        </tbody>

        {{ $tasks->links() }}

        @else
        <p><em>There are no tasks assigned yet</em></p>
        @endif

    </table>
</div>


@stop

@section('scripts')
<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.10.3/jquery-ui.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/v/dt/dt-1.10.12/datatables.min.js"></script>

<script>
$(document).ready(function() {
    $('#project_tasks').on('change', function() {
        if ($(this).val() != "0") {
            $.ajax({
                type: "get",
                url: "{{ route('task.filter') }}",
                data: {
                    project_id: $(this).val()
                },
                success: function(response) {
                    $('#tablecontents').html(response);
                }
            });
        } else {
            location.reload();
        }
    });

    @if(Auth::user() - > admin)
    $("#tablecontents").sortable({
        items: "tr",
        cursor: 'move',
        opacity: 0.6,
        update: function() {
            sendOrderToServer();
        }
    });

    function sendOrderToServer() {
        var order = [];
        var token = '{{csrf_token()}}';
        $('tr.row1').each(function(index, element) {
            order.push({
                id: $(this).attr('data-id'),
                position: index + 1
            });
        });

        $.ajax({
            type: "POST",
            dataType: "json",
            url: "{{ route('task.reorder') }}",
            data: {
                order: order,
                _token: token
            },
            success: function(response) {
                if (response.status == "success") {
                    console.log(response);
                } else {
                    console.log(response);
                }
            }
        });
    }
    @endif
})
</script>
@stop