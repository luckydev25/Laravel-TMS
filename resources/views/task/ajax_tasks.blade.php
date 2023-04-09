@foreach ( $tasks as $task)
<tr class="row1" data-id="{{ $task->id }}">
    <td>{{ $task->id }}</td>
    <td>{{ Carbon\Carbon::parse($task->created_at)->format('m-d-Y') }}</td>
    <td>{{ $task->task_title }} </td>

    <td>
        @if(Auth::user()->admin)
        @foreach( $users as $user)
        @if ( $user->id == $task->user_id )
        <a href="{{ route('user.list', [ 'id' => $user->id ]) }}">{{ $user->name }}</a>
        @endif
        @endforeach
        <span class="label label-jc">{{ $task->project->project_name }}</span>
        @else
        <a href="{{ route('user.list', [ 'id' => Auth::user()->id ]) }}">{{ Auth::user()->name }}</a>
        <span class="label label-jc">{{ $task->project->project_name }}</span>
        @endif
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
        <a href="{{ route('task.completed', ['id' => $task->id]) }}" class="btn btn-warning"> Mark as completed</a>
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