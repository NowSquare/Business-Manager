@extends('../../layouts.app')

@section('page_title', $title . ' - ' . config('system.name'))

@section('page_head')
  <link rel="stylesheet" href="{{ url('assets/css/wysiwyg.css?' . config('system.client_side_timestamp')) }}">
  <script src="{{ url('assets/js/wysiwyg.js?' . config('system.client_side_timestamp')) }}"></script>

<style type="text/css">
.btn-custom-primary, .btn-custom-primary:hover, .btn-custom-primary.active, .btn-custom-primary:active, .btn-custom-primary:visited {
  background-color: #58bd24;
  color: #fff;
  font-size: 21px;
}

.btn-custom-secondary, .btn-custom-secondary:hover, .btn-custom-secondary.active, .btn-custom-secondary:active, .btn-custom-secondary:visited {
  background-color: #146eff;
  color: #fff;
}

.btn-custom-primary:hover, .btn-custom-primary:active, .btn-custom-primary.active,
.btn-custom-secondary:hover, .btn-custom-secondary:active, .btn-custom-secondary.active {
  filter: brightness(85%);
  transition: all 0.5s ease;
}

.btn-custom-primary:focus, .btn-custom-primary.focus,
.btn-custom-secondary:focus, .btn-custom-secondary.focus {
  transition: all 0.2s ease;
  box-shadow: 0 0 0 2px rgba(0,0,0,0.2) !important;
}
</style>
@stop

@section('content')
  <div class="my-3 my-md-5">
    <div class="container">
      <div class="row">
        <div class="col-12">
<?php if (! $pusher_configured) { ?>
          <div class="alert alert-danger rounded-0">
            {!! trans('coupons::g.pusher_config_required') !!}
          </div>
<?php } ?>

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

          <div class="row">
            <div class="col-md-7">

              {!! form_start($form) !!}

                <div class="card">
                  <div class="card-header">
                    <h3 class="card-title">{{ $title }}</h3>
                  </div>

                  <div class="card-body p-0">

                    <ul class="nav nav-tabs mx-0" role="tablist">
                      <li class="nav-item pl-5">
                        <a class="nav-link" id="general-tab" data-toggle="tab" href="#general" role="tab" aria-selected="false">{{ trans('g.general') }}</a>
                      </li>
                      <li class="nav-item">
                        <a class="nav-link" id="design-tab" data-toggle="tab" href="#design" role="tab" aria-selected="false">{{ trans('coupons::g.design') }}</a>
                      </li>
                      <li class="nav-item">
                        <a class="nav-link" id="form-tab" data-toggle="tab" href="#form" role="tab" aria-selected="false">{{ trans('coupons::g.form') }}</a>
                      </li>
                    </ul>

                    <div class="tab-content">
                      <div class="tab-pane px-5 pt-5 pb-3" id="general" role="tabpanel" aria-labelledby="general-tab">

                        <div class="row">
                          <div class="col-md-12">
                            <div class="row">
                              <div class="col-8">
                                {!! form_row($form->name) !!}
                              </div>
                              <div class="col-4">
                                <div class="mt-5 pt-3">
                                  {!! form_row($form->active) !!}
                                </div>
                              </div>
                            </div>

                            {!! form_row($form->slug) !!}

                            {!! form_row($form->redemption_code) !!}

                            <div class="mb-5">
                              {!! form_row($form->content) !!}
                            </div>

                            {!! form_row($form->location) !!}

                            {!! form_row($form->expiration_date) !!}

                            <div class="row">
                              <div class="col">
                                {!! form_row($form->phone) !!}
                              </div>
                              <div class="col">
                                {!! form_row($form->website) !!}
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>

                      <div class="tab-pane px-5 pt-5 pb-3" id="design" role="tabpanel" aria-labelledby="design-tab">
                        <div class="row">
                          <div class="col-md-12">
                            <div class="row">
                              <div class="col-lg-6">
                                {!! form_row($form->image) !!}
                              </div>
                              <div class="col-lg-6">
                                {!! form_row($form->favicon) !!}
                              </div>
                            </div>
                            <div class="row">
                              <div class="col">
                                {!! form_row($form->additional_fields_primary_bg_color) !!}
                              </div>
                              <div class="col">
                                {!! form_row($form->additional_fields_primary_text_color) !!}
                              </div>
                            </div>
                            <div class="row">
                              <div class="col">
                                {!! form_row($form->additional_fields_secondary_bg_color) !!}
                              </div>
                              <div class="col">
                                {!! form_row($form->additional_fields_secondary_text_color) !!}
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>

                      <div class="tab-pane px-5 pt-5 pb-3" id="form" role="tabpanel" aria-labelledby="form-tab">
                        <div class="row">
                          <div class="col-12">
                            <label for="" class="control-label">{{ trans('coupons::g.form_info') }}</label>
                            {!! form_row($form->form_fields) !!}
                          </div>
                        </div>
                        <div class="bg-light border p-5">
                          <div class="row">
                            <div class="col-6">
                              <strong>{{ trans('coupons::g.available_fields') }}</strong>
