@extends('../../layouts.app')

@section('page_title', trans('g.people') . ' - ' . config('system.name'))

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
              <h3 class="card-title">{{ trans('g.people') }}</h3>
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
                    <th style="min-width: 120px">{{ trans('g.role') }}</th>
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
$roles = \App\Role::all();
foreach ($roles as $role) {
?>
  roles[<?php echo $role->id ?>] = {name: "<?php echo str_replace('"', '&quot;', $role->name) ?>", color: '{{ $role->color }}', count: '<?php echo \App\User::whereHas('roles', function($q) use($role){ $q->where('id', $role->id); })->count() ?>'};
<?php
}
?>
  $('#all_rows').on('click', checkRowSelection);

  datatable = $('#datatable').DataTable({
    ajax: "{{ $form->getData('ajax') }}",
    stateSaveCallback: function(settings,data) {
      localStorage.setItem('users_{{ auth()->user()->id }}_' + settings.sInstance, JSON.stringify(data));
    },
    stateLoadCallback: function(settings) {
      return JSON.parse(localStorage.getItem( 'users_{{ auth()->user()->id }}_' + settings.sInstance ));
    },
    buttons: [
      {
        extend: 'collection',
        fade: 0,
        text: "<i class=\"material-icons d-none d-md-inline\" style=\"position:relative;top:1px\">import_export</i> {{ trans('g.export') }} ",
        className: 'btn btn-secondary buttons-collection dropdown-toggle mr-2 rounded-0',
        buttons: [
          {
            text: "Excel (.xlsx)",
            action: function ( e, dt, node, config ) { document.location = '{{ url('users/export/xlsx') }}'; }
          }, {
            text: "Excel (.xls)",
            action: function ( e, dt, node, config ) { document.location = '{{ url('users/export/xls') }}'; }
          }, {
            text: "Comma-separated (.csv)",
            action: function ( e, dt, node, config ) { document.location = '{{ url('users/export/csv') }}'; }
          }<?php /*, {
            text: "HTML (.html)",
            action: function ( e, dt, node, config ) { document.location = '{{ url('users/export/html') }}'; }
          }*/ ?>
          , {
            text: "<hr class='m-0 my-3'>",
            className: "disabled py-0",
            action: function ( e, dt, node, config ) { }
          }, {
            text: "{{ trans('g.import_people') }}...",
            action: function ( e, dt, node, config ) { document.location = '{{ url('users/import') }}'; }
          }
        ]
      },
<?php if (auth()->user()->can('delete-user')) { ?>
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
<?php if (auth()->user()->can('create-user')) { ?>
      {
        text: "<i class=\"material-icons\" style=\"position:relative;top:1px\">add</i><span class=\"d-none d-md-inline\"> {{ trans('g.user') }}</span>",
        attr: {
            title : '{{ trans('g.create_user') }}',
            'data-toggle' : 'tooltip'
        },
        className: 'btn btn-secondary rounded-0',
        action: function ( e, dt, node, config ) {
          document.location = '{{ url('users/create') }}';
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
        data: "role",
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
          if (row.id != 1 && row.id != {{ auth()->user()->id }}) {
            return '<div class="custom-control custom-checkbox" style="left:10px; top:-10px">' + 
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
        render: function (data, type, row) {
          var ret = '';

          ret += '<div class="avatar d-block" style="background-image: url(' + data + ');width:48px;height:48px;">';
          if (row.recently_online) ret += '<span class="avatar-status bg-green"></span>';
          ret += '</div>';

          return ret;
        },
        targets: 1
      },
      {
        className: 'align-middle',
        render: function (data, type, row) {
          var ret = '';

          var active_class = (parseInt(row.active) == 0) ? ' class="text-muted"' : '';

          ret += '<div' + active_class + '>' + ((row.name == null) ? '-' : row.name) + '</div>';

          if (row.job_title != null) {
            ret += '<div><small class="text-muted">' + row.job_title + '</small></div>';
          }

          if (row.lead_source != null) {
            ret += '<div><small class="text-muted">{{ trans('g.lead_source') }}: ' + row.lead_source + '</small></div>';
          }

          if (parseInt(row.active) == 0) {
            ret += ' <div class="badge badge-secondary"><?php echo strtolower(trans('g.inactive')) ?></div>';
          }
          return ret;
        },
        targets: [2]
      },
      {
        className: 'align-middle text-truncate d-none d-md-table-cell',
        render: function (data, type, row) {
          var ret = '';
          var active_class = (parseInt(row.active) == 0) ? ' class="text-muted"' : '';

          ret += '<div' + active_class + '><i class="material-icons" style="font-size:12px; position:relative; top: 2px">alternate_email</i> ' + row.email + '</div>';

          if (row.phone != null) {
            ret += '<div' + active_class + '><i class="material-icons" style="font-size:12px; position:relative; top: 1px">phone</i> ' + row.phone + '</div>';
          }
          return ret;
        },
        targets: [3]
      },
      {
        className: 'align-middle',
        targets: [0]
      },
      {
        render: function (data, type, row) {
          var bullet = '<span style=\'display:inline-block;position:relative;top:-1px;border-radius:50%;width:8px;height:8px;margin-right:2px;background-color:' + roles[data].color + '\'></span> ';
          if (parseInt(row.active) == 0) {
            return '<div class="text-muted">' + bullet + roles[data].name + '</div>';
          } else {
            return bullet + roles[data].name;
          }
        },
        className: 'align-middle',
        targets: [4]
      },
      {
        className: 'align-middle',
        render: function (data, type, row) {
          var ret = '';

          ret += '<div class="item-action dropdown">';
          ret += '<a href="javascript:void(0)" data-toggle="dropdown" class="icon"><i class="fe fe-more-vertical"></i></a>';
          ret += '<div class="dropdown-menu dropdown-menu-right">';
<?php if (auth()->user()->can('edit-user')) { ?>
          ret += '  <a href="<?php echo url('users/edit/') ?>/' + data + '" class="dropdown-item"><i class="dropdown-icon fe fe-edit-2"></i> {{ trans('g.edit') }}</a>';
<?php } ?>
<?php if (auth()->user()->can('view-user')) { ?>
          ret += '  <a href="<?php echo url('users/view/') ?>/' + data + '" class="dropdown-item"><i class="dropdown-icon fe fe-search"></i> {{ trans('g.view') }}</a>';
<?php } ?>
<?php if (auth()->user()->can('login-as-user')) { ?>
          ret += '  <a href="<?php echo url('users/login/') ?>/' + data + '" class="dropdown-item"><i class="dropdown-icon fe fe-log-in"></i> {{ trans('g.login_as_user') }}</a>';
<?php } ?>
          if (row.id != 1 && row.id != {{ auth()->user()->id }}) {
            ret += '  <div class="dropdown-divider"></div>';
            ret += '  <a href="javascript:deleteRecords([' + row.id + '])" class="dropdown-item"><i class="dropdown-icon fe fe-trash"></i> {{ trans('g.delete_record') }}</a>';
          }

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
    initComplete: function () {
      this.api().columns(4).every(function () {
          var column = this;
          var select = $('<select class="form-control form-control-sm selectize-color"><option value="0" data-data=\'{"color": "#000"}\'>{{ trans('g.all_roles') }}</option><option value="0" disabled>---</option></select>')
              .appendTo($(column.header()).empty())
              .on('change', function() {
                  var val = $.fn.dataTable.util.escapeRegex(
                    $(this).val()
                  );

                  column
                      .search(val ? ''+val+'' : '', true, false)
                      .draw();
              });

          // Add options
          var selected_value = (typeof column.data().state().columns[4].search.search !== 'undefined') ? column.data().state().columns[4].search.search : 0;

          for (var i in roles) {
            var selected = (parseInt(selected_value) == parseInt(i)) ? ' selected' : '';
            select.append( '<option value="' + i + '"' + selected + ' data-data=\'{"color": "' + roles[i].color + '"}\'">' + roles[i].name + ' (' + roles[i].count + ')</option>' )
          }
      } );

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
        url: "{{ url('users/delete-users') }}",
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