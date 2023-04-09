<?php
namespace App\Http\Controllers;

use Session;
use Illuminate\Http\Request;

// import our models
use App\Project;
use App\Task;
use App\TaskFiles;
use App\User; 
use Auth;

use Illuminate\Support\Facades\Input; 

class TaskController extends Controller
{
/*===============================================
    INDEX
===============================================*/
    public function index()
    {
        $users =  User::all() ; 
        $projects = Project::all();
        
        if(Auth::user()->admin) {
            $tasks  = Task::orderBy('order', 'asc')->paginate(10) ;  // Paginate Tasks 
        } else {
            $tasks  = Task::where('user_id', Auth::user()->id)->orderBy('order', 'asc')->paginate(10) ;  // Paginate Tasks 
        }
        

        return view('task.tasks')->with('tasks', $tasks) 
                                 ->with('users', $users )
                                 ->with('projects', $projects ) ;
    }

    public function reorder(Request $request) {
        $tasks = Task::all();

        foreach ($tasks as $task) {
            foreach ($request->order as $order) {
                if ($order['id'] == $task->id) {
                    $task->update(['order' => $order['position']]);
                }
            }
        }
        
        return response('Update Successfully.', 200);
    }

    public function filter(Request $request) {
        $projectid = $request->project_id;
        $users = User::all(); 
        $tasks = Auth::user()->admin? Task::where('project_id', $projectid)->get() : Task::where('project_id', $projectid)->where('user_id', Auth::user()->id)->get();
        
        return view('task.ajax_tasks')->with('tasks', $tasks)
                                      ->with('users', $users);
    }

/*===============================================
    LIST Tasks
===============================================*/
    public function tasklist( $projectid ) {

        $users =  User::all() ;
        $p_name = Project::find($projectid) ;
        $task_list = Task::where('project_id','=' , $projectid)->get();
        return view('task.list')->with('users', $users) 
                                ->with('p_name', $p_name)
                                ->with('task_list', $task_list) ;
    }

/*===============================================
    VIEW Task
===============================================*/
    public function view($id)  {
        $images_set = [] ;
        $files_set = [] ;
        $images_array = ['png','gif','jpeg','jpg'] ;
        // get task file names with task_id number
        $taskfiles = TaskFiles::where('task_id', $id )->get() ;

        if ( count($taskfiles) > 0 ) { 
            foreach ( $taskfiles as $taskfile ) {

                $taskfile = explode(".", $taskfile->filename ) ;
                if ( in_array($taskfile[1], $images_array ) ) 
                    $images_set[] = $taskfile[0] . '.' . $taskfile[1] ;
                else
                    $files_set[] = $taskfile[0] . '.' . $taskfile[1]; 
            }
        }



        $task_view = Task::find($id) ;

        // Get task created and due dates
        $from = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $task_view->created_at);
        $to   = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $task_view->duedate ); // add here the due date from create task

        $current_date = \Carbon\Carbon::now();
 
        // Format dates for Humans
        $formatted_from = $from->toRfc850String();  
        $formatted_to   = $to->toRfc850String();

        $diff_in_days = $current_date->diffInDays($to);
        $is_overdue = ($current_date->gt($to) ) ? true : false ;

        $projects = Project::all() ;
        return view('task.view')
            ->with('task_view', $task_view) 
            ->with('projects', $projects) 
            ->with('taskfiles', $taskfiles)
            ->with('diff_in_days', $diff_in_days )
            ->with('is_overdue', $is_overdue) 
            ->with('formatted_from', $formatted_from ) 
            ->with('formatted_to', $formatted_to )
            ->with('images_set', $images_set)
            ->with('files_set', $files_set) ;
    }

/*===============================================
    SORT TASKS
===============================================*/
    public function sort( $key ) {
        $users = User::all() ;
        // dd ($key) ; 
        switch($key) {
            case 'task':
                $tasks = Task::orderBy('task')->paginate(10); // replace get() with paginate()
            break;
            case 'priority':
                $tasks = Task::orderBy('priority')->paginate(10);
            break;
            case 'completed':
                $tasks = Task::orderBy('completed')->paginate(10);
            break;
        }

        return view('task.tasks')->with('users', $users)
                                ->with('tasks', $tasks) ;
    }

/*===============================================
    CREATE TASK
===============================================*/
    public function create()
    {
        $projects = Project::all()  ;
        $users = User::all() ;
        return view('task.create')->with('projects', $projects) 
                                  ->with('users', $users) ;        
    }