<?php
$available_fields = ['salutation', 'name', 'phone', 'website', 'street', 'postal_code', 'city', 'state', 'country'];

if (isset($coupon->form_fields)) {
  $form_fields = json_decode($coupon->form_fields);

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
                                    <label for="{{ $field }}_required" class="custom-control-label">{{ trans('coupons::g.required') }}</label>    
                                  </div>
                                </li>
<?php
}
?>
                              </ul>
                            </div>

                            <div class="col-6">
                              <strong>{{ trans('coupons::g.form') }}</strong>

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
                                    <label for="{{ $field->field }}_required" class="custom-control-label">{{ trans('coupons::g.required') }}</label>    
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
            <div class="col-md-5">
              <div class="card">
                <div class="card-status bg-dark"></div>
                <div class="card-header">
                  <h3 class="card-title">{{ trans('coupons::g.preview') }}</h3>
                  <div class="card-options">
  <?php if ($coupon->favicon_file_name !== null) { ?>
                  <img id="imagePreviewFavicon" src="{{ $coupon->favicon->url('iphone_ios7') }}" style="width: 32px; height: 32px;">
  <?php } else { ?>
                  <img id="imagePreviewFavicon" src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7" class="d-none" style="width: 32px; height: 32px;">
  <?php } ?>
                  </div>
                </div>

                <div class="card-body pb-0">

                  <h3 class="mt-0" id="preview_name">{{ trans('coupons::g.title_placeholder') }}</h3>
  <?php if ($coupon->image_file_name !== null) { ?>
                  <img id="imagePreviewImage" src="{{ $coupon->image->url('large') }}" class="img-fluid mb-4 mdl-shadow--2dp" style="min-width: 100%">
  <?php } else { ?>
                  <img id="imagePreviewImage" src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7" class="img-fluid mb-4 mdl-shadow--2dp d-none" style="min-width: 100%">
  <?php } ?>
                  <div id="preview_content">{{ trans('coupons::g.details_placeholder') }}</div>

                  <div class="font-weight-bold my-3 d-none" id="preview_location"><i class="material-icons" style="font-size: 14px; position: relative; top: 1px;">location_on</i> <a href="#" class="text-inherit" target="_blank"></a></div>
                  <small class="text-muted" id="preview_expires"></small>

                  <div class="row mt-4">
                    <div class="col-12">
                      <a href="javascript:void(0);" class="btn btn-custom-primary btn-lg text-truncate rounded-0 btn-block mb-4 primaryColor"><i class="material-icons" style="position: relative; top: 1px; font-size: 20px">redeem</i></i> <span id="preview_primaryBtnText">{{ trans('coupons::g.redeem') }}</span></a>
                    </div>
                  </div>

                  <div class="row">
                    <div class="col">
                      <a href="javascript:void(0);" class="btn btn-custom-secondary btn-lg text-truncate rounded-0 btn-block mb-4 secondaryColor"><i class="material-icons">phone</i> <span id="preview_callBtnText">{{ trans('coupons::g.call') }}</span></a>
                    </div>
                    <div class="col">
                      <a href="javascript:void(0);" class="btn btn-custom-secondary btn-lg text-truncate rounded-0 btn-block mb-4 secondaryColor"><i class="material-icons">info</i> <span id="preview_moreBtnText">{{ trans('coupons::g.more') }}</span></a>
                    </div>
                    <div class="col">
                      <div class="dropdown btn-block">
                        <button class="btn btn-custom-secondary btn-lg text-truncate rounded-0 btn-block mb-4 dropdown-toggle secondaryColor" type="button" id="dropdownShare" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                          <span id="preview_shareBtnText">{{ trans('coupons::g.share') }}</span>
                        </button>
                        <div class="dropdown-menu rounded-0 dropdown-menu-right btn-block" aria-labelledby="dropdownShare">
                          <a class="dropdown-item" href="javascript:void(0);"><span style="width:30px;float:left; text-align: left"><i class="fab fa-whatsapp" aria-hidden="true"></i></span> WhatsApp</a>
                          <a class="dropdown-item" href="javascript:void(0);"><span style="width:30px;float:left; text-align: left"><i class="fab fa-facebook" aria-hidden="true"></i></span> Facebook</a>
                          <a class="dropdown-item" href="javascript:void(0);"><span style="width:30px;float:left; text-align: left"><i class="fab fa-google" aria-hidden="true"></i></span> Google+</a>
                          <a class="dropdown-item" href="javascript:void(0);"><span style="width:30px;float:left; text-align: left"><i class="fab fa-twitter" aria-hidden="true"></i></span> Twitter</a>
                          <a class="dropdown-item" href="javascript:void(0);"><span style="width:30px;float:left; text-align: left"><i class="far fa-envelope" aria-hidden="true"></i></span> Mail</a>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
