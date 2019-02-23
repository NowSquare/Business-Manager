<?php namespace Platform\Controllers\App;

use Platform\Controllers\Core;

use Kris\LaravelFormBuilder\FormBuilder;
use Kris\LaravelFormBuilder\FormBuilderTrait;

use App\Forms\Company;

use Illuminate\Support\Facades\Mail;
use App\Mail\NotifyCompanyCreate;

use App\Exports\CompaniesExport;

class CompanyController extends \App\Http\Controllers\Controller {

  use FormBuilderTrait;

  /*
   |--------------------------------------------------------------------------
   | Company Controller
   |--------------------------------------------------------------------------
   */

  /**
   * Companies list
   */

  public function getCompanyList(FormBuilder $formBuilder) {

    // Generate form
    $form = $formBuilder->create('App\Forms\Company', [
      'language_name' => 'g',
      'data' => ['ajax' => url('companies/json')]
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

    return view('app.companies.list-companies', compact('columns', 'form'));
  }

  /**
   * Companies list json
   */

  public function getCompanyListJson(FormBuilder $formBuilder) {
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
    $form = $formBuilder->create('App\Forms\Company', [
      'language_name' => 'g'
    ]);

    $table = 'companies';
    $select_columns = [];
    $select_columns[] = $table . '.id';
    $select_columns[] = $table . '.default';
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
    $query = \Platform\Models\Company::select($select_columns);

    // Filter assigned records
    if (! auth()->user()->can('all-companies')) {
      $query = $query->whereHas('users', function($query) {
        $query->where('user_id', auth()->user()->id);
      });
    }

    $count = $query->where(function ($query) use($q, $search_columns) {
        if($q != '') {
          foreach ($search_columns as $search_column) {
            $query->orWhere($search_column, 'like', '%' . $q . '%');
          }
        }
      })->count();

    $records = $query->orderBy('active', 'desc')->orderBy('default', 'desc')->orderBy($order_by, $order)
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
        $row[$column['column_name']] = $record->{$column['column_name']};
      }

      //$users = $record->users->where('active', 1);
      $users = $record->users->sortBy(function ($user, $key) {
        return ! $user->active . ' ' . $user->name;
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

      $row['users'] = $user_data;
      $row['default'] = $record->default;
      $row['active'] = $record->active;
      $row['sl'] = Core\Secure::array2string(array('company_id' => $record->id));

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
   * View company
   */

  public function getViewCompany($sl, FormBuilder $formBuilder) {
    $qs = Core\Secure::string2array($sl);
    $id = $qs['company_id'];

    if (is_numeric($id)) {
      // Set model
      $company = \Platform\Models\Company::findOrFail($id);

      // Get form
      $form = $formBuilder->create('App\Forms\Company', [
        'url' => '#',
        'class' => 'disabled-view-form',
        'model' => $company->toArray(), // Pass model as array so hidden fields are respected
        'language_name' => 'g',
        'data' =>  ['model' => $company] // Pass model as collection for field processing
      ]);

      $form->remove('active');
      $form->remove('active');
      $form->remove('notes');

      $form->disableFields();

      // Check if user is assigned
      $query = \Platform\Models\Company::select(['id']);
      if (! auth()->user()->can('all-companies')) {
        $query = $query->whereHas('users', function($query) {
          $query->where('user_id', auth()->user()->id);
        });
      }
      $query = $query->find($id);
      $can_access_company = ($query !== null) ? true : false;

      return view('app.companies.view-company', compact('form', 'company', 'sl', 'can_access_company'));
    }
  }

  /**
   * Create company
   */

  public function getCreateCompany(FormBuilder $formBuilder) {
    // Get form
    $form = $formBuilder->create('App\Forms\Company', [
      'method' => 'POST',
      'enctype' => 'multipart/form-data',
      'url' => url('companies/create'),
      'language_name' => 'g',
      'data' => ['create' => true]
    ]);

    return view('app.companies.create-company', compact('form'));
  }

  /**
   * Create company post
   */

  public function postCreateCompany(FormBuilder $formBuilder) {
    // Form
    $form = $this->form(Company::class);

    // Validate form
    if (! $form->isValid()) {
      return redirect()->back()->withErrors($form->getErrors())->withInput();
    }

    $form_fields = $form->getFieldValues();

    // Process image attachment field
    if (request()->get('logo_changed') == 1) {
      if ($form_fields['logo'] == null) {
        $form_fields['logo'] = \Czim\Paperclip\Attachment\Attachment::NULL_ATTACHMENT;
      }
    } else {
      array_forget($form_fields, 'logo');
    }

    // Set other company's default to null
    if (request()->get('default', null) == 1) {
      \DB::table('companies')->where('account_id', '=', auth()->user()->account_id)->update(['default' => null]);
      $form_fields['active'] = 1;
    }

    // Create record
    $model = \Platform\Models\Company::create($form_fields);

    // Process companies many-to-many
    $model->users()->sync($form_fields['users']);

    // Log
    Core\Log::add(
      'create_company', 
      trans('g.log_company_create_company', ['name' => auth()->user()->name, 'company' => $model->name]),
      '\Platform\Models\Company',
      $model->id,
      auth()->user()
    );

    return redirect('companies')->with('success', trans('g.form_success'));
  }

  /**
   * Edit company
   */

  public function getEditCompany($sl, FormBuilder $formBuilder) {
    $qs = Core\Secure::string2array($sl);
    $id = $qs['company_id'];

    if (is_numeric($id)) {
      // Set model
      $company = \Platform\Models\Company::findOrFail($id);

      // Get form
      $form = $formBuilder->create('App\Forms\Company', [
        'method' => 'POST',
        'enctype' => 'multipart/form-data',
        'url' => url('companies/edit/' . $sl),
        'model' => $company->toArray(), // Pass model as array so hidden fields are respected
        'language_name' => 'g',
        'data' =>  ['model' => $company] // Pass model as collection for field processing
      ]);

      // History
      $limit_log = 20;
      $history = \Platform\Models\Log::where('model', '\Platform\Models\Company')->where('model_id', $id)->where('user_id', '<>', $id)->orderBy('created_at', 'desc')->limit($limit_log)->get();

      return view('app.companies.edit-company', compact('company', 'form', 'history', 'limit_log'));
    }
  }

  /**
   * Edit company post
   */

  public function postEditCompany($sl, FormBuilder $formBuilder) {
    $qs = Core\Secure::string2array($sl);
    $id = $qs['company_id'];

    if (is_numeric($id)) {
      // Form
      $form = $this->form(Company::class);

      // Override validation
      $form->validate(['email' => 'nullable|email|unique:companies,email,' . $qs['company_id']]);

      $model = \Platform\Models\Company::findOrFail($id);

      // Validate form
      if (! $form->isValid()) {
        return redirect()->back()->withErrors($form->getErrors())->withInput();
      }

      $form_fields = $form->getFieldValues();

      // Process image attachment field
      if (request()->get('logo_changed') == 1) {
        if ($form_fields['logo'] == null) {
          $form_fields['logo'] = \Czim\Paperclip\Attachment\Attachment::NULL_ATTACHMENT;
        }
      } else {
        array_forget($form_fields, 'logo');
      }

      // Set other company's default to null
      if (request()->get('default', null) == 1) {
        \DB::table('companies')->where('id', '<>', $qs['company_id'])->where('account_id', '=', auth()->user()->account_id)->update(['default' => null]);
        $form_fields['active'] = 1;
      }

      // Process companies many-to-many
      $model->users()->sync($form_fields['users']);

      $model->fill($form_fields);
      $model->save();

      // Log
      Core\Log::add(
        'update_company', 
        trans('g.log_company_update_company', ['name' => auth()->user()->name, 'company' => $model->name]),
        '\Platform\Models\Company',
        $model->id,
        auth()->user()
      );
    }

    return redirect('companies')->with('success', trans('g.form_success'));
  }

  /**
   * Delete (selected) companies
   */

  public function postDeleteCompanies() {
    $msg = null;
    $ids = request()->get('ids');

    if (is_array($ids)) {
      foreach ($ids as $id) {
        // Filter assigned records
        $query = \Platform\Models\Company::select(['id', 'name', 'email']);
        if (! auth()->user()->can('all-companies')) {
          $query = $query->whereHas('users', function($query) {
            $query->where('user_id', auth()->user()->id);
          });
        }
        $query = $query->find($id);
        if ($query !== null) {
          // Check if company is used for project or invoice
          $project = \Platform\Models\Project::where('company_id', '=', $id)->get();
          $invoice = \Platform\Models\Project::where('company_id', '=', $id)->get();

          if ($project->count() === 0 && $invoice->count() === 0) {
            // Log
            Core\Log::add(
              'delete_company', 
              trans('g.log_company_delete_company', ['name' => auth()->user()->name, 'company' => $query->name]),
              '\Platform\Models\Company',
              $query->id,
              auth()->user()
            );

            // Delete
            $query->delete();
          } else {
            $msg = trans('g.company_could_not_be_deleted');
          }
        }
      }
    }

    if ($msg !== null) {
      return response()->json(['msg' => $msg]);
    } else {
      return response()->json(true);
    }
  }

  /**
   * Export records
   */

  public function getExportRecords($ext) {
    // Filename
    $filename = str_slug(str_replace([':','/',' '], '-', config('system.name') . '-' . trans('g.companies') . '-' . \Carbon\Carbon::now(auth()->user()->getTimezone())->format(auth()->user()->getUserDateFormat() . '-' . auth()->user()->getUserTimeFormat())), '-');

    switch ($ext) {
      case 'xlsx'; return (new CompaniesExport)->download($filename . '.' . $ext, \Maatwebsite\Excel\Excel::XLSX); break;
      case 'xls'; return (new CompaniesExport)->download($filename . '.' . $ext, \Maatwebsite\Excel\Excel::XLS); break;
      case 'csv'; return (new CompaniesExport)->download($filename . '.' . $ext, \Maatwebsite\Excel\Excel::CSV); break;
      case 'html'; return (new CompaniesExport)->download($filename . '.' . $ext, \Maatwebsite\Excel\Excel::HTML); break;
    }
  }

}