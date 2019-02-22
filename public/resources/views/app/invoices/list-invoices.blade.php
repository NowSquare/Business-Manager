@extends('../../layouts.app')

@section('page_title', trans('g.invoices') . ' - ' . config('system.name'))

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

          <div class="card shadow-lg">
            <div class="card-header">
              <h3 class="card-title">{{ trans('g.invoices') }}</h3>
            </div>
            <div class="card-body px-0 py-3">

              <table class="table table-striped table-hover table-borderless" id="datatable">
                <thead class="thead-dark">
                  <tr>
                    <th>
                      <div class="custom-control custom-checkbox" style="position: relative; top: -15px; left:10px">
                        <input type="checkbox" class="custom-control-input" id="all_rows" onClick="toggleRows(this)">
                        <label class="custom-control-label" for="all_rows"></label>
                      </div>
                    </th>
                    <th></th>
                    <th></th>
                    <th></th>
                  </tr>
                </thead>
                <tbody>

                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
@stop

@section('page_bottom')
<style type="text/css">
.swal2-popup .selectize-input,
.swal2-popup .selectize-dropdown-content{
  text-align: left;
}
.swal2-popup .swal2-actions {
  z-index: 0;
}
.dropdown-item.disabled {
  cursor: not-allowed;
}  
</style>
<script>
  var datatable;

  function toggleRows(source) {
    var checkboxes = document.getElementsByName("rows[]");
    for(var i in checkboxes) {
      checkboxes[i].checked = source.checked;
    }
  }

  function checkRowSelection() {
  $('.btn-delete-selected').attr('disabled', 1);
  var checkboxes = document.getElementsByName("rows[]");
  for(var i in checkboxes) {
    if (checkboxes[i].checked && typeof checkboxes[i].value !== 'undefined') {
      $('.btn-delete-selected').attr('disabled', null);
    }
  }
  
  datatable.rows().every( function ( rowIdx, tableLoop, rowLoop ) {
    var data = this.data();
    if ($('#' + data.DT_RowId).find('input[name="rows[]"]').prop('checked')) {
      $('#' + data.DT_RowId).addClass('table-active');
    } else {
      $('#' + data.DT_RowId).removeClass('table-active');
    }
  });
}

