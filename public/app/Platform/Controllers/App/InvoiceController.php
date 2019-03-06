<?php namespace Platform\Controllers\App;

use Platform\Controllers\Core;
use Money\Money;
use App\Exports\InvoicesExport;

use Kris\LaravelFormBuilder\FormBuilder;
use Kris\LaravelFormBuilder\FormBuilderTrait;

use Illuminate\Support\Facades\Mail;
use App\Mail\SendInvoice;

use App\Forms\Invoice as InvoiceForm;

class InvoiceController extends \App\Http\Controllers\Controller {

  use FormBuilderTrait;

  /*
   |--------------------------------------------------------------------------
   | Invoice Controller
   |--------------------------------------------------------------------------
   */

  /**
   * Invoices list
   */

  public function getInvoiceList() {
    return view('app.invoices.list-invoices');
  }

  /**
   * Invoices list json
   */

  public function getInvoiceListJson() {
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

    $table = 'invoices';
    $select_columns = [];
    $select_columns[] = $table . '.id';
    $select_columns[] = $table . '.created_at';
    $select_columns[] = $table . '.reference';
    $select_columns[] = $table . '.total';
    $select_columns[] = $table . '.currency_code';
    $select_columns[] = $table . '.issue_date';
    $select_columns[] = $table . '.due_date';
    $select_columns[] = $table . '.sent_date';
    $select_columns[] = $table . '.resent_date';
    $select_columns[] = $table . '.partially_paid_date';
    $select_columns[] = $table . '.paid_date';
    $select_columns[] = $table . '.written_off_date';
    $search_columns = [];
    $search_columns[] = $table . '.issue_date';
    $search_columns[] = $table . '.reference';
    $search_columns[] = $table . '.currency_code';
    $search_columns[] = 'companies.name';

    // Query model
    $query = \Platform\Models\Invoice::select(array_merge($select_columns, ['company_id']))->join('companies', 'companies.id', '=', 'invoices.company_id')->with(['client:id,name']);

    // Filter status
    $status = request()->input('columns.1.search.value', 0);

    $count = $query->where(function ($query) use($q, $search_columns) {
        if($q != '') {
          foreach ($search_columns as $search_column) {
            $query->orWhere($search_column, 'like', '%' . $q . '%');
          }
        }
      })->count();

    $records = $query->orderBy('issue_date', 'desc');

    $records = $records->where(function ($query) use($q, $search_columns) {
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
      if ($status == '' || $status == '0' || $status == $record->status_key) {
        $row['id'] = $record->id;
        $row['DT_RowId'] = 'row_' . $record->id;

        $row['client_name'] = $record->client->name ?? null;
        $row['status'] = $record->status;
        $row['status_key'] = $record->status_key;
        $row['status_color'] = $record->status_color;
        $row['total'] = $record->total / 100;
        $row['issue_date'] = auth()->user()->formatDate($record->issue_date, 'date_medium');
        $row['due_date'] = auth()->user()->formatDate($record->due_date, 'date_medium');
        $row['sent_date'] = auth()->user()->formatDate($record->sent_date, 'date_medium');
        $row['resent_date'] = auth()->user()->formatDate($record->resent_date, 'date_medium');
        $row['partially_paid_date'] = auth()->user()->formatDate($record->partially_paid_date, 'date_medium');
        $row['paid_date'] = auth()->user()->formatDate($record->paid_date, 'date_medium');
        $row['written_off_date'] = auth()->user()->formatDate($record->written_off_date, 'date_medium');
        $row['reference'] = $record->reference;
        $row['currency_code'] = $record->currency_code;
        $row['sl'] = Core\Secure::array2string(array('invoice_id' => $record->id));

        $data[] = $row;
      }
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
   * Create invoice
   */

  public function getCreateInvoice($type = null, $sl = null, $tax = null, $only_completed_tasks = null, FormBuilder $formBuilder) {
    // Get form
    $form = $formBuilder->create('App\Forms\Invoice', [
      'method' => 'POST',
      'enctype' => 'multipart/form-data',
      'url' => url('invoices/create'),
      'language_name' => 'g',
      'data' => ['create' => true]
    ]);

    $invoice = null;
    $action = 'create';
    $title = trans('g.create_invoice');
    $project = null;
    $items = [];

    if ($type == 'project-proposition' || $type == 'project-tasks') {
      $qs = Core\Secure::string2array($sl);
      $project_id = $qs['project_id'];

      if (is_numeric($project_id)) {
        $project = \Platform\Models\Project::findOrFail($project_id);

        $project->reference = null;
        $project->due_date = null;

        // Defaults
        $project->notes = Core\Settings::get('invoice_notes', 'text');
        
        $invoice_payment_methods = \Platform\Controllers\Core\Settings::get('invoice_payment_methods', 'json');
        if ($invoice_payment_methods !== null) {
          $project->additional_fields = $invoice_payment_methods;
        }

        // Override form
        $form = $formBuilder->create('App\Forms\Invoice', [
          'method' => 'POST',
          'enctype' => 'multipart/form-data',
          'url' => url('invoices/create'),
          'model' => $project->toArray(), // Pass model as array so hidden fields are respected
          'language_name' => 'g',
          'data' =>  ['model' => $project, 'sl' => $sl] // Pass model as collection for field processing
        ]);

        if ($type == 'project-proposition') {
          if (isset($project->propositions[0])) {
            $items = $project->propositions[0]->items;
          }
        }

        if ($type == 'project-tasks') {
          if ($project->tasks->count() > 0) {
            foreach ($project->tasks as $task) {
              if ($task->billable) {
                if ($only_completed_tasks == 0 || ($only_completed_tasks == 1 && $task->project_status_id == 72)) {
                  $item = new \stdClass();
                  $item->type = 'item';
                  $item->description = $task->subject;
                  $item->quantity = $task->hours;
                  $item->unit = 'hour';
                  $item->unit_price = $task->hourly_rate;
                  $item->tax_rate = $tax;
                  $item->discount_type = null;

                  $items[] = $item;
                }
              }
            }
          }
        }
      }
    }

    // Default company
    $from = auth()->user()->getDefaultCompany();

    return view('app.invoices.invoice', compact('form', 'action', 'title', 'invoice', 'items', 'from'));
  }

  /**
   * Create invoice post
   */

  public function postCreateInvoice(FormBuilder $formBuilder) {
    // Form
    $form = $this->form('App\Forms\Invoice');

    // Validate form
    if (! $form->isValid()) {
      return redirect()->back()->withErrors($form->getErrors())->withInput();
    }

    $form_fields = $form->getFieldValues();

    // Set currency for Money functions
    $currency = $form_fields['currency_code'];

    // Create record
    $invoice = \Platform\Models\Invoice::create($form_fields);

    // Payment methods
    $invoice['additional_fields->payment_method_terms_selected'] = request()->get('payment_method_terms_selected', 0);
    $invoice['additional_fields->payment_method_terms'] = request()->get('payment_method_terms', null);

    $invoice['additional_fields->payment_method_bank_selected'] = request()->get('payment_method_bank_selected', 0);
    $invoice['additional_fields->payment_method_bank_bic_swift'] = request()->get('payment_method_bank_bic_swift', null);
    $invoice['additional_fields->payment_method_bank_iban'] = request()->get('payment_method_bank_iban', null);
    $invoice['additional_fields->payment_method_bank_name'] = request()->get('payment_method_bank_name', null);

    $invoice['additional_fields->payment_method_cash_selected'] = request()->get('payment_method_cash_selected', 0);

    $invoice['additional_fields->payment_method_check_selected'] = request()->get('payment_method_cash_selected', 0);

    if (request()->get('save_payment_methods', 0) == 1) {
      Core\Settings::set('invoice_payment_methods', 'json', [
        'payment_method_terms_selected' => request()->get('payment_method_terms_selected', 0),
        'payment_method_terms' => request()->get('payment_method_terms', null),
        'payment_method_bank_selected' => request()->get('payment_method_bank_selected', 0),
        'payment_method_bank_bic_swift' => request()->get('payment_method_bank_bic_swift', null),
        'payment_method_bank_iban' => request()->get('payment_method_bank_iban', null),
        'payment_method_bank_name' => request()->get('payment_method_bank_name', null),
        'payment_method_cash_selected' => request()->get('payment_method_cash_selected', 0),
        'payment_method_check_selected' => request()->get('payment_method_cash_selected', 0)
      ]);
    }

    // Save notesfor future use
    if (request()->get('save_notes', 0) == 1) {
      Core\Settings::set('invoice_notes', 'text', request()->get('notes', null));
    }

    $invoice->save();

    // Process invoice items
    $invoice_type = request()->get('invoice_type', null);
    $invoice_description = request()->get('invoice_description', null);
    $invoice_quantity = request()->get('invoice_quantity', null);
    $invoice_unit = request()->get('invoice_unit', null);
    $invoice_discount_unit = request()->get('invoice_discount_unit', null);
    $invoice_unit_price = request()->get('invoice_unit_price', null);
    $invoice_tax_rate = request()->get('invoice_tax_rate', null);

    if ($invoice_type !== null) {
      // Totals     
      $sub_total = Money::{$currency}(0);
      $tax_total = Money::{$currency}(0);
      $grand_total = Money::{$currency}(0);
      $discount_total = Money::{$currency}(0);

      $taxes = [];

      // Invoice items
      foreach ($invoice_type as $i => $row) {
        if (
          $invoice_type[$i] == 'item' && 
          //$invoice_description[$i] != '' && 
          $invoice_quantity[$i] != '' && 
          $invoice_unit_price[$i] != '' && 
          $invoice_tax_rate[$i] != ''
        ) {
          $invoice_item = new \Platform\Models\InvoiceItem;
          $invoice_item->invoice_id = $invoice->id;
          $invoice_item->type = $invoice_type[$i];
          $invoice_item->description = $invoice_description[$i];
          if ($invoice_quantity[$i] !== null) $invoice_quantity[$i] = $invoice_quantity[$i] * 100;
          $invoice_item->quantity = $invoice_quantity[$i];
          $invoice_item->unit = $invoice_unit[$i];
          $invoice_item->unit_price = $invoice_unit_price[$i] * 100;
          $invoice_item->tax_rate = $invoice_tax_rate[$i];

          $invoice_item->save();

          $row_total_excl_taxes = Money::{$currency}($invoice_item->unit_price)->multiply($invoice_item->quantity)->divide(100);
          $tax = $row_total_excl_taxes->multiply($invoice_item->tax_rate)->divide(10000);

          $sub_total = $sub_total->add($row_total_excl_taxes);
          $tax_total = $tax_total->add($tax);
          $grand_total = $grand_total->add($row_total_excl_taxes->add($tax));
        }
      }

      foreach ($invoice_type as $i => $row) {
        if (
          $invoice_type[$i] == 'discount' && 
          //$invoice_description[$i] != '' && 
          $invoice_quantity[$i] != '' && 
          $invoice_discount_unit[$i] != '' && 
          $invoice_tax_rate[$i] != ''
        ) {
          $invoice_item = new \Platform\Models\InvoiceItem;
          $invoice_item->invoice_id = $invoice->id;
          $invoice_item->type = $invoice_type[$i];
          $invoice_item->description = $invoice_description[$i];
          $invoice_item->quantity = $invoice_quantity[$i] * 100;
          $invoice_item->discount_type = $invoice_discount_unit[$i];
          $invoice_item->tax_rate = $invoice_tax_rate[$i];

          $invoice_item->save();

          if ($invoice_item->discount_type == '%') {
            $row_total_excl_taxes = $sub_total->multiply($invoice_item->quantity)->divide(10000);
          } else {
            $row_total_excl_taxes = Money::{$currency}($invoice_item->quantity)->divide(100);
          }

          $tax = $row_total_excl_taxes->multiply($invoice_item->tax_rate)->divide(10000);

          $discount_total = $discount_total->add($row_total_excl_taxes);
          $tax_total = $tax_total->subtract($tax);
          $grand_total = $grand_total->subtract($row_total_excl_taxes->add($tax));
        }
      }

      $invoice->total = $grand_total->getAmount();
      $invoice->total_discount = $discount_total->getAmount();
      $invoice->total_tax = $tax_total->getAmount();
      $invoice->save();
    }

    // Generate PDF
    $this->generateInvoicePdf($invoice->id);

    if (request()->get('send', 0) == 1) {
      $msg = trans('g.invoice_success_sent', ['email' => $invoice->client->email]);
      $this->sendInvoice($invoice);
    } else {
      $msg = trans('g.form_success');
    }

    // Log
    Core\Log::add(
      'create_invoice', 
      trans('g.log_invoice_create_invoice', ['name' => auth()->user()->name, 'invoice' => $invoice->reference . ' (' . $invoice->client->name . ')']),
      '\Platform\Models\Invoice',
      $invoice->id,
      auth()->user()
    );

    return redirect('invoices')->with('success', $msg);
  }

  /**
   * Edit invoice
   */

  public function getEditInvoice($sl, FormBuilder $formBuilder) {
    $qs = Core\Secure::string2array($sl);
    $id = $qs['invoice_id'];

    $action = 'edit';
    $title = trans('g.edit_invoice');

    if (is_numeric($id)) {
      // Set invoice
      $invoice = \Platform\Models\Invoice::findOrFail($id);
      $items = $invoice->items;

      // Get form
      $form = $formBuilder->create('App\Forms\Invoice', [
        'method' => 'POST',
        'enctype' => 'multipart/form-data',
        'url' => url('invoices/edit/' . $sl),
        'model' => $invoice->toArray(), // Pass model as array so hidden fields are respected
        'language_name' => 'g',
        'data' =>  ['model' => $invoice, 'sl' => $sl] // Pass model as collection for field processing
      ]);

      // History
      $history = \Platform\Models\Log::where('model', '\Platform\Models\Invoice')->where('model_id', $id)->where('user_id', '<>', $id)->orderBy('created_at', 'desc')->get();

      // Default company
      $from = auth()->user()->getDefaultCompany();

      return view('app.invoices.invoice', compact('sl', 'invoice', 'items', 'form', 'action', 'title', 'from', 'history'));
    }
  }

  /**
   * Edit invoice post
   */

  public function postEditInvoice($sl) {
    $msg = trans('g.form_success');
    $qs = Core\Secure::string2array($sl);
    $id = $qs['invoice_id'];

    if (is_numeric($id)) {
      // Form
      $form = $this->form('App\Forms\Invoice');

      // Override validation
      //$form->validate(['email' => 'nullable|email|unique:invoices,email,' . $qs['invoice_id']]);

      $invoice = \Platform\Models\Invoice::findOrFail($id);

      // Validate form
      if (! $form->isValid()) {
        return redirect()->back()->withErrors($form->getErrors())->withInput();
      }

      $form_fields = $form->getFieldValues();

      // Set currency for Money functions
      $currency = $form_fields['currency_code'];

      // Add updated_by manually because it isn't triggered if only relationships are updated
      $invoice->updated_by = auth()->user()->id;

      $invoice->fill($form_fields);

      // Payment methods
      $invoice['additional_fields->payment_method_terms_selected'] = request()->get('payment_method_terms_selected', 0);
      $invoice['additional_fields->payment_method_terms'] = request()->get('payment_method_terms', null);

      $invoice['additional_fields->payment_method_bank_selected'] = request()->get('payment_method_bank_selected', 0);
      $invoice['additional_fields->payment_method_bank_bic_swift'] = request()->get('payment_method_bank_bic_swift', null);
      $invoice['additional_fields->payment_method_bank_iban'] = request()->get('payment_method_bank_iban', null);
      $invoice['additional_fields->payment_method_bank_name'] = request()->get('payment_method_bank_name', null);

      $invoice['additional_fields->payment_method_cash_selected'] = request()->get('payment_method_cash_selected', 0);

      $invoice['additional_fields->payment_method_check_selected'] = request()->get('payment_method_check_selected', 0);

      $invoice->save();

      // Process invoice, first delete existing invoice and items
      $invoice->items()->delete();

      $invoice_type = request()->get('invoice_type', null);
      $invoice_description = request()->get('invoice_description', null);
      $invoice_quantity = request()->get('invoice_quantity', null);
      $invoice_unit = request()->get('invoice_unit', null);
      $invoice_discount_unit = request()->get('invoice_discount_unit', null);
      $invoice_unit_price = request()->get('invoice_unit_price', null);
      $invoice_tax_rate = request()->get('invoice_tax_rate', null);

      if ($invoice_type !== null) {
        // Totals     
        $sub_total = Money::{$currency}(0);
        $tax_total = Money::{$currency}(0);
        $grand_total = Money::{$currency}(0);
        $discount_total = Money::{$currency}(0);

        $taxes = [];

        // Insert invoice items
        foreach ($invoice_type as $i => $row) {
          if (
            $invoice_type[$i] == 'item' && 
            //$invoice_description[$i] != '' && 
            $invoice_quantity[$i] != '' && 
            $invoice_unit_price[$i] != '' && 
            $invoice_tax_rate[$i] != ''
          ) {
            $invoice_item = new \Platform\Models\InvoiceItem;
            $invoice_item->invoice_id = $invoice->id;
            $invoice_item->type = $invoice_type[$i];
            $invoice_item->description = $invoice_description[$i];
            $invoice_item->quantity = $invoice_quantity[$i] * 100;
            $invoice_item->unit = $invoice_unit[$i];
            $invoice_item->unit_price = $invoice_unit_price[$i] * 100;
            $invoice_item->tax_rate = $invoice_tax_rate[$i];

            $invoice_item->save();

            $row_total_excl_taxes = Money::{$currency}($invoice_item->unit_price)->multiply($invoice_item->quantity)->divide(100);
            $tax = $row_total_excl_taxes->multiply($invoice_item->tax_rate)->divide(10000);

            $sub_total = $sub_total->add($row_total_excl_taxes);
            $tax_total = $tax_total->add($tax);
            $grand_total = $grand_total->add($row_total_excl_taxes->add($tax));
          }
        }

        foreach ($invoice_type as $i => $row) {
          if (
            $invoice_type[$i] == 'discount' && 
            //$invoice_description[$i] != '' && 
            $invoice_quantity[$i] != '' && 
            $invoice_discount_unit[$i] != '' && 
            $invoice_tax_rate[$i] != ''
          ) {
            $invoice_item = new \Platform\Models\InvoiceItem;
            $invoice_item->invoice_id = $invoice->id;
            $invoice_item->type = $invoice_type[$i];
            $invoice_item->description = $invoice_description[$i];
            $invoice_item->quantity = $invoice_quantity[$i] * 100;
            $invoice_item->discount_type = $invoice_discount_unit[$i];
            $invoice_item->tax_rate = $invoice_tax_rate[$i];

            $invoice_item->save();

            if ($invoice_item->discount_type == '%') {
              $row_total_excl_taxes = $sub_total->multiply($invoice_item->quantity)->divide(10000);
            } else {
              $row_total_excl_taxes = Money::{$currency}($invoice_item->quantity)->divide(100);
            }

            $tax = $row_total_excl_taxes->multiply($invoice_item->tax_rate)->divide(10000);

            $discount_total = $discount_total->add($row_total_excl_taxes);
            $tax_total = $tax_total->subtract($tax);
            $grand_total = $grand_total->subtract($row_total_excl_taxes->add($tax));
          }
        }

        $invoice->total = $grand_total->getAmount();
        $invoice->total_discount = $discount_total->getAmount();
        $invoice->total_tax = $tax_total->getAmount();
        $invoice->save();
      }

      // Generate PDF
      $this->generateInvoicePdf($invoice->id);

      if (request()->get('send', 0) == 1) {
        $msg = trans('g.invoice_success_sent', ['email' => $invoice->client->email]);
        $this->sendInvoice($invoice);
      }

      // Log
      Core\Log::add(
        'update_invoice', 
        trans('g.log_invoice_update_invoice', ['name' => auth()->user()->name, 'invoice' => $invoice->reference . ' (' . $invoice->client->name . ')']),
        '\Platform\Models\Invoice',
        $invoice->id,
        auth()->user()
      );
    }

    return redirect('invoices')->with('success', $msg);
  }

  /**
   * Delete (selected) invoices
   */

  public function postDeleteInvoices() {
    $ids = request()->get('ids');

    if (is_array($ids)) {
      foreach ($ids as $id) {
        // Filter assigned records
        $query = \Platform\Models\Invoice::select(['id', 'reference', 'company_id'])->find($id);

        if ($query !== null && $query->status_key == 'draft') {
          // Log
          Core\Log::add(
            'delete_invoice', 
            trans('g.log_invoice_delete_invoice', ['name' => auth()->user()->name, 'invoice' => $query->reference . ' (' . $query->client->name . ')']),
            '\Platform\Models\Invoice',
            $query->id,
            auth()->user()
          );

          // Delete pdf
          $dir = public_path() . '/files/invoices/' . $query->id . '.pdf';

          if (\File::exists($dir)) {
            \File::deleteDirectory($dir);
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
    $filename = str_slug(str_replace([':','/',' '], '-', config('system.name') . '-' . trans('g.invoices') . '-' . \Carbon\Carbon::now(auth()->user()->getTimezone())->format(auth()->user()->getUserDateFormat() . '-' . auth()->user()->getUserTimeFormat())), '-');

    switch ($ext) {
      case 'xlsx'; return (new InvoicesExport)->download($filename . '.' . $ext, \Maatwebsite\Excel\Excel::XLSX); break;
      case 'xls'; return (new InvoicesExport)->download($filename . '.' . $ext, \Maatwebsite\Excel\Excel::XLS); break;
      case 'csv'; return (new InvoicesExport)->download($filename . '.' . $ext, \Maatwebsite\Excel\Excel::CSV); break;
      case 'html'; return (new InvoicesExport)->download($filename . '.' . $ext, \Maatwebsite\Excel\Excel::HTML); break;
    }
  }

  /**
   * Send invoice to client
   */

  public function sendInvoice($invoice) {
    if (! config('app.demo')) {
      // Default company
      $from = auth()->user()->getDefaultCompany();

      if ($from !== null) {
        // Send email
        Mail::to($invoice->client->email, $invoice->client->name)->send(new SendInvoice(auth()->user()->name, auth()->user()->email, $from->name, $invoice, $invoice->client->name, $invoice->client->email));
      }
    }

    $invoice->sent_to_email = $invoice->client->email;
    $invoice->sent_date = \Carbon\Carbon::now();
    $invoice->save();

    return true;
  }

  /**
   * Change invoice status
   */

  public function postChangeInvoiceStatus() {
    $id = request()->get('id', null);
    $status = request()->get('status', null);

    if (is_numeric($id)) {
      $invoice = \Platform\Models\Invoice::findOrFail($id);

      if ($status == 'sent') {
        $invoice->partially_paid_date = null;
        $invoice->paid_date = null;
        $invoice->written_off_date = null;
      }

      if ($status == 'partially_paid') {
        $invoice->partially_paid_date = \Carbon\Carbon::now();
        $invoice->paid_date = null;
        $invoice->written_off_date = null;
      }

      if ($status == 'paid') {
        $invoice->partially_paid_date = null;
        $invoice->paid_date = \Carbon\Carbon::now();
        $invoice->written_off_date = null;
      }

      if ($status == 'written_off') {
        $invoice->partially_paid_date = null;
        $invoice->paid_date = null;
        $invoice->written_off_date = \Carbon\Carbon::now();
      }

      $invoice->save();
    }

    return response()->json(true);
  }
  /**
   * Generate invoice pdf
   */

  public function generateInvoicePdf($id) {
    if (is_numeric($id)) {
      $invoice = \Platform\Models\Invoice::findOrFail($id);

      // Create dir if not exists
      $dir = storage_path('app/invoices');
      if (! \File::exists($dir)) {
        \File::makeDirectory($dir, $mode = 0777, true, true);
      }

      // Generate pdf
      $pdf = \PDF::loadView('pdf.invoice.invoice', compact('invoice'))
      ->setWarnings(false);

      $pdf->save($dir . '/' . $invoice->id . '.pdf');
    }
  }

  /**
   * Download invoice pdf
   */

  public function getInvoicePdf($sl) {
    $qs = Core\Secure::string2array($sl);
    $id = $qs['invoice_id'];

    if (is_numeric($id)) {
      $invoice = \Platform\Models\Invoice::findOrFail($id);
      $file = storage_path('app/invoices/' . $id . '.pdf');

      if (\File::exists($file)) {
        // Default company
        $from = auth()->user()->getDefaultCompany();

        return response()->download($file, 'invoice-' . str_slug($invoice->reference . '-' . $from->name . '-' . auth()->user()->formatDate($invoice->issue_date, 'date_medium')) . '.pdf');
      } else {
        $this->generateInvoicePdf($id);
        return $this->getInvoicePdf($sl);
      }
    }
  }
  /**
   * (re)send invoice
   */

  public function postSendInvoice() {
    $id = request()->get('id', null);

    if (is_numeric($id)) {
      $invoice = \Platform\Models\Invoice::findOrFail($id);
      $invoice->resent_date = \Carbon\Carbon::now();
      $invoice->save();

      $this->sendInvoice($invoice);

      return response()->json(['msg' => trans('g.invoice_success_sent', ['email' => $invoice->client->email])]);
    }

    return response()->json(true);
  }
}