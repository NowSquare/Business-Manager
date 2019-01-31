<?php namespace Platform\Controllers\App;

use Platform\Controllers\Core;

use Kris\LaravelFormBuilder\FormBuilder;
use Kris\LaravelFormBuilder\FormBuilderTrait;

use App\Forms\Project as ProjectForm;

use Money\Money;

//use Illuminate\Support\Facades\Mail;
//use App\Mail\NotifyProjectCreate;

use App\Exports\ProjectsExport;

class ProjectController extends \App\Http\Controllers\Controller {

  use FormBuilderTrait;

  /*
   |--------------------------------------------------------------------------
   | Project Controller
   |--------------------------------------------------------------------------
   */

  /**
   * Projects list
   */

  public function getProjectList(FormBuilder $formBuilder) {

    // Generate form
    $form = $formBuilder->create('App\Forms\Project', [
      'language_name' => 'g',
      'data' => ['ajax' => url('projects/json')]
    ]);

    $columns = [];

    foreach ($form->getFields() as $field) {
      $options = $field->getOptions();

      if (isset($options['crud'])) {
        if (isset($options['crud']['list']['visible']) && $options['crud']['list']['visible']) {
          if (! isset($options['crud']['list']['column']) || $options['crud']['list']['column']) {
            $column = $options['crud']['list'];
            $column['name'] = $options['label'];
            $column['options'] = $options;

            $columns[] = $column;
          }
        }
      }
    }

    $columns = collect($columns)->sortBy('sort')->values()->toArray();

    return view('app.projects.list-projects', compact('columns', 'form'));
  }

  /**
   * Projects list json
   */