$(function() {
  $('#all_rows').on('click', checkRowSelection);

  datatable = $('#datatable').DataTable({
    ajax: "{{ url('invoices/json') }}",
    stateSaveCallback: function(settings,data) {
      localStorage.setItem('invoices_{{ auth()->user()->id }}_' + settings.sInstance, JSON.stringify(data));
    },
    stateLoadCallback: function(settings) {
      return JSON.parse(localStorage.getItem( 'invoices_{{ auth()->user()->id }}_' + settings.sInstance ));
    },
    buttons: [
      {
        extend: 'collection',
        fade: 0,
        text: "<i class=\"material-icons d-none d-md-inline\" style=\"position:relative;top:1px\">import_export</i> {{ trans('g.export') }} ",
        className: 'btn btn-secondary buttons-collection dropdown-toggle mr-2 rounded-0 btn-export',
        buttons: [
          {
            text: "Excel (.xlsx)",
            action: function ( e, dt, node, config ) { document.location = '{{ url('invoices/export/xlsx') }}'; }
          }, {
            text: "Excel (.xls)",
            action: function ( e, dt, node, config ) { document.location = '{{ url('invoices/export/xls') }}'; }
          }, {
            text: "Comma-separated (.csv)",
            action: function ( e, dt, node, config ) { document.location = '{{ url('invoices/export/csv') }}'; }
          }
        ]
      },
<?php if (auth()->user()->can('delete-invoice')) { ?>
      {
        text: "<i class=\"material-icons\" style=\"position:relative;top:1px\">delete_outline</i> {!! trans('g.delete_selected') !!}",
        className: 'btn btn-secondary btn-delete-selected mr-2 rounded-0 d-none d-md-block',
        action: function ( e, dt, node, config ) {

          var ids = [];
          var count = 0;
          var checkboxes = document.getElementsByName("rows[]");
          for(var i in checkboxes) {
            if (checkboxes[i].checked && typeof checkboxes[i].value !== 'undefined') {
              ids[count] = checkboxes[i].value;
              count++;
            }
          }

          deleteRecords(ids);
        }
      },
<?php } ?>
<?php if (auth()->user()->can('create-invoice')) { ?>
      {
        text: "<i class=\"material-icons\" style=\"position:relative;top:1px\">add</i><span class=\"d-none d-md-inline\"> {{ trans('g.invoice') }}</span>",
        attr: {
            title : '{{ trans('g.create_invoice') }}',
            'data-toggle' : 'tooltip'
        },
        className: 'btn btn-secondary rounded-0',
        action: function ( e, dt, node, config ) {
          addRecord();
        }
      }
<?php } ?>
    ],
    columns: [
      {
        data: "id",
        sortable: false,
        width: 25
      },
      {
        data: "status",
        sortable: false
      },
      {
        data: "total",
        sortable: false
      },
      {
        data: "sl",
        sortable: false,
        searchable: false,
        width: 18
      }
    ],
    columnDefs: [
      {
        className: 'd-none d-md-table-cell',
        render: function (data, type, row) {
          if (row.status_key == 'draft') {
            return '<div class="custom-control custom-checkbox" style="left:10px; top:-11px">' + 
                   ' <input type="checkbox" class="custom-control-input" id="rows_' + row.id + '" name="rows[]" value="' + row.id + '" onclick="checkRowSelection()">' + 
                   ' <label class="custom-control-label" for="rows_' + row.id + '"></label>' + 
                   '</div>';
          } else {
            return '';
          }

        },
        targets: 0
      },
      {
        className: 'align-middle',
        render: function (data, type, row) {
          var ret = '';
  
          ret += '<div><i class="material-icons" style="font-size:12px; position:relative; top: 1px">business</i> ' + row.client_name + '</div>';
          ret += '<div>';
          ret += '<span class="badge badge-secondary mr-2" style="background-color:' + row.status_color + '">' + row.status + '</span>';
          ret += '<span class="text-muted small">';
          ret += '{{ trans('g.invoice') }}: ' + row.reference + ' ';
          ret += '&bull; {{ trans('g.issue_date') }}: ' + row.issue_date + ' ';

          if (row.status_key != 'draft') {
            ret += '&bull; {{ trans('g.sent') }}: ' + row.sent_date + ' ';
          }

          if (row.status_key == 'overdue') {
            ret += '&bull; {{ trans('g.due') }}: ' + row.due_date + ' ';
          }

          if (row.status_key == 'partially_paid') {
            ret += '&bull; {{ trans('g.partially_paid') }}: ' + row.partially_paid_date + ' ';
          }

          if (row.status_key == 'paid') {
            ret += '&bull; {{ trans('g.paid') }}: ' + row.paid_date + ' ';
          }

          if (row.status_key == 'written_off') {
            ret += '&bull; {{ trans('g.written_off') }}: ' + row.written_off_date + ' ';
          }

          ret += '</span></div>';

          return ret;
        },
        targets: [1]
      },
      {
        className: 'align-middle text-right',
        render: function (data, type, row) {
          var ret = '';
  
          ret += '<div>' + formatCurrency(row.total) + '</div>';
          ret += '<div class="small">' + row.currency_code + '</div>';

          return ret;
        },
        targets: [2]
      },
      {
        className: 'align-middle',
        render: function (data, type, row) {
          var ret = '';

          ret += '<div class="item-action dropdown">';
          ret += '<a href="javascript:void(0)" data-toggle="dropdown" class="icon"><i class="fe fe-more-vertical"></i></a>';
          ret += '<div class="dropdown-menu dropdown-menu-right">';
<?php if (auth()->user()->can('edit-invoice')) { ?>
          if (row.status_key == 'draft') {
            ret += '  <a href="<?php echo url('invoices/edit/') ?>/' + data + '" class="dropdown-item"><i class="dropdown-icon fe fe-edit-2"></i> {{ trans('g.edit') }}</a>';
          } else {
            ret += '  <a href="javascript:resendInvoice(' + row.id + ');" class="dropdown-item"><i class="dropdown-icon fe fe-send"></i> {{ trans('g.resend') }}</a>';
          }
<?php } ?>
<?php if (auth()->user()->can('download-invoice')) { ?>
          ret += '  <a href="<?php echo url('invoices/pdf/') ?>/' + data + '" class="dropdown-item"><i class="dropdown-icon fe fe-download"></i> {{ trans('g.download_pdf') }}</a>';
<?php } ?>
<?php if (auth()->user()->can('edit-invoice')) { ?>
          if (row.status_key != 'draft') {
            ret += '  <div class="dropdown-divider"></div>';

            if (row.status_key != 'sent' && row.status_key != 'overdue') {
              ret += '  <a href="javascript:changeStatus(' + row.id + ', \'sent\')" class="dropdown-item"><span style=\'display:inline-block;position:relative;top:-1px;border-radius:50%;width:8px;height:8px;margin-right:8px;background-color:{{ trans('g.invoice_statuses.sent.color') }}\'></span> {{ trans('g.invoice_statuses.sent.label') }}</a>';
            }

            if (row.status_key != 'partially_paid') {
              ret += '  <a href="javascript:changeStatus(' + row.id + ', \'partially_paid\')" class="dropdown-item"><span style=\'display:inline-block;position:relative;top:-1px;border-radius:50%;width:8px;height:8px;margin-right:8px;background-color:{{ trans('g.invoice_statuses.partially_paid.color') }}\'></span> {{ trans('g.invoice_statuses.partially_paid.label') }}</a>';
            }

            if (row.status_key != 'paid') {
              ret += '  <a href="javascript:changeStatus(' + row.id + ', \'paid\')" class="dropdown-item"><span style=\'display:inline-block;position:relative;top:-1px;border-radius:50%;width:8px;height:8px;margin-right:8px;background-color:{{ trans('g.invoice_statuses.paid.color') }}\'></span> {{ trans('g.invoice_statuses.paid.label') }}</a>';
            }

            if (row.status_key != 'written_off') {
              ret += '  <a href="javascript:changeStatus(' + row.id + ', \'written_off\')" class="dropdown-item"><span style=\'display:inline-block;position:relative;top:-1px;border-radius:50%;width:8px;height:8px;margin-right:8px;background-color:{{ trans('g.invoice_statuses.written_off.color') }}\'></span> {{ trans('g.invoice_statuses.written_off.label') }}</a>';
            }
          }
<?php } ?>
<?php if (auth()->user()->can('delete-invoice')) { ?>
          if (row.status_key == 'draft') {
            ret += '  <div class="dropdown-divider"></div>';
            ret += '  <a href="javascript:deleteRecords([' + row.id + '])" class="dropdown-item"><i class="dropdown-icon fe fe-trash"></i> {{ trans('g.delete_record') }}</a>';
          }
<?php } ?>
          ret += '</div>';
          ret += '</div>';
          return ret;
        },
        targets: 3
      },
    ],
    order: [],
    drawCallback: function () {
      if (! datatable.data().count()) {
        $('.btn-export').attr('disabled', 1);
      } else {
        $('.btn-export').attr('disabled', null);
      }

      $('[data-toggle="tooltip"]').tooltip({
        boundary: 'window',
        trigger: 'hover'
      });

      $('[data-toggle="popover"]').popover({
        boundary: 'window',
        trigger: 'hover',
        html: true
      });
    },
    initComplete: function() {
      // Create status array
      var status = [];
      datatable.rows().every(function (index, value) {
        status[this.data().invoice_status_id] = this.data().status_name;
      });

      this.api().columns(1).every(function() {
          var column = this;
          var select = $('<select class="form-control form-control-sm selectize-color"><option value="0" data-data=\'{"color": "#000"}\'>{{ trans('g.all_statuses') }}</option><option value="" disabled>---</option></select>')
              .appendTo($(column.header()).empty())
              .on('change', function () {
                  var val = $.fn.dataTable.util.escapeRegex(
                      $(this).val()
                  );

                  column
                      .search(val ? ''+val+'' : '', true, false)
                      .draw();
              });

          // Add options
          var selected_value = (typeof column.data().state().columns[1].search.search !== 'undefined') ? column.data().state().columns[1].search.search : 0;

<?php foreach (trans('g.invoice_statuses') as $status) { ?>
            var selected = (selected_value == "{{ $status['value'] }}") ? ' selected' : '';
            select.append( '<option value="{{ $status['value'] }}"' + selected + ' data-data=\'{"color": "{{ $status['color'] }}"}\'">{{ $status['label'] }}</option>' )
<?php } ?>

      });

      $('.selectize-color').selectize({
        render: {
          option: function (data, escape) {
            return '<div class="text-truncate">' +
            '<span style=\'display:inline-block;position:relative;top:-1px;border-radius:50%;width:8px;height:8px;margin-right:8px;background-color:' + data.color + '\'></span>' +
            '<span>' + escape(data.text) + '</span>' +
            '</div>';
          },
        item: function (data, escape) {
          return '<div>' +
            '<span style=\'display:inline-block;position:relative;top:0px;border-radius:50%;width:8px;height:8px;margin-right:8px;background-color:' + data.color + '\'></span>' +
            '<span>' + escape(data.text) + '</span>' +
            '</div>';
          }
        }
      });
    }
  });
  $('.dataTables_filter .form-control-sm').removeClass('form-control-sm').css('width', '125px');
  $('.dataTables_length .custom-select-sm').removeClass('custom-select-sm form-control-sm');
  $('.btn-delete-selected').attr('disabled', 1);
  
});

