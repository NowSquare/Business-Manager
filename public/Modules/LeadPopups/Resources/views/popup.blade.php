@extends('../../layouts.app')

@section('page_title', $title . ' - ' . config('system.name'))

@section('page_head')
  <link rel="stylesheet" href="{{ url('modules/popups/assets/style.css?' . config('system.client_side_timestamp')) }}">
  <script src="{{ url('modules/popups/assets/scripts.js?' . config('system.client_side_timestamp')) }}"></script>

  <link rel="stylesheet" href="{{ url('assets/css/wysiwyg.css?' . config('system.client_side_timestamp')) }}">
  <script src="{{ url('assets/js/wysiwyg.js?' . config('system.client_side_timestamp')) }}"></script>

@stop

@section('content')
  <div class="my-3 my-md-5">
    <div class="container">
      <div class="row">
        <div class="col-12">

          @if(session()->has('success'))
          <div class="alert alert-success rounded-0">
            {!! session()->get('success') !!}
          </div>
          @endif

          @if ($errors->any())
          <div class="alert alert-danger rounded-0">
            {!! trans('g.form_error') !!}
          </div>
          @endif

          {!! form_start($form) !!}

          <div class="card">
            <div class="card-header">
              <h3 class="card-title">{{ $title }}</h3>
              <div class="card-options">
                <button type="button" class="btn btn-success mr-2" id="showPopup" data-toggle="tooltip" title="{{ trans('leadpopups::g.test_title') }}">{{ trans('leadpopups::g.show_popup') }}</button>
              </div>
            </div>

            <div class="card-body p-0">

              <ul class="nav nav-tabs mx-0" role="tablist">
                <li class="nav-item pl-5">
                  <a class="nav-link" id="general-tab" data-toggle="tab" href="#general" role="tab" aria-selected="false">{{ trans('g.general') }}</a>
                </li>
                <li class="nav-item">
                  <a class="nav-link" id="form-tab" data-toggle="tab" href="#form" role="tab" aria-selected="false">{{ trans('leadpopups::g.form') }}</a>
                </li>
                <li class="nav-item">
                  <a class="nav-link" id="conditions-tab" data-toggle="tab" href="#conditions" role="tab" aria-selected="false">{{ trans('leadpopups::g.conditions') }}</a>
                </li>
                <li class="nav-item">
                  <a class="nav-link" id="design-tab" data-toggle="tab" href="#design" role="tab" aria-selected="false">{{ trans('leadpopups::g.design_layout') }}</a>
                </li>
              </ul>

              <div class="tab-content">
                <div class="tab-pane px-5 pt-5 pb-3" id="general" role="tabpanel" aria-labelledby="general-tab">

                  <div class="row">
                    <div class="col-md-6 col-lg-6">
                      {!! form_row($form->name) !!}
                    </div>
                    <div class="col-md-6 col-lg-6">
                      <div class="mt-5 pt-3">
                        {!! form_row($form->active) !!}
                      </div>
                    </div>
                  </div>

                  <div class="row">
                    <div class="col-12 my-4">
                      {!! form_row($form->content) !!}
                    </div>
                  </div>

                </div>

                <div class="tab-pane px-5 pt-5 pb-3" id="form" role="tabpanel" aria-labelledby="form-tab">

                  <div class="row">
                    <div class="col-12">
                      <label for="" class="control-label">{{ trans('leadpopups::g.form_info') }}</label>
                      {!! form_row($form->form_fields) !!}
                    </div>
                  </div>

                  <div class="bg-light border p-5">
                    <div class="row">
                      <div class="col-6">
                        <strong>{{ trans('leadpopups::g.available_fields') }}</strong>
<?php
$available_fields = ['salutation', 'name', 'phone', 'website', 'street', 'postal_code', 'city', 'state', 'country'];

if (isset($popup->form_fields)) {
  $form_fields = json_decode($popup->form_fields);

  foreach ($form_fields as $field) {
    if (($key = array_search($field->field, $available_fields)) !== false) {
      unset($available_fields[$key]);
    }
  }
} else {
  $form_fields = (object)[
    (object)['field' => 'email', 'required' => 1]
  ];
}

?>
                        <ul id="sortable1" class="list-group mt-4 connectedSortable">