  public function getProjectListJson(FormBuilder $formBuilder) {
    // DataTables parameters
    $order_by = request()->input('order.0.column', 1);
    //$order_by--;
    $order = request()->input('order.0.dir', 'asc');
    $search = request()->input('search.regex', '');
    $q = request()->input('search.value', '');
    $start = request()->input('start', 0);
    $draw = request()->input('draw', 1);
    $length = request()->input('length', 10);
    if ($length == -1) $length = 1000;
    $data = array();

    // Get form
    $form = $formBuilder->create('App\Forms\Project', [
      'language_name' => 'g'
    ]);

    $table = 'projects';
    $select_columns = [];
    $select_columns[] = $table . '.id';
    $select_columns[] = $table . '.active';
    $select_columns[] = $table . '.created_at';
    $search_columns = [];
    $columns = [];

    foreach ($form->getFields() as $field) {
      $options = $field->getOptions();

      if (isset($options['crud'])) {
        if (isset($options['crud']['list']['visible']) && $options['crud']['list']['visible']) {
          $column = $options['crud']['list'];

          if ($field->getType() == 'image') {
            $column_name = $options['real_name'] . '_file_name';
          } else {
            $column_name = $options['real_name'];
          }

          $column['column_name'] = $column_name;
          $column['name'] = $options['label'];
          $column['options'] = $options;

          $columns[] = $column;
          $select_columns[] = $table . '.' . $column_name;

          if (isset($options['crud']['list']['search']) && $options['crud']['list']['search']) {
            $search_columns[] = $table . '.' . $column_name;
          }
        }
      }
    }

    $columns = collect($columns)->sortBy('sort')->values()->toArray();

    $sort_by = null;
    if ($order_by == 3) {
      $sort_by = 'task_progress';
    }

    $order_by = (isset($columns[$order_by]['options']['real_name'])) ? $columns[$order_by]['options']['real_name'] : null;

    // Query model
    $query = \Platform\Models\Project::select(array_merge($select_columns, ['company_id']))->with(['status:id,name,color', 'client:id,name']);

    // Filter projects user can see
    if (! auth()->user()->can('all-projects')) {
      // Client company users
      $query = $query->whereHas('client', function($query) {
        $query->whereHas('users', function($query) {
          $query->where('users.id', auth()->user()->id);
        });
      });
      // Project managers
      $query = $query->orWhereHas('managers', function($query) {
        $query->where('users.id', auth()->user()->id);
      });
      // Users with task
      $query = $query->orWhereHas('tasks', function($query) {
        $query->whereHas('assignees', function($query) {
          $query->where('users.id', auth()->user()->id);
        });
      });
    }

    // Filter status
    $project_status_id = request()->input('columns.1.search.value', 0);

    if ($project_status_id != 0) {
      $query = $query->where('project_status_id', $project_status_id);
    }

    $count = $query->where(function ($query) use($q, $search_columns) {
        if($q != '') {
          foreach ($search_columns as $search_column) {
            $query->orWhere($search_column, 'like', '%' . $q . '%');
          }
        }
      })->count();

    $records = $query->orderBy('active', 'desc');
    if ($order_by !== null) {
      $records = $records->orderBy($order_by, $order);
    }
    $records = $records->where(function ($query) use($q, $search_columns) {
        if($q != '') {
          foreach ($search_columns as $search_column) {
            $query->orWhere($search_column, 'like', '%' . $q . '%');
          }
        }
      })
      ->take($length)->skip($start)->get();

    if ($sort_by !== null) {
      if ($order == 'desc') {
        $records = $records->sortByDesc($sort_by);
      } else {
        $records = $records->sortBy($sort_by);
      }
    }

    if($length == -1) $length = $count;

    $data = [];

    foreach ($records as $record) {
      $row['id'] = $record->id;
      $row['DT_RowId'] = 'row_' . $record->id;

      foreach($columns as $i => $column) {
        $row[$column['column_name']] = $record->{$column['column_name']};
      }

      $users = collect();
      
      // Get client user(s)
      if ($record->client !== null) {
        $users = $users->merge($record->client->users()->orderBy('name')->get());
      }

      // Get managers
      if ($record->managers !== null) {
        $users = $users->merge($record->managers);
      }

      // Get task(s) assignee
      foreach($record->tasks as $task) {
        $users = $users->merge($task->assignees);
      }

      $users = $users->unique('id');

      $users = $users->sortBy(function ($user, $key) {
        return ! $user->active . ' ' . $user->roles->pluck('id')->min() . ' ' . $user->name;
      }, SORT_FLAG_CASE);

      $user_data = [];
      if (! empty($users)) {
        foreach ($users as $user) {
          $user_data[] = [
            'sl' => Core\Secure::array2string(array('user_id' => $user->id)),
            'name' => $user->name,
            'active' => $user->active,
            'email' => $user->email,
            'phone' => $user->phone,
            'job_title' => $user->job_title,
            'role' => $user->getRoleNames()[0],
            'role_color' => $user->roles[0]->color,
            'avatar' => (string) $user->getAvatar(),
            'recently_online' => $user->getRecentlyOnline()
          ];
        }
      }

      $total_tasks = $record->tasks->count();
      $completed_tasks = $record->tasks()->whereNotNull('completed_date')->count();

      $row['users'] = $user_data;
      $row['client_name'] = $record->client->name ?? null;
      $row['status_name'] = $record->status->name ?? null;
      $row['status_color'] = $record->status->color ?? null;
      $row['total_tasks'] = $total_tasks;
      $row['completed_tasks'] = $completed_tasks;
      $row['task_progress'] = $record->task_progress;
      $row['active'] = $record->active;
      $row['sl'] = Core\Secure::array2string(array('project_id' => $record->id));

      $data[] = $row;
    }

    $response = array(
      'draw' => $draw,
      'recordsTotal' => $count,
      'recordsFiltered' => $count,
      'data' => $data
    );

    return response()->json($response);
  }

  /**
   * View project
   */