function formatCurrency(amount) {
  return currency(amount, { precision: "{{ auth()->user()->getDecimals() }}", separator: "{{ auth()->user()->getThousandsSep() }}", decimal: "{{ auth()->user()->getDecimalSep() }}", formatWithSymbol: false }).format();
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
        url: "{{ url('invoices/delete-invoices') }}",
        data: {ids: ids, _token: '<?= csrf_token() ?>'},
        method: 'POST'
      })
      .done(function(data) {
        if(data === true) {
          datatable.ajax.reload();
        }
      })
      .fail(function() {
        console.log('error');
      })
      .always(function() {
      });
    }
  })
}

function changeStatus(id, status) {
  var jqxhr = $.ajax({
    url: "{{ url('invoices/status') }}",
    data: {id: id, status: status, _token: '<?= csrf_token() ?>'},
    method: 'POST'
  })
  .done(function(data) {
    if(data === true) {
      datatable.ajax.reload();
    }
  })
  .fail(function() {
    console.log('error');
  })
  .always(function() {
  });
}

function resendInvoice(id) {
  Swal({
    title: "{!! trans('g.send_invoice') !!}",
    text: "{!! trans('g.send_invoice_msg') !!}",
    imageUrl: "{{ url('assets/img/icons/fe/send.svg') }}",
    imageWidth: 48,
    showCancelButton: true,
    confirmButtonColor: '#3085d6',
    cancelButtonColor: '#d33',
    confirmButtonText: "{!! trans('g.send') !!}"
  }).then((result) => {
    if (result.value) {
      var jqxhr = $.ajax({
        url: "{{ url('invoices/send') }}",
        data: {id: id, _token: '<?= csrf_token() ?>'},
        method: 'POST'
      })
      .done(function(data) {
        if(typeof data.msg !== 'undefined') {
          datatable.ajax.reload();
          Swal({ imageUrl: "{{ url('assets/img/icons/fe/send.svg') }}", imageWidth: 48, title: "{!! trans('g.sent') !!}", text: data.msg });
        }
      })
      .fail(function() {
        console.log('error');
      })
      .always(function() {
      });
    }
  });
};

