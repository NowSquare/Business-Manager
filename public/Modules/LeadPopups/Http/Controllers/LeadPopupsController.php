<?php

namespace Modules\LeadPopups\Http\Controllers;

use Platform\Controllers\Core;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;

use Kris\LaravelFormBuilder\FormBuilder;
use Kris\LaravelFormBuilder\FormBuilderTrait;

use Modules\LeadPopups\Forms\Popup as PopupForm;

class LeadPopupsController extends Controller {

  use FormBuilderTrait;

  /**
   * Get popups list
   */
  public function getPopupList() {
    return view('leadpopups::list-popups');
  }

  /**
   * Popups list json
   */

  public function getPopupListJson() {
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

    $table = 'lead_popups';
    $select_columns = [];
    $select_columns[] = $table . '.id';
    $select_columns[] = $table . '.name';
    $select_columns[] = $table . '.views';
    $select_columns[] = $table . '.conversions';
    $select_columns[] = $table . '.active';
    $select_columns[] = $table . '.additional_fields';
    $select_columns[] = $table . '.created_at';
    $search_columns = [];
    $search_columns[] = $table . '.name';

    $order_by = (isset($select_columns[$order_by])) ? $select_columns[$order_by] : null;

    // Query model
    $query = \Modules\LeadPopups\Models\Popup::select($select_columns);

    $count = $query->where(function ($query) use($q, $search_columns) {
        if($q != '') {
          foreach ($search_columns as $search_column) {
            $query->orWhere($search_column, 'like', '%' . $q . '%');
          }
        }
      })->count();

    $records = $query->where(function ($query) use($q, $search_columns) {
        if($q != '') {
          foreach ($search_columns as $search_column) {
            $query->orWhere($search_column, 'like', '%' . $q . '%');
          }
        }
      })
      ->orderBy($order_by, $order)
      ->take($length)->skip($start)->get();

    if($length == -1) $length = $count;

    $data = [];

    foreach ($records as $record) {
      $row['id'] = $record->id;
      $row['DT_RowId'] = 'row_' . $record->id;
      $row['name'] = $record->name;
      $row['views'] = $record->views;
      $row['conversions'] = $record->conversions;
      $row['active'] = $record->active;
      $row['token'] = Core\Secure::staticHash($record->id);
      $row['trigger'] = trans('leadpopups::g.' . $record->additional_fields['trigger']) ?? null;
      $row['sl'] = Core\Secure::array2string(array('popup_id' => $record->id));

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
   * Create popup
   */

  public function getCreatePopup(FormBuilder $formBuilder) {
    $action = 'create';
    $title = trans('leadpopups::g.create_popup');

    // Get form
    $form = $formBuilder->create('Modules\LeadPopups\Forms\Popup', [
      'method' => 'POST',
      'enctype' => 'multipart/form-data',
      'url' => url('popups/create'),
      'language_name' => 'g',
      'data' => ['create' => true]
    ]);

    return view('leadpopups::popup', compact('form', 'action', 'title'));
  }

  /**
   * Create popup post
   */

  public function postCreatePopup(FormBuilder $formBuilder) {
    // Form
    $form = $this->form('Modules\LeadPopups\Forms\Popup');

    // Validate form
    if (! $form->isValid()) {
      return redirect()->back()->withErrors($form->getErrors())->withInput();
    }

    $form_fields = $form->getFieldValues();

    $fields = [];
    foreach ($form_fields as $key => $val) {
      if (starts_with($key, 'additional_fields_')) {
        $key = str_replace('additional_fields_', '', $key);
        $key = 'additional_fields->' . $key . '';
      }

      if ($key == 'form_fields') {
        $val = json_decode($val);
      }
      
      $fields[$key] = $val;
    }

    // Create record
    $popup = \Modules\LeadPopups\Models\Popup::create($fields);

    return redirect('popups')->with('success', trans('g.form_success'));
  }

  /**
   * Edit popup
   */

  public function getEditPopup($sl, FormBuilder $formBuilder) {
    $qs = Core\Secure::string2array($sl);
    $id = $qs['popup_id'];

    $action = 'edit';
    $title = trans('leadpopups::g.edit_popup');

    if (is_numeric($id)) {
      // Set popup
      $popup = \Modules\LeadPopups\Models\Popup::findOrFail($id);

      $popup->form_fields = json_encode($popup->form_fields);

      foreach ($popup->additional_fields as $key => $val) {
        $popup->{'additional_fields_' . $key} = $val;
      }

      // Get form
      $form = $formBuilder->create('Modules\LeadPopups\Forms\Popup', [
        'method' => 'POST',
        'enctype' => 'multipart/form-data',
        'url' => url('popups/edit/' . $sl),
        'model' => $popup->toArray(), // Pass model as array so hidden fields are respected
        'language_name' => 'g',
        'data' =>  ['model' => $popup, 'sl' => $sl] // Pass model as collection for field processing
      ]);

      return view('leadpopups::popup', compact('sl', 'popup', 'form', 'title'));
    }
  }

  /**
   * Edit popup post
   */

  public function postEditPopup($sl) {
    $msg = trans('g.form_success');
    $qs = Core\Secure::string2array($sl);
    $id = $qs['popup_id'];

    if (is_numeric($id)) {
      // Form
      $form = $this->form('Modules\LeadPopups\Forms\Popup');

      $popup = \Modules\LeadPopups\Models\Popup::findOrFail($id);

      // Validate form
      if (! $form->isValid()) {
        return redirect()->back()->withErrors($form->getErrors())->withInput();
      }

      $form_fields = $form->getFieldValues();

      $fields = [];
      foreach ($form_fields as $key => $val) {
        if (starts_with($key, 'additional_fields_')) {
          $key = str_replace('additional_fields_', '', $key);
          $key = 'additional_fields->' . $key . '';
        }

        if ($key == 'form_fields') {
          $val = json_decode($val);
        }

        $fields[$key] = $val;
      }

      $popup->fill($fields);
      $popup->save();
    }

    return redirect('popups')->with('success', $msg);
  }

  /**
   * Delete (selected) popups
   */

  public function postDeletePopups() {
    $ids = request()->get('ids');

    if (is_array($ids)) {
      foreach ($ids as $id) {
        // Filter assigned records
        $query = \Modules\LeadPopups\Models\Popup::select(['id'])->find($id);

        if ($query !== null) {
          // Delete
          $query->delete();
        }
      }
    }

    return response()->json(true);
  }

}