  public function getViewProject($sl, FormBuilder $formBuilder) {
    $qs = Core\Secure::string2array($sl);
    $id = $qs['project_id'];

    if (is_numeric($id)) {
      // Set model
      $project = \Platform\Models\Project::findOrFail($id);

      // Get form
      $form = $formBuilder->create('App\Forms\Project', [
        'url' => '#',
        'class' => 'disabled-view-form',
        'model' => $project->toArray(), // Pass model as array so hidden fields are respected
        'language_name' => 'g',
        'data' =>  ['model' => $project] // Pass model as collection for field processing
      ]);

      $form->remove('name');
      $form->remove('desc1');
      $form->remove('company_id');
      $form->remove('managers');
      $form->remove('category');
      $form->remove('project_status_id');
      $form->remove('short_description');
      $form->remove('description');
      $form->remove('notes');
      $form->remove('client_can_comment');
      $form->remove('client_can_view_tasks');
      $form->remove('client_can_edit_tasks');
      $form->remove('client_can_view_description');
      $form->remove('client_can_upload_files');
      $form->remove('client_can_view_proposition');
      $form->remove('client_can_approve_proposition');
      $form->remove('notify_people_involved');
      $form->remove('completed_by_id');
      $form->remove('back');
      $form->remove('submit');

      if (auth()->user()->can('user-view-project-comments', $project)) {
        if ($project->reference === null) $form->remove('reference');
        if (! isset($project->propositions[0]) || (isset($project->propositions[0]) && $project->propositions[0]->proposition_valid_until === null)) $form->remove('proposition_valid_until');
      } else {
        $form->remove('reference');
        $form->remove('currency_code');
        $form->remove('proposition_valid_until');
      }

      if ($project->start_date === null) $form->modify('start_date', 'text', ['value' => '-']);
      if ($project->due_date === null) $form->modify('due_date', 'text', ['value' => '-']);
      if ($project->completed_date === null) $form->modify('completed_date', 'text', ['value' => '-']);

      $form->disableFields();

      // Check if user is assigned
      $query = \Platform\Models\Project::select(['id']);
      if (! auth()->user()->can('all-projects')) {
        // Client company users
        $query = $query->whereHas('client', function($query) {
          $query->whereHas('users', function($query) {
            $query->where('users.id', auth()->user()->id);
          });
        });
        // Project managers
        $query = $query->orWhereHas('managers', function($query) {
          $query->where('users.id', auth()->user()->id);
        });
        // Users with task
        $query = $query->orWhereHas('tasks', function($query) {
          $query->whereHas('assignees', function($query) {
            $query->where('users.id', auth()->user()->id);
          });
        });
      }

      $query = $query->find($id);
      $can_access_project = ($query !== null) ? true : false;

      // File config
      switch (auth()->user()->roles[0]->id) {
        case 1:
        case 2:
          $type = 'project';
          break;
        case 5:
          $type = 'project-client';
          break;
        default:
          $type = 'project-user';
      }

      session(['elfinder.type' => $type]);
      session(['elfinder.company_id' => $project->client->id]);
      session(['elfinder.project_id' => $project->id]);

      return view('app.projects.view-project', compact('form', 'project', 'sl', 'can_access_project'));
    }
  }

  /**
   * Create project
   */

  public function getCreateProject(FormBuilder $formBuilder) {
    // Get form
    $form = $formBuilder->create('App\Forms\Project', [
      'method' => 'POST',
      'enctype' => 'multipart/form-data',
      'url' => url('projects/create'),
      'language_name' => 'g',
      'data' => ['create' => true]
    ]);

    return view('app.projects.create-project', compact('form'));
  }

  /**
   * Create project post
   */