<?php
$projects = \Platform\Models\Project::join('companies', 'companies.id', 'projects.company_id')->select([DB::raw("projects.id as id"), 'projects.project_status_id', 'companies.name as company_name', 'projects.name as project_name', DB::raw("companies.name, CONCAT(companies.name, ' - ', projects.name) as name"), 'company_id'])->orderBy('name')->get();

// Projects with proposition
$projects_with_proposition_found = 0;
$projects_with_proposition = [];
foreach ($projects as $project) {
  if (isset($project->propositions[0])) {
    $projects_with_proposition[] = ['id' => \Platform\Controllers\Core\Secure::array2string(array('project_id' => $project->id)), 'name' => $project->name];
    $projects_with_proposition_found++;
  }
}
$projects_with_proposition_placeholder = ($projects_with_proposition_found == 0) ? trans('g.no_projects_with_proposition_found') : trans('g.select_project_');

// Projects with tasks
$projects_with_tasks_found = 0;
$projects_with_tasks = [];
foreach ($projects as $project) {
  if ($project->tasks->count() > 0) {
    $projects_with_tasks[] = ['id' => \Platform\Controllers\Core\Secure::array2string(array('project_id' => $project->id)), 'name' => $project->name];
    $projects_with_tasks_found++;
  }
}
$projects_with_tasks_placeholder = ($projects_with_tasks_found == 0) ? trans('g.no_projects_with_tasks_found') : trans('g.select_project_');

