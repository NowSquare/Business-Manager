<?php namespace Platform\Controllers\App;

use Platform\Controllers\Core;

use Kris\LaravelFormBuilder\FormBuilder;
use Kris\LaravelFormBuilder\FormBuilderTrait;

use App\Forms\Profile;
use App\Forms\User;

use Illuminate\Support\Facades\Mail;
use App\Mail\NotifyUserCreate;

use App\Exports\UsersExport;

class UserController extends \App\Http\Controllers\Controller {

  use FormBuilderTrait;

  /*
   |--------------------------------------------------------------------------
   | User Controller
   |--------------------------------------------------------------------------
   */

  /**
   * Profile
   */

  public function getProfile(FormBuilder $formBuilder) {
    // Set model
    $model = auth()->user();

    // Get form
    $form = $formBuilder->create('App\Forms\Profile', [
      'method' => 'POST',
      'enctype' => 'multipart/form-data',
      'url' => url('profile'),
      'model' => $model->toArray(), // Pass model as array so hidden fields are respected
      'language_name' => 'g',
      'data' =>  ['model' => $model] // Pass model as collection for field processing
    ]);

    return view('app.users.profile', compact('form'));
  }

  /**
   * Update profile
   */

  public function postProfile(FormBuilder $formBuilder) {
    if (config('app.demo') && auth()->user()->id == 1) return redirect('profile')->with('warning', trans('g.demo_mode_update_root_user'));

    // Form and model
    $form = $this->form(Profile::class);
    $model = auth()->user();

    // Validate form
    if (! $form->isValid()) {
      return redirect()->back()->withErrors($form->getErrors())->withInput();
    }

    // Get form post
    $form_fields = $form->getFieldValues();

    // Process password field
    if ($form_fields['password'] == null) {
      array_forget($form_fields, 'password');
    } else {
      $form_fields['password'] = bcrypt($form_fields['password']);
    }

    // Process number notation
    $seperators = $form_fields['seperators'];

    switch ($seperators) {
      case '.,': $form_fields['decimal_seperator'] = '.'; $form_fields['thousands_seperator'] = ','; break;
      case ',.': $form_fields['decimal_seperator'] = ','; $form_fields['thousands_seperator'] = '.'; break;
      case ',': $form_fields['decimal_seperator'] = ','; $form_fields['thousands_seperator'] = ' '; break;
      case '.': $form_fields['decimal_seperator'] = '.'; $form_fields['thousands_seperator'] = ' '; break;
    }
    array_forget($form_fields, 'seperators');

    // Process image attachment field
    if (request()->get('avatar_changed') == 1) {
      if ($form_fields['avatar'] == null) {
        $form_fields['avatar'] = \Czim\Paperclip\Attachment\Attachment::NULL_ATTACHMENT;
      }
    } else {
      array_forget($form_fields, 'avatar');
    }

    // Update model
    $model->fill($form_fields);
    $model->save();

    // Log
    Core\Log::add(
      'update_profile', 
      trans('g.log_user_update_profile', ['name' => $model->name . ' (' . $model->email . ')']),
      '\App\User',
      $model->id,
      auth()->user()
    );

    return redirect('profile')->with('success', trans('g.form_success'));
  }

  /**
   * Users list
   */