  public function postCreateProject(FormBuilder $formBuilder) {
    // Form
    $form = $this->form('App\Forms\Project');

    // Validate form
    if (! $form->isValid()) {
      return redirect()->back()->withErrors($form->getErrors())->withInput();
    }

    $form_fields = $form->getFieldValues();

    // Set currency for Money functions
    $currency = $form_fields['currency_code'];

    // Create record
    $project = \Platform\Models\Project::create($form_fields);

    // Sync project managers
    $project->managers()->sync($form_fields['managers']);  

    // Notification
    $notify_people_involved = request()->get('notify_people_involved', 0);

    // Process tasks
    $task_subject = request()->get('task_subject', null);
    $task_description = request()->get('task_description', null);
    $task_assigned_to_id = request()->get('task_assigned_to_id', null);
    $task_start_date = request()->get('task_start_date', null);
    $task_due_date = request()->get('task_due_date', null);
    $task_completed_date = request()->get('task_completed_date', null);
    $task_completed_by_id = request()->get('task_completed_by_id', null);
    $task_project_status_id = request()->get('task_project_status_id', null);
    $task_priority = request()->get('task_priority', null);

    if ($task_subject !== null) {
      foreach ($task_subject as $i => $row) {

        $project_task = new \Platform\Models\ProjectTask;
        $project_task->project_id = $project->id;
        $project_task->project_status_id = $task_project_status_id[$i];
        //$project_task->assigned_to_id = $task_assigned_to_id[$i];
        $project_task->subject = $task_subject[$i];
        $project_task->priority = $task_priority[$i];
        $project_task->description = $task_description[$i];
        $project_task->start_date = $task_start_date[$i];
        $project_task->due_date = $task_due_date[$i];
        $project_task->completed_date = $task_completed_date[$i];
        $project_task->completed_by_id = $task_completed_by_id[$i];
        $project_task->save();

        // Sync assignees
        if ($task_assigned_to_id[$i] !== null) {
          $project_task->assignees()->sync(explode(',', $task_assigned_to_id[$i]));
        }

        // Notify assignee(s)
        if ($notify_people_involved == 1) {
          if ($project_task->assignees->count() > 0) {
            foreach ($project_task->assignees as $user) {
              //$user = \App\User::find($task_assigned_to_id[$i]);
              if ($user->active) {
                \Notification::send($user, new \App\Notifications\ProjectAssignedToTask(env('APP_URL') . '/login', auth()->user(), $user, $project_task));
              }
            }
          }
        }
      }
    }

    // Process proposition
    $proposition_type = request()->get('proposition_type', null);
    $proposition_description = request()->get('proposition_description', null);
    $proposition_quantity = request()->get('proposition_quantity', null);
    $proposition_unit = request()->get('proposition_unit', null);
    $proposition_discount_unit = request()->get('proposition_discount_unit', null);
    $proposition_unit_price = request()->get('proposition_unit_price', null);
    $proposition_tax_rate = request()->get('proposition_tax_rate', null);

    if ($proposition_type !== null) {
      // Totals     
      $sub_total = Money::{$currency}(0);
      $tax_total = Money::{$currency}(0);
      $grand_total = Money::{$currency}(0);
      $discount_total = Money::{$currency}(0);

      $taxes = [];

      // Insert proposition
      $proposition_reference = request()->get('reference', null);
      $proposition_valid_until = request()->get('proposition_valid_until', null);

      $proposition = new \Platform\Models\ProjectProposition;
      $proposition->project_id = $project->id;
      $proposition->reference = $proposition_reference;
      $proposition->proposition_valid_until = $proposition_valid_until;
      $proposition->save();

      foreach ($proposition_type as $i => $row) {
        if (
          $proposition_type[$i] == 'item' && 
          //$proposition_description[$i] != '' && 
          $proposition_quantity[$i] != '' && 
          $proposition_unit_price[$i] != '' && 
          $proposition_tax_rate[$i] != ''
        ) {
          $proposition_item = new \Platform\Models\ProjectPropositionItem;
          $proposition_item->project_proposition_id = $proposition->id;
          $proposition_item->type = $proposition_type[$i];
          $proposition_item->description = $proposition_description[$i];
          $proposition_item->quantity = $proposition_quantity[$i];
          $proposition_item->unit = $proposition_unit[$i];
          $proposition_item->unit_price = $proposition_unit_price[$i] * 100;
          $proposition_item->tax_rate = $proposition_tax_rate[$i];

          $proposition_item->save();

          $row_total_excl_taxes = Money::{$currency}($proposition_item->unit_price)->multiply($proposition_item->quantity);
          $tax = $row_total_excl_taxes->multiply($proposition_item->tax_rate / 10000);

          $sub_total = $sub_total->add($row_total_excl_taxes);
          $tax_total = $tax_total->add($tax);
          $grand_total = $grand_total->add($row_total_excl_taxes->add($tax));
        }
      }

      foreach ($proposition_type as $i => $row) {
        if (
          $proposition_type[$i] == 'discount' && 
          //$proposition_description[$i] != '' && 
          $proposition_quantity[$i] != '' && 
          $proposition_discount_unit[$i] != '' && 
          $proposition_tax_rate[$i] != ''
        ) {
          $proposition_item = new \Platform\Models\ProjectPropositionItem;
          $proposition_item->project_proposition_id = $proposition->id;
          $proposition_item->type = $proposition_type[$i];
          $proposition_item->description = $proposition_description[$i];
          $proposition_item->quantity = $proposition_quantity[$i];
          $proposition_item->discount_type = $proposition_discount_unit[$i];
          $proposition_item->tax_rate = $proposition_tax_rate[$i];

          $proposition_item->save();

          if ($proposition_item->discount_type == '%') {
            $row_total_excl_taxes = $sub_total->multiply($proposition_item->quantity / 100);
          } else {
            $row_total_excl_taxes = Money::{$currency}($proposition_item->quantity);
          }

          $tax = $row_total_excl_taxes->multiply($proposition_item->tax_rate / 10000);

          $discount_total = $discount_total->add($row_total_excl_taxes);
          $tax_total = $tax_total->subtract($tax);
          $grand_total = $grand_total->subtract($row_total_excl_taxes->add($tax));
        }
      }

      $proposition->total = $grand_total->getAmount();
      $proposition->total_discount = $discount_total->getAmount();
      $proposition->total_tax = $tax_total->getAmount();
      $proposition->save();
    }

    // Notify client member(s)
    if ($notify_people_involved == 1) {
      $users = $project->client->users()->get();

      if ($users !== null) {
        foreach ($users as $user) {
          if ($user->active) {
            \Notification::send($user, new \App\Notifications\ProjectCreated(env('APP_URL') . '/login', auth()->user(), $user, $project));
          }
        }
      }

      if ($project->managers->count() > 0) {
        foreach ($project->managers as $user) {
          if ($user->active) {
            \Notification::send($user, new \App\Notifications\ProjectCreated(env('APP_URL') . '/login', auth()->user(), $user, $project));
          }
        }
      }
    }

    // Create chat with participants who have at least 'view-comments' permission
    //$participants = [$userId, $userId2,...];
    //$conversation = Chat::createConversation($participants);

    // Log
    Core\Log::add(
      'create_project', 
      trans('g.log_project_create_project', ['name' => auth()->user()->name, 'project' => $project->name]),
      '\Platform\Models\Project',
      $project->id,
      auth()->user()
    );

    //return redirect('projects/edit/' . Core\Secure::array2string(array('project_id' => $project->id)))->with('success', trans('g.form_success'));
    return redirect('projects')->with('success', trans('g.form_success'));
  }