$tax_rates = \Platform\Models\Core\TaxRate::select('rate')->orderBy('rate', 'desc')->get();
?>
function addRecord() {
  Swal({
    title: "{!! trans('g.create_invoice') !!}",
    width: 600,
    html: '<div class="form-group mt-3">' +
          '  <select class="custom-select selectize" id="new_invoice_based_on">' +
          '    <option value="empty">{!! str_replace("'", "\'", trans('g.start_from_scratch')) !!}</option>' +
          '    <option value="project_proposition">{!! str_replace("'", "\'", trans('g.project_proposition_invoice')) !!}</option>' +
          '    <option value="project_tasks">{!! str_replace("'", "\'", trans('g.project_tasks_invoice')) !!}</option>' +
          '  </select>' +
          '</div>' +
          '<div id="create_based_on_proposition" class="d-none">' +
          '<div class="form-group">' +
          '  <select class="custom-select selectize" id="new_invoice_proposition_project_id" placeholder="{!! str_replace("'", "\'", $projects_with_proposition_placeholder) !!}">' +
          '    <option value="">{!! str_replace("'", "\'", $projects_with_proposition_placeholder) !!}</option>' +
<?php 
foreach ($projects_with_proposition as $project) {
?>
          '    <option value="{{ $project['id'] }}" data-type="proposition">{!! str_replace("'", "\'", $project['name']) !!}</option>' +
<?php
}
?>
          '  </select>' +
          '</div>' +
          '</div>' +
          '<div id="create_based_on_tasks" class="d-none">' +
          '<div class="form-group">' +
          '  <select class="custom-select selectize" id="new_invoice_tasks_project_id" placeholder="{!! str_replace("'", "\'", $projects_with_tasks_placeholder) !!}">' +
          '    <option value="">{!! str_replace("'", "\'", $projects_with_tasks_placeholder) !!}</option>' +
<?php 
foreach ($projects_with_tasks as $project) {
?>
          '    <option value="{{ $project['id'] }}" data-type="tasks">{!! str_replace("'", "\'", $project['name']) !!}</option>' +
<?php
}
?>
          '  </select>' +
          '</div>' +
          '<div class="form-group">' +
          '  <select class="custom-select" id="new_invoice_tax" placeholder="{!! str_replace("'", "\'", trans('g.select_default_tax_rate_')) !!}">' +
          '    <option value="">{!! str_replace("'", "\'", trans('g.select_default_tax_rate_')) !!}</option>' +
<?php 
foreach ($tax_rates as $rate) {
?>
          '    <option value="{{ $rate->rate }}">{{ $rate->percentage }}</option>' +
<?php
}
?>
          '  </select>' +
          '</div>' +
          '<div class="form-group">' +
          '  <div class="custom-control custom-checkbox">' +
          '    <input type="checkbox" id="new_invoice_only_completed" value="1" class="custom-control-input">' +
          '    <label class="custom-control-label" for="new_invoice_only_completed">{{ str_replace("'", "\'", trans('g.only_add_completed_tasks')) }}</label>' +
          '  </div>' +
          '</div>' +
          '</div>',
    imageUrl: "{{ url('assets/img/icons/fe/plus-circle.svg') }}",
    imageWidth: 48,
    showCancelButton: true,
    confirmButtonColor: '#3085d6',
    cancelButtonColor: '#d33',
    confirmButtonText: "{!! trans('g.go') !!}",
    onBeforeOpen: () => {
      $('#new_invoice_based_on').on('change', function() {
        var based_on = $(this).val();
      
        $('#create_based_on_proposition').addClass('d-none');
        $('#create_based_on_tasks').addClass('d-none');

        if (based_on == 'project_proposition') {
          $('#create_based_on_proposition').removeClass('d-none');
        }

        if (based_on == 'project_tasks') {
          $('#create_based_on_tasks').removeClass('d-none');
        }
      });

      $('#new_invoice_based_on, #new_invoice_proposition_project_id, #new_invoice_tasks_project_id, #new_invoice_tax').selectize();
    },
    preConfirm: () => {
      var based_on = document.getElementById('new_invoice_based_on').value;
      var proposition_project_id = document.getElementById('new_invoice_proposition_project_id').value;
      var tasks_project_id = document.getElementById('new_invoice_tasks_project_id').value;
      var tax = document.getElementById('new_invoice_tax').value;

      $('#new_invoice_proposition_project_id').next('.selectize-control').find('.selectize-input').css('border-color', 'rgba(0,40,100,.12)');
      $('#new_invoice_tasks_project_id').next('.selectize-control').find('.selectize-input').css('border-color', 'rgba(0,40,100,.12)');
      $('#new_invoice_tax').next('.selectize-control').find('.selectize-input').css('border-color', 'rgba(0,40,100,.12)');

      if (based_on == 'project_proposition' && proposition_project_id == '') {
        $('#new_invoice_proposition_project_id').next('.selectize-control').find('.selectize-input').css('border-color', '#ff4141');
        return false;
      }

      if (based_on == 'project_tasks') {
        if (tasks_project_id == '') {
          $('#new_invoice_tasks_project_id').next('.selectize-control').find('.selectize-input').css('border-color', '#ff4141');
        }
        if (tax == '') {
          $('#new_invoice_tax').next('.selectize-control').find('.selectize-input').css('border-color', '#ff4141');
        }

        if (tasks_project_id == '' || tax == '') {
          return false;
        }
      }

      return [
        based_on,
        proposition_project_id,
        tasks_project_id,
        tax,
        document.getElementById('new_invoice_only_completed').checked
      ]
    }
  }).then((result) => {
    if (result.value) {
      var based_on = result.value[0];
      var proposition_project_id = result.value[1];
      var tasks_project_id = result.value[2];
      var tax = result.value[3];
      var only_completed_tasks = (result.value[4]) ? 1 : 0;

      if (based_on == 'empty') {
        document.location = '{{ url('invoices/create') }}';
      }

      if (based_on == 'project_proposition') {
        document.location = '{{ url('invoices/create/project-proposition/') }}/' + proposition_project_id;
      }

      if (based_on == 'project_tasks') {
        document.location = '{{ url('invoices/create/project-tasks/') }}/' + tasks_project_id + '/' + tax + '/' + only_completed_tasks;
      }
    }
  })
}
</script>
@stop