@stop

@section('page_bottom')
<script>
var inputs = document.querySelectorAll('input[pattern]');

for (var i = 0; i < inputs.length; i++) {
  var input = inputs[i];
  var state = {
    value: input.value,
    start: input.selectionStart,
    end: input.selectionEnd,
    pattern: RegExp('' + input.pattern + '')
  };
  
  input.addEventListener('input', function(event) {
    if (state.pattern.test(input.value)) {
      state.value = input.value;
    } else {
      input.value = state.value;
      input.setSelectionRange(state.start, state.end);
    }
  });

  input.addEventListener('keydown', function(event) {
    state.start = input.selectionStart;
    state.end = input.selectionEnd;
  });
}

$(function(){
  $('#additional_fields_primary_bg_color,#additional_fields_primary_text_color,#additional_fields_secondary_bg_color,#additional_fields_secondary_text_color,#expiration_date_time,[name=expiration_date_field]').on('change', renderPreview);

  $('#name,#content,#location,[name=expiration_date_field]').on('keyup', renderPreview);

  tinyMCE.get('content').on('NodeChange', function(e) {
    renderPreview();
  });

  renderPreview();
});

function renderPreview() {
  var name = $('#name').val();
  if (name != '') {
    $('#preview_name').html(name);
  } else {
    $('#preview_name').html('{{ trans('coupons::g.title_placeholder') }}');
  }

  var content = tinyMCE.get('content').getContent();

  if (content != '') {
    $('#preview_content').html(content);
  } else {
    $('#preview_content').html('{{ trans('coupons::details_placeholder') }}');
  }

  var location = $('#location').val();
  if (location == '') {
    $('#preview_location').addClass('d-none');
  } else {
    var url = 'https://www.google.com/maps/search/?api=1&query=' + encodeURIComponent(location);
    $('#preview_location>a').attr('href', url);
    $('#preview_location>a').text(location);
    $('#preview_location').removeClass('d-none');
  }

  var additional_fields_primary_bg_color = $('#additional_fields_primary_bg_color').val();
  var additional_fields_primary_text_color = $('#additional_fields_primary_text_color').val();
  $('.btn-custom-primary').css({
    'background-color': additional_fields_primary_bg_color,
    'color': additional_fields_primary_text_color
  });

  var additional_fields_secondary_bg_color = $('#additional_fields_secondary_bg_color').val();
  var additional_fields_secondary_text_color = $('#additional_fields_secondary_text_color').val();
  $('.btn-custom-secondary').css({
    'background-color': additional_fields_secondary_bg_color,
    'color': additional_fields_secondary_text_color
  });

  var expiration_date = $('[name=expiration_date_field]').val();
  var expiration_time = $('#expiration_date_time option:selected').text();

  if (expiration_date) {

    if (expiration_time != '') {
      $('#preview_expires').html('{{ trans('coupons::g.expires') }} ' + expiration_date + ' ' + expiration_time);
    } else {
      $('#preview_expires').html('{{ trans('coupons::g.expires') }} ' + expiration_date + '');
    }
  } else {
    $('#preview_expires').html('');
  }
}
</script>
@stop