  /**
   * Edit project
   */

  public function getEditProject($sl, FormBuilder $formBuilder) {
    $qs = Core\Secure::string2array($sl);
    $id = $qs['project_id'];

    if (is_numeric($id)) {
      // Set project
      $project = \Platform\Models\Project::findOrFail($id);

      // Get form
      $form = $formBuilder->create('App\Forms\Project', [
        'method' => 'POST',
        'enctype' => 'multipart/form-data',
        'url' => url('projects/edit/' . $sl),
        'model' => $project->toArray(), // Pass model as array so hidden fields are respected
        'language_name' => 'g',
        'data' =>  ['model' => $project] // Pass model as collection for field processing
      ]);

      // History
      $history = \Platform\Models\Log::where('model', '\Platform\Models\Project')->where('model_id', $id)->where('user_id', '<>', $id)->orderBy('created_at', 'desc')->get();

      // File config
      switch (auth()->user()->roles[0]->id) {
        case 1:
        case 2:
          $type = 'project';
          break;
        case 5:
          $type = 'project-client';
          break;
        default:
          $type = 'project-user';
      }

      session(['elfinder.type' => $type]);
      session(['elfinder.company_id' => $project->client->id]);
      session(['elfinder.project_id' => $project->id]);

      return view('app.projects.edit-project', compact('project', 'form', 'history'));
    }
  }

  /**
   * Edit project post
   */

