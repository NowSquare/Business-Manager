<?php

namespace App\Exports;

use Platform\Models\Invoice as ExportInvoice;

use PhpOffice\PhpSpreadsheet\Shared;
use PhpOffice\PhpSpreadsheet\Style;

use Maatwebsite\Excel\Concerns\WithColumnFormatting;

use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;

use Money\Money;

use App\Forms\Project;

class InvoicesExport implements ShouldAutoSize, FromCollection, WithColumnFormatting, WithHeadings, WithMapping
{
    use Exportable;

    public function __construct() {

      $header_names[] = trans('g.invoice_number');
      $header_names[] = trans('g.client');
      $header_names[] = trans('g.currency');
      $header_names[] = trans('g.total');
      $header_names[] = trans('g.total_discount');
      $header_names[] = trans('g.total_tax');
      $header_names[] = trans('g.issue_date');
      $header_names[] = trans('g.due_date');
      $header_names[] = trans('g.status');
      $header_names[] = trans('g.sent_date');
      $header_names[] = trans('g.partially_paid_date');
      $header_names[] = trans('g.paid_date');
      $header_names[] = trans('g.written_off_date');
      $header_names[] = 'Created at';
      $header_names[] = 'Created by';
      $header_names[] = 'Updated at';
      $header_names[] = 'Updated by';

      $table_columns[] = 'reference';
      $table_columns[] = 'company_id';
      $table_columns[] = 'currency_code';
      $table_columns[] = 'total';
      $table_columns[] = 'total_discount';
      $table_columns[] = 'total_tax';
      $table_columns[] = 'issue_date';
      $table_columns[] = 'due_date';
      $table_columns[] = 'status';
      $table_columns[] = 'sent_date';
      $table_columns[] = 'paid_date';
      $table_columns[] = 'partially_paid_date';
      $table_columns[] = 'written_off_date';
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

      $companies = \Platform\Models\Company::all()->pluck('name', 'id');

      foreach ($this->table_columns as $column) {
        if ($column == 'active') {
          $mapping[] = ($model->{$column} == 1) ? trans('g.yes') : trans('g.no');
        } elseif ($column == 'phone' || $column == 'mobile' || $column == 'fax') {
          $mapping[] = ($model->{$column} != '') ? '"' . $model->{$column} . '"' : '';
        } elseif ($column == 'total' || $column == 'total_discount' || $column == 'total_tax') {
          $mapping[] = (is_numeric($model->{$column})) ? Money::{$model->currency_code}($model->{$column})->divide(100)->getAmount() : '';
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
      $query = \Platform\Models\Invoice::select($this->table_columns);
      return $query->get();
    }
}
