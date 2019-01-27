@extends('../../layouts.app')

@section('page_title', trans('g.companies') . ' - ' . config('system.name'))

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
              <h3 class="card-title">{{ trans('g.companies') }}</h3>
            </div>
            <div class="card-body px-0 py-3">

              <table class="table table-striped table-hover table-borderless" id="datatable">
                <thead class="thead-dark">
                  <tr>
                    <th>
                      <div class="custom-control custom-checkbox" style="position: relative; top: -11px; left:10px">
                        <input type="checkbox" class="custom-control-input" id="all_rows" onClick="toggleRows(this)">
                        <label class="custom-control-label" for="all_rows"></label>
                      </div>
                    </th>
<?php
foreach($columns as $column) {
  $width = (isset($column['width'])) ? ' width="' . $column['width'] . '"' : '';
  $head = (isset($column['show_head']) && $column['show_head'] === false) ? '' : $column['name'];
  if (isset($column['label'])) $head = $column['label'];
?>
                    <th{!! $width !!} class="text-truncate">{{ $head }}</th>
<?php } ?>
                    <th>{{ trans('g.contact_persons') }}</th>
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
  var roles = [];
<?php
$roles = \Spatie\Permission\Models\Role::all();
foreach ($roles as $role) {
?>
  roles[<?php echo $role->id ?>] = "<?php echo str_replace('"', '&quot;', $role->name) ?>";
<?php
}
?>
  $('#all_rows').on('click', checkRowSelection);

  datatable = $('#datatable').DataTable({
    ajax: "{{ $form->getData('ajax') }}",
    stateSaveCallback: function(settings,data) {
      localStorage.setItem('companies_{{ auth()->user()->id }}_' + settings.sInstance, JSON.stringify(data));
    },
    stateLoadCallback: function(settings) {
      return JSON.parse(localStorage.getItem( 'companies_{{ auth()->user()->id }}_' + settings.sInstance ));
    },
    buttons: [
      {
        extend: 'collection',
        text: "<i class=\"material-icons\" style=\"position:relative;top:1px\">import_export</i> {{ trans('g.export') }} ",
        className: 'btn btn-secondary buttons-collection dropdown-toggle mr-2 rounded-0 btn-export',
        buttons: [
          {
            text: "Excel (.xlsx)",
            action: function ( e, dt, node, config ) { document.location = '{{ url('companies/export/xlsx') }}'; }
          }, {
            text: "Excel (.xls)",
            action: function ( e, dt, node, config ) { document.location = '{{ url('companies/export/xls') }}'; }
          }, {
            text: "Comma-separated (.csv)",
            action: function ( e, dt, node, config ) { document.location = '{{ url('companies/export/csv') }}'; }
          }<?php /*, {
            text: "HTML (.html)",
            action: function ( e, dt, node, config ) { document.location = '{{ url('companies/export/html') }}'; }
          }*/ ?>
        ]
      },
<?php if (auth()->user()->can('delete-company')) { ?>
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
<?php if (auth()->user()->can('create-company')) { ?>
      {
        text: "<i class=\"material-icons\" style=\"position:relative;top:1px\">add</i><span class=\"d-none d-md-inline\"> {{ trans('g.company') }}</span>",
        attr: {
            title : '{{ trans('g.create_company') }}',
            'data-toggle' : 'tooltip'
        },
        className: 'btn btn-secondary rounded-0',
        action: function ( e, dt, node, config ) {
          document.location = '{{ url('companies/create') }}';
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
<?php
foreach($columns as $i => $column) {
?>
      {
        sortable: <?php echo (isset($column['sortable']) && $column['sortable']) ? 'true' : 'false'; ?>,
        data: "<?php echo $column['options']['real_name'] ?>",
<?php if(isset($column['width'])) { ?>
        width: {{ $column['width'] }}
<?php } ?>
      },
<?php
}
?>
      {
        data: "users",
        sortable: false
      },
      {
        data: "sl",
        sortable: false,
        width: 18
      }
    ],
    columnDefs: [
      {
        className: 'align-middle d-none d-md-table-cell',
        render: function (data, type, row) {
          return '<div class="custom-control custom-checkbox" style="left:10px; top:-10px">' + 
                 ' <input type="checkbox" class="custom-control-input" id="rows_' + row.id + '" name="rows[]" value="' + row.id + '" onclick="checkRowSelection()">' + 
                 ' <label class="custom-control-label" for="rows_' + row.id + '"></label>' + 
                 '</div>';

        },
        targets: 0
      },
      {
        className: 'align-middle',
        render: function (data, type, row) {
          var ret = '';

          var row_class = (parseInt(row.active) == 0) ? 'text-muted' : '';
          if (parseInt(row.default) == 1) row_class += 'font-weight-bold';

          ret += '<div class="' + row_class + '">' + row.name + '</div>';

          if (row.industry != null || row.city != null) {
            ret += '<div><small class="' + row_class + '">';
          }

          if (row.industry != null) {
            ret += '' + row.industry + '';
            if (row.city != null) {
              ret += ', ';
            }
          }

          if (row.city != null) {
            ret += '' + row.city + '';
          }

          if (row.industry != null || row.city != null) {
            ret += '</small></div>';
          }

          if (row.phone != null) {
            ret += '<div class="' + row_class + '"><i class="material-icons" style="font-size:12px; position:relative; top: 1px">phone</i> ' + row.phone + '</div>';
          }

          if (parseInt(row.active) == 0) {
            ret += ' <div class="badge badge-secondary"><?php echo strtolower(trans('g.inactive')) ?></div>';
          }
          return ret;
        },
        targets: [1]
      },
      {
        className: 'align-middle',
        render: function (data, type, row) {
          if (data !== null) {
            if (parseInt(row.active) == 0) {
              return '<div class="text-muted">' + data + '</div>';
            } else {
              return data;
            }
          } else {
            return '';
          }
        },
        targets: []
      },
      {
        className: 'align-middle',
        render: function (data, type, row) {
          var ret = '';

          if (data.length > 0) {
            for (var i in data) {
              var title = (data[i].active == 0) ? ' (<?php echo strtolower(trans('g.inactive')) ?>)' : '';
<?php if (auth()->user()->can('view-user')) { ?>
              ret += '<a href="<?php echo url('users/view/') ?>/' + data[i].sl + '">';
<?php } ?>
              ret += '<div class="m-1" style=\'border-radius:50%;display: inline-block;border: 2px solid ' + data[i].role_color + ';\'>';
              ret += '<div class="avatar" style="background-image: url(' + data[i].avatar + '); border: 1px solid #fff;" data-placement="top" data-title="' + 
                '<div class=\'text-center\'><span style=\'display:inline-block;position:relative;top:-1px;border-radius:50%;width:8px;height:8px;margin-right:2px;background-color:' + data[i].role_color + '\'></span> ' + data[i].role + title + '</div>' + 
                '" data-content="' + 
                '<div class=\'text-center\'><img src=\'' + data[i].avatar + '\' class=\'avatar avatar-xxl\'></div>' + 
                '<div class=\'text-center mt-2\'>' + data[i].name + '</div>';
              if (data[i].job_title !== null) {
                ret += '<div class=\'text-center text-muted mt-1\'>' + data[i].job_title + '</div>';
              }
              ret += '<div class=\'text-center mt-2\'><i class=\'material-icons\' style=\'font-size:12px; position:relative; top: 2px\'>alternate_email</i> ' + data[i].email + '</div>';

              if (data[i].phone != null) {
                ret += '<div class=\'text-center mt-1\'><i class=\'material-icons\' style=\'font-size:12px; position:relative; top: 1px\'>phone</i> ' + data[i].phone + '</div>';
              }

              ret += '" data-toggle="popover">';
              if (data[i].recently_online) ret += '<span class="avatar-status bg-green"></span>';
              ret += '</div>';
              ret += '</div>';
<?php if (auth()->user()->can('view-user')) { ?>
              ret += '</a>';
<?php } ?>
            }
          }

          return ret;
        },
        targets: [2]
      },
      {
        className: 'align-middle',
        targets: [0]
      },
      {
        className: 'align-middle',
        render: function (data, type, row) {
          var ret = '';

          ret += '<div class="item-action dropdown">';
          ret += '<a href="javascript:void(0)" data-toggle="dropdown" class="icon"><i class="fe fe-more-vertical"></i></a>';
          ret += '<div class="dropdown-menu dropdown-menu-right">';
<?php if (auth()->user()->can('edit-company')) { ?>
          ret += '  <a href="<?php echo url('companies/edit/') ?>/' + data + '" class="dropdown-item"><i class="dropdown-icon fe fe-edit-2"></i> {{ trans('g.edit') }}</a>';
<?php } ?>
<?php if (auth()->user()->can('view-company')) { ?>
          ret += '  <a href="<?php echo url('companies/view/') ?>/' + data + '" class="dropdown-item"><i class="dropdown-icon fe fe-search"></i> {{ trans('g.view') }}</a>';
<?php } ?>
<?php if (auth()->user()->can('delete-company')) { ?>
          ret += '  <div class="dropdown-divider"></div>';
          ret += '  <a href="javascript:deleteRecords([' + row.id + '])" class="dropdown-item"><i class="dropdown-icon fe fe-trash"></i> {{ trans('g.delete_record') }}</a>';
<?php } ?>
          ret += '</div>';
          ret += '</div>';
          return ret;
        },
        targets: {{ count($columns) + 2 }}
      },
    ],
    order: [
<?php
foreach($columns as $i => $column) {
  if (isset($column['default_sort'])) {
?>
      [<?php echo $i + 1 ?>, "<?php echo $column['default_sort'] ?>"],
<?php
  }
}
?>
    ],
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
    }
  });
  $('.dataTables_filter .form-control-sm').removeClass('form-control-sm').css('width', '175px');
  $('.dataTables_length .custom-select-sm').removeClass('custom-select-sm form-control-sm');
  $('.btn-delete-selected').attr('disabled', 1);
  
});

function deleteRecords(ids) {
  Swal({
    title: "{!! trans('g.are_you_sure') !!}",
    text: "{!! trans('g.confirm_delete') !!}",
    type: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#3085d6',
    cancelButtonColor: '#d33',
    confirmButtonText: "{!! trans('g.yes_delete') !!}"
  }).then((result) => {
    if (result.value) {
      var jqxhr = $.ajax({
        url: "{{ url('companies/delete-companies') }}",
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
</script>
@stop