  public function postEditProject($sl, FormBuilder $formBuilder) {
    $qs = Core\Secure::string2array($sl);
    $id = $qs['project_id'];

    if (is_numeric($id)) {
      // Form
      $form = $this->form('App\Forms\Project');

      // Override validation
      $form->validate(['email' => 'nullable|email|unique:projects,email,' . $qs['project_id']]);

      $project = \Platform\Models\Project::findOrFail($id);

      // Validate form
      if (! $form->isValid()) {
        return redirect()->back()->withErrors($form->getErrors())->withInput();
      }

      $form_fields = $form->getFieldValues();

      // Set currency for Money functions
      $currency = $form_fields['currency_code'];

      // Add updated_by manually because it isn't triggered if only relationships are updated
      $project->updated_by = auth()->user()->id;

      $project->fill($form_fields);
      $project->save();

      // Sync project managers
      $project->managers()->sync($form_fields['managers']); 

      // Notification
      $notify_people_involved = request()->get('notify_people_involved', 0);

      // Process tasks, first delete existing tasks
      $project->tasks()->delete();

      $task_new = request()->get('task_new', null);
      $task_changed = request()->get('task_changed', null);
      $task_subject = request()->get('task_subject', null);
      $task_description = request()->get('task_description', null);
      $task_assigned_to_id = request()->get('task_assigned_to_id', null);
      $task_start_date = request()->get('task_start_date', null);
      $task_due_date = request()->get('task_due_date', null);
      $task_completed_date = request()->get('task_completed_date', null);
      $task_completed_by_id = request()->get('task_completed_by_id', null);
      $task_project_status_id = request()->get('task_project_status_id', null);
      $task_priority = request()->get('task_priority', null);

      if ($task_subject !== null) {
        foreach ($task_subject as $i => $row) {

          $project_task = new \Platform\Models\ProjectTask;
          $project_task->project_id = $project->id;
          $project_task->project_status_id = $task_project_status_id[$i];
          //$project_task->assigned_to_id = $task_assigned_to_id[$i];
          $project_task->subject = $task_subject[$i];
          $project_task->priority = $task_priority[$i];
          $project_task->description = $task_description[$i];
          $project_task->start_date = $task_start_date[$i];
          $project_task->due_date = $task_due_date[$i];
          $project_task->completed_date = $task_completed_date[$i];
          $project_task->completed_by_id = $task_completed_by_id[$i];
          $project_task->save();

          // Sync assignees
          if ($task_assigned_to_id[$i] !== null) {
            $project_task->assignees()->sync(explode(',', $task_assigned_to_id[$i]));
          }

          // Notify assignee(s)
          if ($notify_people_involved == 1 && ($task_changed[$i] == 1 || $task_new[$i] == 1)) {
            if ($project_task->assignees->count() > 0) {
              foreach ($project_task->assignees as $user) {
                //$user = \App\User::find($task_assigned_to_id[$i]);
                if ($user->active) {
                  if ($task_new[$i] == 1) {
                    \Notification::send($user, new \App\Notifications\ProjectAssignedToTask(env('APP_URL') . '/login', auth()->user(), $user, $project_task));
                  } else {
                    \Notification::send($user, new \App\Notifications\ProjectTaskUpdated(env('APP_URL') . '/login', auth()->user(), $user, $project_task));
                  }
                }
              }
            }
          }
        }
      }

      // Process proposition, first delete existing proposition and items
      $project->propositions()->delete();

      $proposition_type = request()->get('proposition_type', null);
      $proposition_description = request()->get('proposition_description', null);
      $proposition_quantity = request()->get('proposition_quantity', null);
      $proposition_unit = request()->get('proposition_unit', null);
      $proposition_discount_unit = request()->get('proposition_discount_unit', null);
      $proposition_unit_price = request()->get('proposition_unit_price', null);
      $proposition_tax_rate = request()->get('proposition_tax_rate', null);

      if ($proposition_type !== null) {
        // Totals     
        $sub_total = Money::{$currency}(0);
        $tax_total = Money::{$currency}(0);
        $grand_total = Money::{$currency}(0);
        $discount_total = Money::{$currency}(0);

        $taxes = [];

        // Insert proposition
        $proposition_reference = request()->get('reference', null);
        $proposition_valid_until = request()->get('proposition_valid_until', null);

        $proposition = new \Platform\Models\ProjectProposition;
        $proposition->project_id = $project->id;
        $proposition->reference = $proposition_reference;
        $proposition->proposition_valid_until = $proposition_valid_until;
        $proposition->save();

        foreach ($proposition_type as $i => $row) {
          if (
            $proposition_type[$i] == 'item' && 
            //$proposition_description[$i] != '' && 
            $proposition_quantity[$i] != '' && 
            $proposition_unit_price[$i] != '' && 
            $proposition_tax_rate[$i] != ''
          ) {
            $proposition_item = new \Platform\Models\ProjectPropositionItem;
            $proposition_item->project_proposition_id = $proposition->id;
            $proposition_item->type = $proposition_type[$i];
            $proposition_item->description = $proposition_description[$i];
            $proposition_item->quantity = $proposition_quantity[$i];
            $proposition_item->unit = $proposition_unit[$i];
            $proposition_item->unit_price = $proposition_unit_price[$i] * 100;
            $proposition_item->tax_rate = $proposition_tax_rate[$i];

            $proposition_item->save();

            $row_total_excl_taxes = Money::{$currency}($proposition_item->unit_price)->multiply($proposition_item->quantity);
            $tax = $row_total_excl_taxes->multiply($proposition_item->tax_rate / 10000);

            $sub_total = $sub_total->add($row_total_excl_taxes);
            $tax_total = $tax_total->add($tax);
            $grand_total = $grand_total->add($row_total_excl_taxes->add($tax));
          }
        }

        foreach ($proposition_type as $i => $row) {
          if (
            $proposition_type[$i] == 'discount' && 
            //$proposition_description[$i] != '' && 
            $proposition_quantity[$i] != '' && 
            $proposition_discount_unit[$i] != '' && 
            $proposition_tax_rate[$i] != ''
          ) {
            $proposition_item = new \Platform\Models\ProjectPropositionItem;
            $proposition_item->project_proposition_id = $proposition->id;
            $proposition_item->type = $proposition_type[$i];
            $proposition_item->description = $proposition_description[$i];
            $proposition_item->quantity = $proposition_quantity[$i];
            $proposition_item->discount_type = $proposition_discount_unit[$i];
            $proposition_item->tax_rate = $proposition_tax_rate[$i];

            $proposition_item->save();

            if ($proposition_item->discount_type == '%') {
              $row_total_excl_taxes = $sub_total->multiply($proposition_item->quantity / 100);
            } else {
              $row_total_excl_taxes = Money::{$currency}($proposition_item->quantity);
            }

            $tax = $row_total_excl_taxes->multiply($proposition_item->tax_rate / 10000);

            $discount_total = $discount_total->add($row_total_excl_taxes);
            $tax_total = $tax_total->subtract($tax);
            $grand_total = $grand_total->subtract($row_total_excl_taxes->add($tax));
          }
        }

        $proposition->total = $grand_total->getAmount();
        $proposition->total_discount = $discount_total->getAmount();
        $proposition->total_tax = $tax_total->getAmount();
        $proposition->save();
      }

      // Notify client member(s)
      if ($notify_people_involved == 1) {
        $users = $project->client->users()->get();

        if ($users !== null) {
          foreach ($users as $user) {
            if ($user->active) {
              \Notification::send($user, new \App\Notifications\ProjectUpdated(env('APP_URL') . '/login', auth()->user(), $user, $project));
            }
          }
        }
        if ($project->managers->count() > 0) {
          foreach ($project->managers as $user) {
            if ($user->active) {
              \Notification::send($user, new \App\Notifications\ProjectUpdated(env('APP_URL') . '/login', auth()->user(), $user, $project));
            }
          }
        }
      }

      // Log
      Core\Log::add(
        'update_project', 
        trans('g.log_project_update_project', ['name' => auth()->user()->name, 'project' => $project->name]),
        '\Platform\Models\Project',
        $project->id,
        auth()->user()
      );
    }

    return redirect('projects')->with('success', trans('g.form_success'));
  }

