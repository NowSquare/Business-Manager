@extends('../../layouts.app')

@section('page_title', trans('g.settings') . ' - ' . config('system.name'))

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

          @if ($errors->any())
          <div class="alert alert-danger rounded-0">
            {!! trans('g.form_error') !!}
          </div>
          @endif

          {!! form_start($form) !!}

          <div class="card">
            <div class="card-header">
              <h3 class="card-title">{{ trans('g.system_settings') }}</h3>
            </div>

            <div class="card-body p-0">

              <ul class="nav nav-tabs mx-0" role="tablist">
                <li class="nav-item pl-5">
                  <a class="nav-link" id="general-tab" data-toggle="tab" href="#general" role="tab" aria-selected="false">{{ trans('g.general') }}</a>
                </li>
                <li class="nav-item pl-5">
                  <a class="nav-link" id="tax-tab" data-toggle="tab" href="#tax" role="tab" aria-selected="false">{{ trans('g.tax_rates') }}</a>
                </li>
                <li class="nav-item">
                  <a class="nav-link" id="update-tab" data-toggle="tab" href="#update" role="tab" aria-selected="false">{{ trans('g.update') }}</a>
                </li>
              </ul>

              <div class="tab-content">
                <div class="tab-pane px-5 pt-5 pb-3" id="general" role="tabpanel" aria-labelledby="general-tab">
                  <div class="row">
                    <div class="col-md-6 col-lg-5">
                      {!! form_until($form, 'system_icon') !!}
                    </div>
                    <div class="col-md-6 col-lg-5 offset-lg-1">
                      {!! form_until($form, 'system_signup') !!}
                    </div>
                  </div>
                </div>
                <div class="tab-pane p-0 py-3" id="tax" role="tabpanel" aria-labelledby="tax-tab">
                  <div class="row">
                    <div class="col-md-12">

                      <table class="table table-striped table-hover table-borderless" id="datatable">
                        <thead class="thead-dark">
                          <tr>
                            <th>{{ trans('g.rate') }}</th>
                            <th></th>
                          </tr>
                        </thead>
                        <tbody>

                        </tbody>
                      </table>

                    </div>
                  </div>
                </div>
                <div class="tab-pane px-5 pt-5 pb-3" id="update" role="tabpanel" aria-labelledby="update-tab">
                  <div class="row">
                    <div class="col-md-8">
                      <h3>{{ trans('g.system') }}</h3>
                      <h4>{{ trans('g.current_version', ['version' => $version]) }}</h4>
<?php
$modules = \Module::getOrdered();
if (count($modules) > 0) {
?>
                      <h3 class="mt-5">{{ trans('g.add_ons') }}</h3>
<?php
  foreach ($modules as $module) {
    $header_menu_name = config($module->alias . '.header_menu_name');
    $header_menu_icon = config($module->alias . '.header_menu_icon');

    if ($header_menu_name !== null && $header_menu_icon !== null) {
      // Version
      $file = base_path('Modules/' . $module->getName() . '/version');
      if (\File::exists($file)) {
        $version = \File::get($file);
      } else {
        $version = '?';
      }
?>
                      <h4><i class="material-icons" style="position: relative; top: 6px">{{ $header_menu_icon }}</i> {{ $header_menu_name }} {{ $version }}</h4>
<?php
    }
  }
}
?>
                      <p class="text-muted my-5">{{ trans('g.update_text') }}</p>
                      <button type="button" id="run_migrations" class="btn btn-success btn-lg mb-3">{{ trans('g.run_migrations') }}</button>
                    </div>
                  </div>
                </div>
              </div>

            </div>

            <div class="card-footer text-right">
                {!! form_rest($form) !!}
            </div>
          </div>

          {!! form_end($form) !!}

        </div>

      </div>
    </div>
  </div>

@stop

@section('page_bottom')

<script>
var datatable;