  public function getUserList(FormBuilder $formBuilder) {
    // Generate form
    $form = $formBuilder->create('App\Forms\User', [
      'language_name' => 'g',
      'data' => ['ajax' => url('users/json')]
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

    return view('app.users.list-users', compact('columns', 'form'));
  }

  /**
   * Users list json
   */

  public function getUserListJson(FormBuilder $formBuilder) {
    // DataTables parameters
    $order_by = request()->input('order.0.column', 1);
    $order_by--;
    $order = request()->input('order.0.dir', 'asc');
    $search = request()->input('search.regex', '');
    $q = request()->input('search.value', '');
    $start = request()->input('start', 0);
    $draw = request()->input('draw', 1);
    $length = request()->input('length', 10);
    if ($length == -1) $length = 1000;
    $data = array();

    // Get form
    $form = $formBuilder->create('App\Forms\User', [
      'language_name' => 'g'
    ]);

    $table = 'users';
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

    $order_by = (isset($columns[$order_by]['options']['real_name'])) ? $columns[$order_by]['options']['real_name'] : null;

    // Query model
    $query = \App\User::select($select_columns);

    // Filter assigned records
    if (! auth()->user()->can('all-users')) {
      $query = $query->has('assignedUsers');
    }

    // Filter role(s)
    $filter_role = request()->input('columns.4.search.value', 0);

    if ($filter_role != 0) {
      $query = $query->role($filter_role);
    }

    $count = $query->where(function ($query) use($q, $search_columns) {
        if($q != '') {
          foreach ($search_columns as $search_column) {
            $query->orWhere($search_column, 'like', '%' . $q . '%');
          }
        }
      })->count();

    $records = $query->orderBy('active', 'desc')->orderBy($order_by, $order)
      ->where(function ($query) use($q, $search_columns) {
        if($q != '') {
          foreach ($search_columns as $search_column) {
            $query->orWhere($search_column, 'like', '%' . $q . '%');
          }
        }
      })
      ->take($length)->skip($start)->get();

    if($length == -1) $length = $count;

    $data = [];

    foreach ($records as $record) {
      $row['id'] = $record->id;
      $row['DT_RowId'] = 'row_' . $record->id;

      foreach($columns as $i => $column) {
        if ($column['options']['real_name'] == 'avatar') {
          $row['avatar'] = (string) $record->getAvatar();
        } else {
          $row[$column['column_name']] = $record->{$column['column_name']};
        }
      }

      //$row['role'] = implode(', ', $record->getRoleNames()->toArray());
      $row['role'] = $record->roles->pluck('id')->first();
      $row['recently_online'] = $record->getRecentlyOnline();
      $row['active'] = $record->active;
      $row['sl'] = Core\Secure::array2string(array('user_id' => $record->id));

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
   * View user
   */

  public function getViewUser($sl, FormBuilder $formBuilder) {
    $qs = Core\Secure::string2array($sl);
    $id = $qs['user_id'];

    if (is_numeric($id)) {
      // Set model
      $user = \App\User::findOrFail($id);

      // Get form
      $form = $formBuilder->create('App\Forms\User', [
        'url' => '#',
        'class' => 'disabled-view-form',
        'model' => $user->toArray(), // Pass model as array so hidden fields are respected
        'language_name' => 'g',
        'data' =>  ['model' => $user] // Pass model as collection for field processing
      ]);

      $form->remove('active');
      $form->remove('password');
      $form->remove('notes');
      $form->remove('role');
      $form->remove('companies');
      $form->remove('projects');
      $form->remove('header1');
      $form->remove('header2');
      $form->remove('header3');
      $form->remove('header4');
      $form->remove('header7');
      $form->remove('header8');
      $form->remove('date_format');
      $form->remove('timezone');
      $form->remove('time_format');
      $form->remove('header9');
      $form->remove('currency_code');
      $form->remove('header10');
      $form->remove('seperators');
      $form->remove('language');
      $form->remove('back');
      $form->remove('submit');

      $form->disableFields();

      // Check if user is assigned
      $query = \App\User::select(['id']);
      if (! auth()->user()->can('all-users')) {
        $query = $query->has('assignedUsers');
      }
      $query = $query->find($id);
      $can_access_user = ($query !== null) ? true : false;

      return view('app.users.view-user', compact('form', 'user', 'sl', 'can_access_user'));
    }
  }

  /**
   * Create user
   */

  public function getCreateUser(FormBuilder $formBuilder) {
    // Get form
    $form = $formBuilder->create('App\Forms\User', [
      'method' => 'POST',
      'enctype' => 'multipart/form-data',
      'url' => url('users/create'),
      'language_name' => 'g',
      'data' => ['create' => true]
    ]);

    return view('app.users.create-user', compact('form'));
  }

  /**
   * Create user post
   */

  public function postCreateUser(FormBuilder $formBuilder) {
    // Form
    $form = $this->form(User::class);

    // Override validation
    $form->validate(['password' => 'required|min:6|max:32']);

    // Validate form
    if (! $form->isValid()) {
      return redirect()->back()->withErrors($form->getErrors())->withInput();
    }

    $form_fields = $form->getFieldValues();

    // Process password field
    $password = $form_fields['password'];
    $form_fields['password'] = bcrypt($form_fields['password']);

    // Process number notation
    $seperators = $form_fields['seperators'];

    switch ($seperators) {
      case '.,': $form_fields['decimal_seperator'] = '.'; $form_fields['thousands_seperator'] = ','; break;
      case ',.': $form_fields['decimal_seperator'] = ','; $form_fields['thousands_seperator'] = '.'; break;
      case ',': $form_fields['decimal_seperator'] = ','; $form_fields['thousands_seperator'] = ' '; break;
      case '.': $form_fields['decimal_seperator'] = '.'; $form_fields['thousands_seperator'] = ' '; break;
    }
    array_forget($form_fields, 'seperators');

    // Process image attachment field
    if (request()->get('avatar_changed') == 1) {
      if ($form_fields['avatar'] == null) {
        $form_fields['avatar'] = \Czim\Paperclip\Attachment\Attachment::NULL_ATTACHMENT;
      }
    } else {
      array_forget($form_fields, 'avatar');
    }

    // Create record
    $model = \App\User::create($form_fields);

    // Assign role
    $role = \App\Role::find($form_fields['role']);
    $model->assignRole($role);

    // Generate verification code
    $verification_code = str_random(32);
    $model->verification_code = $verification_code;
    $model->save();

    // Log
    Core\Log::add(
      'create_user', 
      trans('g.log_user_create_user', ['name' => auth()->user()->name, 'user' => $model->name . ' (' . $model->email . ')']),
      '\App\User',
      $model->id,
      auth()->user()
    );

    // Notify
    if (request()->get('notify', 0) == 1) {

      // Send verification email
      $actionURL = url('email/verify/' . $verification_code);
      Mail::to($model->email, $model->name)->send(new NotifyUserCreate($actionURL, auth()->user()->name, $model->name, $model->email, $password));
    }

    return redirect('users')->with('success', trans('g.form_success'));
  }

  /**
   * Edit user
   */

  public function getEditUser($sl, FormBuilder $formBuilder) {
    $qs = Core\Secure::string2array($sl);
    $id = $qs['user_id'];

    if (is_numeric($id)) {
      // Set model
      $user = \App\User::findOrFail($id);

      // Get form
      $form = $formBuilder->create('App\Forms\User', [
        'method' => 'POST',
        'enctype' => 'multipart/form-data',
        'url' => url('users/edit/' . $sl),
        'model' => $user->toArray(), // Pass model as array so hidden fields are respected
        'language_name' => 'g',
        'data' =>  ['model' => $user] // Pass model as collection for field processing
      ]);

      // History
      $limit_log = 20;
      $user_actions = \Platform\Models\Log::where('user_id', $id)->orderBy('created_at', 'desc')->limit($limit_log)->get();
      $user_log = \Platform\Models\Log::where('model', '\App\User')->where('model_id', $id)->where('user_id', '<>', $id)->orderBy('created_at', 'desc')->limit($limit_log)->get();

      return view('app.users.edit-user', compact('form', 'user', 'sl', 'user_actions', 'user_log', 'limit_log'));
    }
  }

  /**
   * Edit user post
   */

  public function postEditUser($sl, FormBuilder $formBuilder) {
    $qs = Core\Secure::string2array($sl);
    $id = $qs['user_id'];

    if (config('app.demo') && $id == 1) return redirect('users')->with('warning', trans('g.demo_mode_update_root_user'));

    if (is_numeric($id)) {
      // Form
      $form = $this->form(User::class);

      // Override validation
      $form->validate(['email' => 'required|email|unique:users,email,' . $qs['user_id']]);

      $model = \App\User::findOrFail($id);

      // Validate form
      if (! $form->isValid()) {
        return redirect()->back()->withErrors($form->getErrors())->withInput();
      }

      $form_fields = $form->getFieldValues();

      // Process password field
      if ($form_fields['password'] == null) {
        array_forget($form_fields, 'password');
      } else {
        $form_fields['password'] = bcrypt($form_fields['password']);
      }

      // Process number notation
      $seperators = $form_fields['seperators'];

      switch ($seperators) {
        case '.,': $form_fields['decimal_seperator'] = '.'; $form_fields['thousands_seperator'] = ','; break;
        case ',.': $form_fields['decimal_seperator'] = ','; $form_fields['thousands_seperator'] = '.'; break;
        case ',': $form_fields['decimal_seperator'] = ','; $form_fields['thousands_seperator'] = ' '; break;
        case '.': $form_fields['decimal_seperator'] = '.'; $form_fields['thousands_seperator'] = ' '; break;
      }
      array_forget($form_fields, 'seperators');

      // Process image attachment field
      if (request()->get('avatar_changed') == 1) {
        if ($form_fields['avatar'] == null) {
          $form_fields['avatar'] = \Czim\Paperclip\Attachment\Attachment::NULL_ATTACHMENT;
        }
      } else {
        array_forget($form_fields, 'avatar');
      }

      // Process companies many-to-many
      //$model->companies()->sync($form_fields['companies']);

      // Process projects many-to-many
      //$model->projects()->sync($form_fields['projects']);

      // Assign role
      $role = \App\Role::find($form_fields['role']);
      $model->syncRoles($role);

      $model->fill($form_fields);
      $model->save();

      // Log
      Core\Log::add(
        'update_user', 
        trans('g.log_user_update_user', ['name' => auth()->user()->name, 'user' => $model->name . ' (' . $model->email . ')']),
        '\App\User',
        $model->id,
        auth()->user()
      );
    }

    return redirect('users')->with('success', trans('g.form_success'));
  }

  /**
   * Delete (selected) user(s)
   */

  public function postDeleteUsers() {
    $ids = request()->get('ids');

    if (is_array($ids)) {
      foreach ($ids as $id) {
        // You can't delete the root user or yourself
        if ($id != 1 && auth()->user()->id != $id) {
          // Filter assigned records
          $query = \App\User::select(['id', 'name', 'email']);
          if (! auth()->user()->can('all-users')) {
            $query = $query->has('assignedUsers');
          }
          $query = $query->find($id);
          if ($query !== null) {
            // Log
            Core\Log::add(
              'delete_user', 
              trans('g.log_user_delete_user', ['name' => auth()->user()->name, 'user' => $query->name . ' (' . $query->email . ')']),
              '\App\User',
              $query->id,
              auth()->user()
            );

            // Delete
            $query->delete();
          }
        }
      }
    }

    return response()->json(true);
  }

  /**
   * Login as user
   */

  public function getLoginAsUser($sl) {
    $qs = Core\Secure::string2array($sl);
    $id = $qs['user_id'];

    if (is_numeric($id)) {

      // Filter assigned records
      $query = \App\User::select(['id', 'name', 'email']);
      if (! auth()->user()->can('all-users')) {
        $query = $query->has('assignedUsers');
      }
      $query = $query->find($id);
      if ($query !== null) {
        // Log
        Core\Log::add(
          'login_as_user', 
          trans('g.log_user_login_as', ['name' => auth()->user()->name, 'user' => $query->name . ' (' . $query->email . ')']),
          '\App\User',
          $query->id,
          auth()->user()
        );

        // Set session to redirect to in case of logout
        $logout = Core\Secure::array2string(['user_id' => auth()->user()->id]);
        \Session::put('logout', $logout);

        auth()->loginUsingId($id);

        return redirect('dashboard');
      } else {
        return redirect('users');
      }
    }
  }

  /**
   * Export records
   */

  public function getExportRecords($ext) {
    // Filename
    $filename = str_slug(str_replace([':','/',' '], '-', config('system.name') . '-' . trans('g.people') . '-' . \Carbon\Carbon::now(auth()->user()->getTimezone())->format(auth()->user()->getUserDateFormat() . '-' . auth()->user()->getUserTimeFormat())), '-');

    switch ($ext) {
      case 'xlsx'; return (new UsersExport)->download($filename . '.' . $ext, \Maatwebsite\Excel\Excel::XLSX); break;
      case 'xls'; return (new UsersExport)->download($filename . '.' . $ext, \Maatwebsite\Excel\Excel::XLS); break;
      case 'csv'; return (new UsersExport)->download($filename . '.' . $ext, \Maatwebsite\Excel\Excel::CSV); break;
      case 'html'; return (new UsersExport)->download($filename . '.' . $ext, \Maatwebsite\Excel\Excel::HTML); break;
    }
  }

  /**
   * Import columns
   */

  public static function getImportColumns() {
    $columns = [];
    $columns[] = ['name' => 'name', 'label' => trans('g.full_name')];
    $columns[] = ['name' => 'email', 'label' => trans('g.email_address')];
    $columns[] = ['name' => 'password', 'label' => trans('g.password')];
    $columns[] = ['name' => 'job_title', 'label' => trans('g.job_title')];
    $columns[] = ['name' => 'phone', 'label' => trans('g.phone')];
    $columns[] = ['name' => 'role', 'label' => trans('g.role')];
    $columns[] = ['name' => 'salutation', 'label' => trans('g.salutation')];
    $columns[] = ['name' => 'first_name', 'label' => trans('g.first_name')];
    $columns[] = ['name' => 'last_name', 'label' => trans('g.last_name')];
    $columns[] = ['name' => 'street1', 'label' => trans('g.street')];
    $columns[] = ['name' => 'postal_code', 'label' => trans('g.postal_code')];
    $columns[] = ['name' => 'city', 'label' => trans('g.city')];
    $columns[] = ['name' => 'state', 'label' => trans('g.state')];
    $columns[] = ['name' => 'country_code', 'label' => trans('g.country')];

    return $columns;
  }

  /**
   * Import users
   */

  public function getImportUsers() {
    // Destroy elFinder sessions, so directory defaults to personal folder
    session()->forget('elfinder');

    // Get columns
    $columns = UserController::getImportColumns();

    return view('app.users.import-users', compact('columns'));
  }

  /**
   * Download example Excel file
   */

  public function getDownloadExampleExcel() {
    // Destroy elFinder sessions, so directory defaults to personal folder
    session()->forget('elfinder');

    // Filename
    $filename = str_slug(str_replace([':','/',' '], '-', config('system.name') . '-' . trans('g.people')));

    // Get columns and transform to collection
    $columns = UserController::getImportColumns();

    $array = [];
    foreach($columns as $column) {
      $array[] = $column['label'];
    }
    $collection = collect([$array]);

    return \Excel::download(new class($collection) implements \Maatwebsite\Excel\Concerns\FromCollection {
      public function __construct($collection) {
        $this->collection = $collection;
      }
      public function collection() {
        return $this->collection;
      }
    }, $filename . '.xlsx');
  }

  /**
   * Parse Excel file and return json data for grid view
   */

  public function postParseExcel() {
    // Url to local path
    $file = urldecode(request()->get('file', null));
    $path = parse_url($file)['path'];
    $file = public_path($path);

    if (\File::exists($file)) {
      $data = \Excel::toArray(function($reader) {}, $path, 'public');

      if (isset($data[0])) {
        return response()->json($data[0]);
      } else {
        return response()->json(['msg' => trans('g.error_parsing_file')]);
      }
    } else {
      return response()->json(['msg' => trans('g.file_not_found')]);
    }
  }

  /**
   * Post grid view data for import
   */

  public function postImport() {
    $data = request()->get('data', null);

    if ($data !== null) {
      // Get columns
      $columns = UserController::getImportColumns();

      // Get countries
      $countries = \Countries::getList(auth()->user()->getLanguage(), 'php');
      $countries = array_keys($countries);

      $rows_success = 0;
      $rows_failed = 0;

      foreach($data as $row) {
        // Match row columns
        foreach ($columns as $i => $column) {
          $row[$column['name']] = ($row[$i] != '') ? $row[$i] : null;
        }

        // Check required fields
        if ($row['name'] == null || $row['email'] == null) {
          $rows_failed++;
          continue;
        }

        // Validate email address
        $email = validator(['email' => $row['email']], ['email' => 'required|email|unique:users,email']);
        if ($email->errors()->count() > 0) {
          $rows_failed++;
          continue;
        }

        // Parse password
        if ($row['password'] != null) {
          $row['password'] = \Illuminate\Support\Facades\Hash::make($row['password']);
        } else {
          $row['password'] = \Illuminate\Support\Facades\Hash::make(str_random(12));
        }

        // Parse role
        if ($row['role'] == null) {
          $row['role'] = 3;
        } elseif (is_numeric($row['role']) && $row['role'] <= \App\Role::all()->count()) {
          // Valid number
        } else {
          $result = \App\Role::whereRaw('LOWER(name) = ?', [strtolower($row['role'])])->first();
          if ($result !== null) {
            $row['role'] = $result->id;
          } else {
            $row['role'] = 3;
          }
        }

        // Parse country code
        if ($row['country_code'] != null) {
          if (strlen($row['country_code']) == 2) {
            $row['country_code'] = strtoupper($row['country_code']);
            if (! in_array($row['country_code'], $countries)) {
              $row['country_code'] = null;
            }
          } else {
            $row['country_code'] = null;
          }
        }

        // Validations passed, insert record
        $user = new \App\User;

        $user->active = true;
        $user->email_verified_at = \Carbon\Carbon::now('UTC');

        foreach ($columns as $i => $column) {
          if ($column['name'] != 'role') {
          $user->{$column['name']} = $row[$column['name']];
          }
        }

        $user->save();

        // Set role
        $user->assignRole(\App\Role::find($row['role']));

        $rows_success++;
      }

      return response()->json([
        'icon' => url('assets/img/icons/fe/check-circle.svg'), 
        'title' => trans('g.finished'), 
        'msg' => trans('g.import_results', ['rows_success' => $rows_success, 'rows_failed' => $rows_failed])
      ]);
    } else {
      return response()->json(['msg' => trans('g.error_parsing_file')]);
    }
  }
}