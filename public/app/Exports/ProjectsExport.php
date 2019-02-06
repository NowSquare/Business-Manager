<?php

namespace App\Exports;

use Platform\Models\Project as ExportProject;

use PhpOffice\PhpSpreadsheet\Shared;
use PhpOffice\PhpSpreadsheet\Style;

use Maatwebsite\Excel\Concerns\WithColumnFormatting;

use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;

use Kris\LaravelFormBuilder\FormBuilder;
use Kris\LaravelFormBuilder\FormBuilderTrait;

use App\Forms\Project;

class ProjectsExport implements ShouldAutoSize, FromCollection, WithColumnFormatting, WithHeadings, WithMapping
{
    use FormBuilderTrait;
    use Exportable;

    public function __construct() {
      // Get form for columns
      $form = $this->form(Project::class);
      $model = \Platform\Models\Project::first()->getFillable();
      $columns = $form->getFields();

      // Remove columns
      array_forget($columns, [
        'logo', 
        'role', 
        'users', 
        'projects', 
        'header1', 
        'header2', 
        'header3', 
        'header4', 
        'header5', 
        'header6', 
        'header7', 
        'header8', 
        'header9', 
        'header10',
        'desc1',
        'proposition_valid_until',
        'client_can_comment',
        'client_can_view_tasks',
        'client_can_edit_tasks',
        'client_can_view_description',
        'client_can_upload_files',
        'client_can_view_proposition',
        'client_can_approve_proposition',
        'notify_people_involved',
        'managers',
        'notes', 
        'back', 
        'submit'
      ]);

      $table_columns = [];
      $header_names = [];

      foreach ($columns as $column_name => $column) {
        $options = $column->getOptions();

        $table_columns[] = $column_name;
        $header_names[] = $options['label'];
      }

      // Add columns for export
      array_unshift($table_columns, 'id');
      array_unshift($header_names, 'ID');

      $header_names[] = 'Created at';
      $header_names[] = 'Created by';
      $header_names[] = 'Updated at';
      $header_names[] = 'Updated by';

      $table_columns[] = 'created_at';
      $table_columns[] = 'created_by';
      $table_columns[] = 'updated_at';
      $table_columns[] = 'updated_by';

      $this->table_columns = $table_columns;
      $this->header_names = $header_names;
    }

    public function headings(): array
    {
      return $this->header_names;
    }
   
    /**
    * @var UsersExport $model
    */
    public function map($model): array
    {
      $mapping = [];

      $project_statuses = \Platform\Models\ProjectStatus::all()->pluck('name', 'id');
      $companies = \Platform\Models\Company::all()->pluck('name', 'id');

      foreach ($this->table_columns as $column) {
        if ($column == 'active') {
          $mapping[] = ($model->{$column} == 1) ? trans('g.yes') : trans('g.no');
        } elseif ($column == 'phone' || $column == 'mobile' || $column == 'fax') {
          $mapping[] = ($model->{$column} != '') ? '"' . $model->{$column} . '"' : '';
        } elseif ($column == 'project_status_id') {
          $mapping[] = ($model->{$column} != '' && isset($project_statuses[$model->{$column}])) ? $project_statuses[$model->{$column}] : '';
        } elseif ($column == 'company_id') {
          $mapping[] = ($model->{$column} != '' && isset($companies[$model->{$column}])) ? $companies[$model->{$column}] : '';
        } else {
          $mapping[] = $model->{$column};
        }
      }

      return $mapping;
    }
    
    /**
     * @return array
     */
    public function columnFormats(): array
    {
        return [
            'G' => '@',
            'K' => '@',
        ];
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
      // Query model
      $query = \Platform\Models\Project::select($this->table_columns);

      // Filter assigned records
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

      return $query->get();
    }
}