  /**
   * Edit project task post
   */

  public function postEditTask() {
    $project_id = request()->get('form_project_id', null);
    $task_id = request()->get('form_task_id', null);

    if (is_numeric($project_id) && is_numeric($task_id)) {
      $project = \Platform\Models\Project::findOrFail($project_id);
      $project_task = \Platform\Models\ProjectTask::findOrFail($task_id);

      $task_completed_date = request()->get('form_task_completed_date', null);

      if (! auth()->user()->can('edit-project-task')) {
        $project_task->completed_date = $task_completed_date;
        $project_task->completed_by_id = auth()->user()->id;
        $project_task->project_status_id = 72;
      } else {
        $project_task->completed_date = $task_completed_date;
        $project_task->completed_by_id = request()->get('form_task_completed_by_id', null);
        $project_task->subject = request()->get('form_task_subject', null);
        $project_task->description = request()->get('form_task_description', null);
        $project_task->start_date = request()->get('form_task_start_date', null);
        $project_task->due_date = request()->get('form_task_due_date', null);
        $project_task->priority = request()->get('form_task_priority', null);

        if ($task_completed_date === null || request()->get('form_task_status_changed', 0) == 1) {
          $project_task->project_status_id = request()->get('form_task_project_status_id', 72);
        } else {
          $project_task->project_status_id = 72;
        }

        // Sync assignees
        $project_task->assignees()->sync(request()->get('form_task_assigned_to_id', []));
      }

      $project_task->save();
      $project->touch(); // For last updated date

      // Notify assignee(s)
      if ($project->notify_people_involved == 1) {
        if ($project_task->assignees->count() > 0) {
          foreach ($project_task->assignees as $user) {
            if ($user->active) {
              \Notification::send($user, new \App\Notifications\ProjectTaskUpdated(env('APP_URL') . '/login', auth()->user(), $user, $project_task));
            }
          }
        }
      }

      // Notify client member(s)
      if ($project->notify_people_involved == 1) {
        $users = $project->client->users()->get();

        if ($users !== null) {
          foreach ($users as $user) {
            if ($user->active) {
              \Notification::send($user, new \App\Notifications\ProjectTaskUpdated(env('APP_URL') . '/login', auth()->user(), $user, $project_task));
            }
          }
        }
        if ($project->managers->count() > 0) {
          foreach ($project->managers as $user) {
            if ($user->active) {
              \Notification::send($user, new \App\Notifications\ProjectTaskUpdated(env('APP_URL') . '/login', auth()->user(), $user, $project_task));
            }
          }
        }
      }

      // Log
      Core\Log::add(
        'update_project', 
        trans('g.log_project_update_task', ['name' => auth()->user()->name, 'project' => $project->name, 'task' => $project_task->subject]),
        '\Platform\Models\Project',
        $project->id,
        auth()->user()
      );
    }

    $sl = Core\Secure::array2string(['project_id' => $project->id]);

    return redirect('projects/view/' . $sl . '#tasks')->with('success', trans('g.form_success'));
  }