<?php
foreach ($available_fields as $field) {
?>
                          <li class="list-group-item" data-field="{{ $field }}">
                            <i class="material-icons handle">reorder</i> {{ trans('g.' . $field) }}
                            <div class="custom-control custom-checkbox float-right">
                              <input class="custom-control-input" id="{{ $field }}_required" name="field_required[]" type="checkbox" value="1">
                              <label for="{{ $field }}_required" class="custom-control-label">{{ trans('leadpopups::g.required') }}</label>    
                            </div>
                          </li>
<?php
}
?>
                        </ul>

                      </div>

                      <div class="col-6">
                        <strong>{{ trans('leadpopups::g.form') }}</strong>

                        <ul id="sortable2" class="list-group mt-4 connectedSortable">
<?php
foreach ($form_fields as $field) {
  $name = ($field->field == 'email') ? trans('g.email_address') : trans('g.' . $field->field);
  $mandatory = ($field->field == 'email') ? true : false;
  $required = ($field->required == 1) ? true : false;
?>
                          <li class="list-group-item<?php if ($mandatory) echo ' exclude'; ?>" data-field="{{ $field->field }}">
                            <i class="material-icons handle">reorder</i> {{ $name }}
                            <div class="custom-control custom-checkbox float-right">
                              <input class="custom-control-input" id="{{ $field->field }}_required" name="field_required[]" type="checkbox" value="1"<?php if ($required) echo ' checked="checked"'; ?><?php if ($mandatory) echo ' disabled'; ?>>
                              <label for="{{ $field->field }}_required" class="custom-control-label">{{ trans('leadpopups::g.required') }}</label>    
                            </div>
                          </li>
<?php
}
?>
                        </ul>

                      </div>
                    </div>
                  </div>
<style type="text/css">
  .connectedSortable .handle {
    font-size: 18px;
    position: relative;
    top: 4px;
    margin-right: 5px;
  }
  .connectedSortable {
    min-height: 100px;
  }
  .connectedSortable li .handle {
    cursor: grab;
  }
  .connectedSortable li.unsortable .handle {
    cursor: not-allowed;
  }
  #sortable1 .custom-control {
    display: none;
  }

  .connectedSortable .ui-sortable-helper {
    background-color: #fff !important;
    box-shadow: 0 3px 6px rgba(0, 0, 0, 0.16), 0 3px 6px rgba(0, 0, 0, 0.23);
  }
  .connected-placeholder {
    list-style: none;
    background-color: #f5f5f5 !important;
    box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.12), inset 0 1px 2px rgba(0, 0, 0, 0.24) !important;
    height: 50px;
  }