/*===============================================
    STORE NEW TASK
===============================================*/
    public function store(Request $request)
    {
        $tasks_count = Task::count() ;
        
        if ( $tasks_count < 20  ) { 

            $this->validate( $request, [
                'task_title' => 'required',
                'task'       => 'required',
                'project_id' => 'required|numeric',
                'photos.*'   => 'sometimes|required|mimes:png,gif,jpeg,jpg,txt,pdf,doc',  // photos is an array: photos.*
                'duedate'    => 'required'
            ]) ;

            // First save Task Info
            $task = Task::create([
                'project_id' => $request->project_id,
                'user_id'    => $request->user,
                'task_title' => $request->task_title,
                'task'       => $request->task,
                'priority'   => $request->priority,
                'duedate'    => $request->duedate
            ]);

            // Then save files using the newly created ID above
            if( $request->hasFile('photos') ) {
                foreach ($request->photos as $file) {
                    $filename = strtr( pathinfo( time() . '_' . $file->getClientOriginalName(), PATHINFO_FILENAME) , [' ' => '', '.' => ''] ) . '.' . pathinfo($file->getClientOriginalName(), PATHINFO_EXTENSION);
                    $file->move('images',$filename);

                    // save to DB
                    TaskFiles::create([
                        'task_id'  => $task->id, // newly created ID
                        'filename' => $filename  // For Regular Public Images
                    ]);
                }
            }
    
            Session::flash('success', 'Task Created') ;
            return redirect()->route('task.show') ; 
        }
        
        else {
            Session::flash('info', 'Please delete some tasks, Demo max tasks: 20') ;
            return redirect()->route('task.show') ;         
        }

    }

/*===============================================
    MARK TASK AS COMPLETED
===============================================*/
    public function completed($id)
    {
        $task_complete = Task::find($id) ;
        $task_complete->completed = 1;
        $task_complete->save() ;
        return redirect()->back();
    }

/*===============================================
    EDIT TASK
===============================================*/
    public function edit($id)
    {
        $task = Task::find($id)  ; 
        $taskfiles = TaskFiles::where('task_id', '=', $id)->get() ;
        $projects = Project::all() ;
        $users = User::all() ;
        return view('task.edit')->with('task', $task)
                                ->with('projects', $projects ) 
                                ->with('users', $users)
                                ->with('taskfiles', $taskfiles);
    }

/*===============================================
    UPDATE TASK
===============================================*/
    public function update(Request $request, $id)
    {
        $update_task = Task::find($id) ;

        $this->validate( $request, [
            'task_title' => 'required',
            'task'       => 'required',
            'project_id' => 'required|numeric',
            'photos.*'   => 'sometimes|required|mimes:png,gif,jpeg,jpg,txt,pdf,doc' // photos is an array: photos.*
        ]) ;

        $update_task->task_title = $request->task_title; 
        $update_task->task       = $request->task;
        $update_task->user_id    = $request->user_id;
        $update_task->project_id = $request->project_id;
        $update_task->priority   = $request->priority;
        $update_task->completed  = $request->completed;
        $update_task->duedate    = $request->duedate;

        if( $request->hasFile('photos') ) {
            foreach ($request->photos as $file) {
                // remove whitespaces and dots in filenames : [' ' => '', '.' => ''] 
                $filename = strtr( pathinfo( time() . '_' . $file->getClientOriginalName(), PATHINFO_FILENAME) , [' ' => '', '.' => ''] ) . '.' . pathinfo($file->getClientOriginalName(), PATHINFO_EXTENSION);
                $file->move('images',$filename);

                // save to DB
                TaskFiles::create([
                    'task_id'  => $request->task_id,
                    'filename' => $filename  // For Regular Public Images
                ]);
            }        
        }

        $update_task->save() ;
        
        Session::flash('success', 'Task was sucessfully edited') ;
        return redirect()->route('task.show') ;
    }

/*===============================================
    DESTROY TASK
===============================================*/
    public function destroy($id)
    {
        $delete_task = Task::find($id) ;
        $delete_task->delete() ;
        Session::flash('success', 'Task was deleted') ;
        return redirect()->back();
    }

/*===============================================
    DELETE FILE
===============================================*/
    public function deleteFile($id) {
        $delete_file = TaskFiles::find($id) ;
        // remove  file from public directory
        unlink( public_path() . '/images/' . $delete_file->filename ) ;

        // delete entry from database
        $delete_file->delete() ;
        Session::flash('success', 'File Deleted') ;
        return redirect()->back(); 
    }

/*===============================================
    SEARCH TASK
===============================================*/
    public function searchTask() {
        $value = Input::get('search_task');
        $tasks = Task::where('task', 'LIKE', '%' . $value . '%')->limit(25)->get();

        return view('task.search', compact('value', 'tasks')  ) ;
    }


}
