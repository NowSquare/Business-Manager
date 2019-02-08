@extends('../../layouts.app')

@section('page_title', trans('g.import_people') . ' - ' . config('system.name'))

@section('page_head')
  <link rel="stylesheet" href="{{ url('assets/css/spreadsheet.css?' . config('system.client_side_timestamp')) }}">
  <script src="{{ url('assets/js/spreadsheet.js?' . config('system.client_side_timestamp')) }}"></script>

@include('layouts.modules.elfinder-init')

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

          @if(session()->has('warning'))
          <div class="alert alert-warning rounded-0">
            {!! session()->get('warning') !!}
          </div>
          @endif

          <div class="card shadow-lg">
            <div class="card-header">
              <h3 class="card-title">{{ trans('g.import_people') }}</h3>
              <div class="card-options">
                <button class="btn btn-secondary ml-2" data-toggle="modal" data-target="#importHelp"><i class="fe fe-help-circle"></i> {{ trans('g.help') }}</button>
                <button class="btn btn-success ml-2 selectFile" data-callback="selectExcelCb" data-mimes-title=".csv, .xls, xlsx" data-mimes='["text/csv", "text/x-csv", "application/vnd.ms-excel", "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"]'>{{ trans('g.select_excel_file_to_import') }}</button>
              </div>
            </div>
            <div class="card-body p-0">

              <div id="sheet"></div>

            </div>
            <div class="card-footer text-right">
              <a href="{{ url('users') }}" class="btn btn-secondary">{{ trans('g.back') }}</a>
              <button type="button" class="btn btn-primary" id="importSheet" disabled>{{ trans('g.import') }}</button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

<div class="modal fade" id="importHelp" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content border-0 rounded-0 shadow-lg">
      <div class="modal-header">
        <h5 class="modal-title">{{ trans('g.import_people') }}</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        </button>
      </div>
      <div class="modal-body">
        <p>To import data, select an Excel file with the data to be imported in the first tab. The contents of the sheet will be shown on this page before they are imported, so you can make any changes you like.</p>
        <p><i class="fas fa-file-pdf mr-1"></i> <a href="{{ url('users/import/download-example') }}">Download example Excel file</a></p>
        <h3>Unique email addresses</h3>
        <p>Email addresses are unique in the system. If the email column of a row contains an email address that already exists, the row will be skipped.</p>
        <h3>Required fields</h3>
        <p>If one or more required fields are not filled in for a row, this row will be skipped:</p>
        <ul>
          <li>Full name</li>
          <li>Email address</li>
        </ul>

        <h3>User role</h3>
        <p>The user role can be either a number, or a name:</p>
        <ul>
<?php foreach (\App\Role::all() as $role) { ?>
          <li>{{ $role->id }} or {{ $role->name }}</li>
<?php } ?>
        </ul>
        <p>If the role column is empty or can't be matched to an existing role, it is set to {{ \App\Role::find(3)->name }} by default.</p>

        <h3>Country</h3>
        <p>A country must be the two-letter country code according to the <a href="https://www.nationsonline.org/oneworld/country_code_list.htm" target="_blank">ISO 3166-1 alpha-2 codes</a>.</p>
        
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

@stop

@section('page_bottom')
<script>

function rowValidationRenderer(instance, td, row, col, prop, value, cellProperties) {
  Handsontable.renderers.TextRenderer.apply(this, arguments);

  if (col == 0 || col == 1) {
    if (!value || value === '') {
      td.style.background = '#ffcdd2';
    } else {
      td.style.background = '';
    }
  }

  if (col == 1) {
    var regex = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;
    var isEmail = regex.test(value);

    if (!value || value === '' || ! isEmail) {
      td.style.color = '#d50000';
    } else {
      td.style.color = '#1b5e20';
    }
  }
}

Handsontable.renderers.registerRenderer('rowValidationRenderer', rowValidationRenderer);

var container = document.getElementById('sheet');
var hot = new Handsontable(container, {
  data: [[
<?php foreach ($columns as $column) { ?>
    "",
<?php } ?>
  ]],
  rowHeaders: true,
  contextMenu: true,
  manualColumnResize: true,
  stretchH: 'all',
  width: '100%',
  height: 320,
  afterChange: function (change, source) {
    checkIfData(this);
  },
  colHeaders: [
<?php foreach ($columns as $column) { ?>
    "{{ $column['label'] }}",
<?php } ?>
  ],
  columns: [
<?php foreach ($columns as $column) { ?>
    {},
<?php } ?>
  ],
  afterSelection: function (row, col, row2, col2) {
    var meta = this.getCellMeta(row2, col2);

    if (meta.readOnly) {
      this.updateSettings({fillHandle: false});
    }
    else {
      this.updateSettings({fillHandle: true});
    }
  },
  cells: function (row, col) {
    var cellProperties = {};
    var data = this.instance.getData();

    cellProperties.renderer = "rowValidationRenderer";

    return cellProperties;
  }
});

function selectExcelCb(file) {
  $('#page_loader').show();
  var jqxhr = $.ajax({
    url: "{{ url('users/import/parse-excel') }}",
    data: {file: file, _token: '<?= csrf_token() ?>'},
    method: 'POST'
  })
  .done(function(data) {
    if(typeof data.msg !== 'undefined') {
      Swal({ imageUrl: "{{ url('assets/img/icons/fe/x-circle.svg') }}", imageWidth: 48, title: "{!! trans('g.operation_failed') !!}", text: data.msg });
    } else {
      hot.loadData(data);
    }
  })
  .fail(function() {
    Swal({ imageUrl: "{{ url('assets/img/icons/fe/frown.svg') }}", imageWidth: 48, title: "{!! trans('g.unkown_error') !!}", text: "{!! trans('g.unkown_error_msg') !!}" });
  })
  .always(function() {
    $('#page_loader').hide();
  });
}

$(function() {
  
  $('#importSheet').on('click', function() {
    $('#page_loader').show();
    var data = hot.getData();

    var jqxhr = $.ajax({
      url: "{{ url('users/import/data') }}",
      data: {data: data, _token: '<?= csrf_token() ?>'},
      method: 'POST'
    })
    .done(function(data) {
      var icon = (typeof data.icon === 'undefined') ? "{{ url('assets/img/icons/fe/x-circle.svg') }}" : data.icon;
      var title = (typeof data.title === 'undefined') ? "{!! trans('g.operation_failed') !!}" : data.title;

      if(typeof data.msg !== 'undefined') {
        Swal({ imageUrl: icon, imageWidth: 48, title: title, text: data.msg });
      }
    })
    .fail(function() {
      Swal({ imageUrl: "{{ url('assets/img/icons/fe/frown.svg') }}", imageWidth: 48, title: "{!! trans('g.unkown_error') !!}", text: "{!! trans('g.unkown_error_msg') !!}" });
    })
    .always(function() {
      $('#page_loader').hide();
    });
  });

  fitSheet();

  $(window).resize(fitSheet);

  function fitSheet() {
    hot.updateSettings({
      height: parseInt($(window).height()) - 390
      /*height: parseInt($('.ht_master .wtHider').height()) + 6*/
    });
  }
});

function checkIfData(hot) {
  var data = hot.getData();
  var data_found = false;
  for (var i in data) {
    var row = data[i];
    for (var j in row) {
      var cell = row[j];
      if (cell != '') {
        data_found = true;
        break;
      }
    }
  }
  if (data_found) {
    $('#importSheet').prop('disabled', null);
  } else {
    $('#importSheet').prop('disabled', 1);
  }
}
</script>
@stop