</style>
<script>
$(function() {
  setFormFields();

  $('[name="field_required[]"]').on('change', setFormFields);

  $("#sortable1, #sortable2" ).sortable({
    items: "li.list-group-item:not(.unsortable)",
    handle: ".handle",
    placeholder: 'connected-placeholder',
    forcePlaceholderSize: true,
    connectWith: ".connectedSortable",
    start: function(event, ui) {
      if (ui.item.hasClass("exclude")) {
        $("#sortable2").sortable("option", "connectWith", false);
        $("#sortable2").sortable("refresh");
      }
    },
    stop: function( event, ui ) {
      if (ui.item.hasClass("exclude")) {
        $("#sortable2").sortable("option", "connectWith", "#sortable1");
        $("#sortable2").sortable("refresh");
      }

      setFormFields();
    }
  });

  function setFormFields() {
    var fields = [];
    $('#sortable2 li').each(function(index) {
      var field = $(this).attr('data-field');
      var required = $(this).find('[type=checkbox]').prop('checked');
      required = (required) ? 1 : 0;

      fields.push({
        field: field,
        required: required
      });

    });
    fields = JSON.stringify(fields);

    $('#form_fields').val(fields);
  }
});
</script>
                  <div class="row">
                    <div class="col-12 my-4">
                      {!! form_row($form->additional_fields_submit_button) !!}
                      {!! form_row($form->additional_fields_after_submit_message) !!}
                    </div>
                  </div>

                </div>

                <div class="tab-pane px-5 pt-5 pb-3" id="conditions" role="tabpanel" aria-labelledby="conditions-tab">

                  <div class="row">
                    <div class="col-md-6 col-lg-6">
                      {!! form_row($form->additional_fields_trigger) !!}
                    </div>
                    <div class="col-md-6 col-lg-6">

                      <div id="scroll_top">
                        {!! form_row($form->additional_fields_scrollTop) !!}
                      </div>
                    </div>
                  </div>

                  <div class="row mt-5">
                    <div class="col-md-6 col-lg-6">
                      {!! form_row($form->additional_fields_delay) !!}
                    </div>
                    <div class="col-md-6 col-lg-6">
                      {!! form_row($form->additional_fields_ignoreAfterCloses) !!}
                    </div>
                  </div>

                  <div class="row mt-5">
                    <div class="col-md-6 col-lg-6">
                      {!! form_row($form->hosts) !!}
                    </div>
                    <div class="col-md-6 col-lg-6">
                      {!! form_row($form->paths) !!}
                    </div>
                  </div>

                </div>

                <div class="tab-pane px-5 pt-5 pb-3" id="design" role="tabpanel" aria-labelledby="design-tab">

                  <div class="row">
                    <div class="col-md-3 col-lg-3">
                      {!! form_row($form->additional_fields_position) !!}
                    </div>
                    <div class="col-md-3 col-lg-3">
                      {!! form_row($form->additional_fields_width) !!}
                    </div>
                    <div class="col-md-3 col-lg-3">
                      {!! form_row($form->additional_fields_height) !!}
                    </div>
                    <div class="col-md-3 col-lg-3">
                      <div class="mt-5 pt-3">
                        {!! form_row($form->additional_fields_shadow) !!}
                      </div>
                    </div>
                  </div>

                  <div class="row">
                    <div class="col-md-3 col-lg-3">
                      {!! form_row($form->additional_fields_closeBtnColor) !!}
                      {!! form_row($form->additional_fields_closeBtnMargin) !!}
                    </div>

                    <div class="col-md-3 col-lg-3">
                      {!! form_row($form->additional_fields_backdropBgColor) !!}
                      {!! form_row($form->additional_fields_backdropVisible) !!}
                    </div>

                    <div class="col-md-3 col-lg-3">
                      {!! form_row($form->additional_fields_loaderColor) !!}
                      {!! form_row($form->additional_fields_showLoader) !!}
                    </div>
                    <div class="col-md-3 col-lg-3">


                    </div>
                  </div>

                </div>
              </div>

            </div>
            <div class="card-footer text-right">
                {!! form_row($form->back) !!}
                {!! form_row($form->submit) !!}
            </div>
          </div>

          </form>

        </div>
      </div>
    </div>
  </div>
@stop

@section('page_bottom')
<script>
$(function() {
  checkTrigger();
  $('#additional_fields_trigger').on('change', checkTrigger);

  $('#showPopup').on('click', function() {
    $(this).tooltip('hide');

    var content = tinyMCE.get('content').getContent();
    var submit_button = $('#additional_fields_submit_button').val();
    var after_submit_message = $('#additional_fields_after_submit_message').val();
    var form_fields = $('#form_fields').val();
    var shadow = $('#additional_fields_shadow').is(':checked');
    var contentClasses = (shadow) ? '-lm-shadow--8dp': '';
    var backdropVisible = $('#additional_fields_backdropVisible').is(':checked');
    var backdropBgColor = $('#additional_fields_backdropBgColor').val();
    var showLoader = $('#additional_fields_showLoader').is(':checked');
    var loaderColor = $('#additional_fields_loaderColor').val();
    var position = $('#additional_fields_position').val();
    var width = parseInt($('#additional_fields_width').val());
    var height = parseInt($('#additional_fields_height').val());
    var closeBtnColor = $('#additional_fields_closeBtnColor').val();
    var closeBtnMargin = $('#additional_fields_closeBtnMargin').val();

    var cfg = {
      locale: '{{ app()->getLocale() }}',
      content: content,
      submit_button: submit_button,
      after_submit_message: after_submit_message,
      form_fields: form_fields,
      backdropVisible: backdropVisible,
      backdropBgColor: backdropBgColor,
      showLoader: showLoader,
      loaderColor: loaderColor,
      contentPosition: position,
      contentWidth: width,
      contentHeight: height,
      contentClasses: contentClasses,
      closeBtnColor: closeBtnColor,
      closeBtnMargin: closeBtnMargin
    };

    $('#showPopup').attr('disabled', true);

    window.showLeadModal(cfg);

    setTimeout(function() {
      $('#showPopup').attr('disabled', null);
    }, 3000);

  });
});

function checkTrigger() {
  var trigger = $('#additional_fields_trigger').val();
  if (trigger == 'onscroll') {
    $('#scroll_top').show();
  } else {
    $('#scroll_top').hide();
  }
}
</script>
@stop