$(function() {
  datatable = $('#datatable').DataTable({
    dom:  "<'row'<'col-7 col-md-5'<'float-left ml-2'l>><'col-5 col-md-7 text-right'<'mr-3'B>>>" +
        "<'row'<'col-sm-12'tr>>" +
        "<'row'<'col-sm-12 col-md-5'<'ml-3 text-muted'i>><'col-sm-12 col-md-7'<'mr-3 mt-2'p>>>",
    ajax: "{{ url('settings/tax-rates/json') }}",
    stateSaveCallback: function(settings,data) {
      localStorage.setItem('tax_rates_{{ auth()->user()->id }}_' + settings.sInstance, JSON.stringify(data));
    },
    stateLoadCallback: function(settings) {
      return JSON.parse(localStorage.getItem( 'tax_rates_{{ auth()->user()->id }}_' + settings.sInstance ));
    },
    buttons: [
      {
        text: "<i class=\"material-icons\" style=\"position:relative;top:1px\">add</i><span class=\"d-none d-md-inline\"> {{ trans('g.tax_rate') }}</span>",
        attr: {
            title : '{{ trans('g.create_tax_rate') }}',
            'data-toggle' : 'tooltip'
        },
        className: 'btn btn-secondary rounded-0',
        action: function ( e, dt, node, config ) {
          addRecord();
        }
      }
    ],
    columns: [
      {
        data: "rate",
        sortable: true
      },
      {
        data: "sl",
        sortable: false,
        width: 18
      }
    ],
    columnDefs: [
      {
        className: 'align-middle',
        targets: [0]
      },
      {
        className: 'align-middle',
        render: function (data, type, row) {
          var ret = '';
          ret += '<a href="javascript:deleteRecords([' + row.id + '])" class="icon"><i class="fe fe-trash"></i></a>';
          return ret;
        },
        targets: 1
      },
    ],
    order: [[0, 'asc']],
    drawCallback: function () {
      $('[data-toggle="tooltip"]').tooltip({
        boundary: 'window',
        trigger: 'hover'
      });
    }
  });

  $('.dataTables_length .custom-select-sm').removeClass('custom-select-sm form-control-sm');
});

function addRecord() {
  Swal({
    title: "{!! trans('g.create_tax_rate') !!}",
    input: 'select',
    imageUrl: "{{ url('assets/img/icons/fe/plus-circle.svg') }}",
    imageWidth: 48,
    inputOptions: {
<?php for($rate = 0; $rate <= 3000; $rate += 25) { ?>
      '{{ $rate }}': '{{ number_format($rate / 100, auth()->user()->getDecimals(), auth()->user()->getDecimalSep(), auth()->user()->getThousandsSep()) }}%',
<?php } ?>
    },
    inputPlaceholder: "{!! trans('g.select_option') !!}",
    inputClass: 'custom-select form-control',
    showCancelButton: true,
    confirmButtonColor: '#3085d6',
    cancelButtonColor: '#d33',
    confirmButtonText: "{!! trans('g.save') !!}",
    inputValidator: (value) => {
    }
  }).then((result) => {
    if (result.value) {
      var jqxhr = $.ajax({
        url: "{{ url('settings/tax-rates/create') }}",
        data: {rate: result.value, _token: '<?= csrf_token() ?>'},
        method: 'POST'
      })
      .done(function(data) {
        if(data === true) {
          datatable.ajax.reload();
        } else if(typeof data.msg !== 'undefined') {
          Swal({ imageUrl: "{{ url('assets/img/icons/fe/x-circle.svg') }}", imageWidth: 48, title: "{!! trans('g.operation_failed') !!}", text: data.msg });
        }
      })
      .fail(function() {
        console.log('error');
      });
    }
  })
}

function deleteRecords(ids) {
  Swal({
    title: "{!! trans('g.are_you_sure') !!}",
    text: "{!! trans('g.confirm_delete') !!}",
    imageUrl: "{{ url('assets/img/icons/fe/trash-2.svg') }}",
    imageWidth: 48,
    showCancelButton: true,
    confirmButtonColor: '#3085d6',
    cancelButtonColor: '#d33',
    confirmButtonText: "{!! trans('g.yes_delete') !!}"
  }).then((result) => {
    if (result.value) {
      var jqxhr = $.ajax({
        url: "{{ url('settings/tax-rates/delete') }}",
        data: {ids: ids, _token: '<?= csrf_token() ?>'},
        method: 'POST'
      })
      .done(function(data) {
        if(data === true) {
          datatable.ajax.reload();
        } else if(typeof data.msg !== 'undefined') {
          Swal({ imageUrl: "{{ url('assets/img/icons/fe/x-circle.svg') }}", imageWidth: 48, title: "{!! trans('g.operation_failed') !!}", text: data.msg });
        }
      })
      .fail(function() {
        console.log('error');
      });
    }
  })
}

$(function() {
  $('#run_migrations').on('click', function() {
    var $btn = $(this);
    $btn.prop('disabled', 1);
    $btn.text("{{ trans('g.running_migrations') }}");

    var jqxhr = $.ajax({
      url: "{{ url('settings/run-migrations') }}",
      data: {_token: '<?= csrf_token() ?>'},
      method: 'POST'
    })
    .done(function(data) {
      $btn.text("{{ trans('g.ready') }}");
    })
    .fail(function() {
      $btn.prop('disabled', null);
      alert('An error occurred. Please reload this page.');
    });

  });
});
</script>
@stop