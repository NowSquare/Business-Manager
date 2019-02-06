<?php

namespace App\Exports;

use Platform\Models\Company as ExportCompany;

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

use App\Forms\Company;

class CompaniesExport implements ShouldAutoSize, FromCollection, WithColumnFormatting, WithHeadings, WithMapping
{
    use FormBuilderTrait;
    use Exportable;

    public function __construct() {
      // Get form for columns
      $form = $this->form(Company::class);
      $model = \Platform\Models\Company::first()->getFillable();
      $columns = $form->getFields();

      // Remove columns
      array_forget($columns, [
        'password', 
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
        'default', 
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
    * @var UsersExport $company
    */
    public function map($company): array
    {
      $mapping = [];

      foreach ($this->table_columns as $column) {
        if ($column == 'active') {
          $mapping[] = ($company->{$column} == 1) ? trans('g.yes') : trans('g.no');
        } elseif ($column == 'phone' || $column == 'mobile' || $column == 'fax') {
          $mapping[] = ($company->{$column} != '') ? '"' . $company->{$column} . '"' : '';
        } else {
          $mapping[] = $company->{$column};
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
            'G' => '@', //\PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING,
            'K' => '@',
        ];
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
      // Query model
      $query = \Platform\Models\Company::select($this->table_columns);

      // Filter assigned records
      if (! auth()->user()->can('all-companies')) {
        $query = $query->whereHas('users', function($query) {
          $query->where('user_id', auth()->user()->id);
        });
      }

      return $query->get();
    }
}
