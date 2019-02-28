<?php

namespace App\Exports;

use App\User as ExportUser;

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

use App\Forms\User;

class UsersExport implements ShouldAutoSize, FromCollection, WithColumnFormatting, WithHeadings, WithMapping
{
    use FormBuilderTrait;
    use Exportable;

    public function __construct() {
      // Get form for columns
      $form = $this->form(User::class);
      $model = \App\User::first()->getFillable();
      $columns = $form->getFields();

      // Remove columns
      array_forget($columns, [
        'password', 
        'avatar', 
        'role', 
        'companies', 
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
        'header11',
        'timezone', 
        'date_format', 
        'time_format', 
        'decimals', 
        'decimal_seperator', 
        'thousands_seperator', 
        'language', 
        'locale', 
        'seperators', 
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

      $header_names[] = 'Role';
      $header_names[] = 'Lead source';
      $header_names[] = 'Created at';
      $header_names[] = 'Created by';
      $header_names[] = 'Updated at';
      $header_names[] = 'Updated by';

      $table_columns[] = 'use_parentheses_for_negative_numbers'; // Substitute for custom role accessor
      $table_columns[] = 'lead_source';
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
    * @var UsersExport $user
    */
    public function map($user): array
    {
      $mapping = [];

      foreach ($this->table_columns as $column) {
        if ($column == 'active') {
          $mapping[] = ($user->{$column} == 1) ? trans('g.yes') : trans('g.no');
        } elseif ($column == 'phone' || $column == 'mobile' || $column == 'fax') {
          $mapping[] = ($user->{$column} != '') ? '"' . $user->{$column} . '"' : '';
        } else {
          $mapping[] = $user->{$column};
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
            'K' => '@', //\PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING,
            'O' => '@',
        ];
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
      // Filter assigned records
      $query = \App\User::select($this->table_columns);

      if (! auth()->user()->can('all-users')) {
        $query = $query->has('assignedUsers');
      }

      $users = collect($query->get());

      $users->map(function ($users) {
          $users['use_parentheses_for_negative_numbers'] = $users->role;
          $users['lead_source'] = $users->lead_source;
          return $users;
      });

      return $users;
    }
}