  /**
   * Delete (selected) projects
   */

  public function postDeleteProjects() {
    $ids = request()->get('ids');

    if (is_array($ids)) {
      foreach ($ids as $id) {
        // Filter assigned records
        $query = \Platform\Models\Project::select(['id', 'name', 'company_id', 'files_dir'])->find($id);

        if ($query !== null) {
          // Log
          Core\Log::add(
            'delete_project', 
            trans('g.log_project_delete_project', ['name' => auth()->user()->name, 'project' => $query->name]),
            '\Platform\Models\Project',
            $query->id,
            auth()->user()
          );

          // Delete files
          if ($query->client->files_dir !== null && $query->files_dir !== null) {
            $dir = public_path() . '/files/projects/' . $query->client->files_dir . '-' . Core\Secure::staticHash($query->client->id * 10000) . '/' . $query->files_dir . '-' . Core\Secure::staticHash($query->id * 10000);

            if (\File::exists($dir)) {
              \File::deleteDirectory($dir);
            }
          }

          // Delete
          $query->delete();
        }
      }
    }

    return response()->json(true);
  }

  /**
   * Export records
   */

  public function getExportRecords($ext) {
    // Filename
    $filename = str_slug(str_replace([':','/',' '], '-', config('system.name') . '-' . trans('g.projects') . '-' . \Carbon\Carbon::now(auth()->user()->getTimezone())->format(auth()->user()->getUserDateFormat() . '-' . auth()->user()->getUserTimeFormat())), '-');

    switch ($ext) {
      case 'xlsx'; return (new ProjectsExport)->download($filename . '.' . $ext, \Maatwebsite\Excel\Excel::XLSX); break;
      case 'xls'; return (new ProjectsExport)->download($filename . '.' . $ext, \Maatwebsite\Excel\Excel::XLS); break;
      case 'csv'; return (new ProjectsExport)->download($filename . '.' . $ext, \Maatwebsite\Excel\Excel::CSV); break;
      case 'html'; return (new ProjectsExport)->download($filename . '.' . $ext, \Maatwebsite\Excel\Excel::HTML); break;
    }
  }

  /**
   * Approve proposition
   */

  public function postApproveProposition() {
    $id = request()->get('id', null);

    $project = \Platform\Models\Project::findOrFail($id);

    if(! auth()->user()->can('user-approve-project-proposition', $project)) abort(404);

    // Set proposition as approved, first check "valid until"
    if ($project->propositions[0]->proposition_valid_until !== null) {
      if ($project->propositions[0]->proposition_valid_until->isPast()) {
        return response()->json(['msg' => trans('g.valid_until_expired')]);
      }
    }

    $project->propositions[0]->approved = \Carbon\Carbon::now('UTC');
    $project->propositions[0]->approved_by = auth()->user()->id;
    $project->propositions[0]->save();

    // Notify all project members about approval, including auth() user
    $users = collect();

    // Get client user(s)
    if ($project->client !== null) {
      $users = $users->merge($project->client->users);
    }

    // Get managers
    if ($project->managers !== null) {
      $users = $users->merge($project->managers);
    }

    $users = $users->unique('id');

    foreach ($users as $user) {
      if ($user->active) {
        // Send notification
        \Notification::send($user, new \App\Notifications\ProjectPropositionApproved(env('APP_URL') . '/login', auth()->user(), $user, $project));
      }
    }

    return response()->json(true);
  }

  /**
   * Reset proposition approval
   */

  public function postResetPropositionApproval() {
    $id = request()->get('id', null);

    $project = \Platform\Models\Project::findOrFail($id);

    if(! auth()->user()->can('user-reset-approval-project-proposition', $project)) abort(404);

    $project->propositions[0]->approved = null;
    $project->propositions[0]->approved_by = null;
    $project->propositions[0]->save();

    // Notify all project members about approval reset, including auth() user
    $users = collect();

    // Get client user(s)
    if ($project->client !== null) {
      $users = $users->merge($project->client->users);
    }

    // Get managers
    if ($project->managers !== null) {
      $users = $users->merge($project->managers);
    }

    $users = $users->unique('id');

    foreach ($users as $user) {
      if ($user->active) {
        // Send notification
        \Notification::send($user, new \App\Notifications\ProjectPropositionApprovalReset(env('APP_URL') . '/login', auth()->user(), $user, $project));
      }
    }

    return response()->json(true);
  }
}