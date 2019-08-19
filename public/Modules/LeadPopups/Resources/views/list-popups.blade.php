@extends('layouts.app')

@section('page_title', trans('leadpopups::g.lead_popups') . ' - ' . config('system.name'))

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
              <h3 class="card-title">{{ trans('leadpopups::g.lead_popups') }}</h3>
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
                    <th>{{ trans('g.name') }}</th>
                    <th class="text-center">{{ trans('leadpopups::g.views') }}</th>
                    <th class="text-center">{{ trans('leadpopups::g.conversions') }}</th>
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
  $('#all_rows').on('click', checkRowSelection);

  datatable = $('#datatable').DataTable({
    ajax: "{{ url('popups/json') }}",
    stateSaveCallback: function(settings,data) {
      localStorage.setItem('popups_{{ auth()->user()->id }}_' + settings.sInstance, JSON.stringify(data));
    },
    stateLoadCallback: function(settings) {
      return JSON.parse(localStorage.getItem( 'popups_{{ auth()->user()->id }}_' + settings.sInstance ));
    },
    buttons: [
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
      {
        text: "<i class=\"material-icons\" style=\"position:relative;top:1px\">add</i><span class=\"d-none d-md-inline\"> {{ trans('leadpopups::g.popup') }}</span>",
        attr: {
            title : '{{ trans('leadpopups::g.create_popup') }}',
            'data-toggle' : 'tooltip'
        },
        className: 'btn btn-secondary rounded-0',
        action: function ( e, dt, node, config ) {
          document.location = '{{ url('popups/create') }}';
        }
      }
    ],
    columns: [
      {
        data: "id",
        sortable: false,
        width: 25
      },
      {
        data: "name",
        sortable: true
      },
      {
        data: "views",
        sortable: true,
        width: 54
      },
      {
        data: "conversions",
        sortable: true,
        width: 54
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
          return '<div class="custom-control custom-checkbox" style="left:10px; top:-12px">' + 
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
  
          ret += '<div>' + row.name + '</div>';
          ret += '<div class="text-muted small">';
          ret += '{{ trans('leadpopups::g.trigger') }}: ' + row.trigger + ' ';
          ret += '</div>';

          if (row.active != 1) {
            ret += '<span class="badge badge-secondary mr-2">{!! trans('g.inactive') !!}</span>';
          }

          return ret;
        },
        targets: [1]
      },
      {
        className: 'align-middle text-center',
        render: function (data, type, row) {
          return data;
        },
        targets: [2, 3]
      },
      {
        className: 'align-middle',
        render: function (data, type, row) {
          var ret = '';

          ret += '<div class="item-action dropdown">';
          ret += '<a href="javascript:void(0)" data-toggle="dropdown" class="icon"><i class="fe fe-more-vertical"></i></a>';
          ret += '<div class="dropdown-menu dropdown-menu-right">';
          ret += '  <a href="<?php echo url('popups/edit/') ?>/' + data + '" class="dropdown-item"><i class="dropdown-icon fe fe-edit-2"></i> {{ trans('g.edit') }}</a>';
          ret += '  <a href="javascript:getScript(\'' + row.token + '\', \'' + row.name + '\')" class="dropdown-item"><i class="dropdown-icon fe fe-code"></i> {{ trans('leadpopups::g.get_script') }}</a>';
          ret += '  <div class="dropdown-divider"></div>';
          ret += '  <a href="javascript:deleteRecords([' + row.id + '])" class="dropdown-item"><i class="dropdown-icon fe fe-trash"></i> {{ trans('g.delete_record') }}</a>';
          ret += '</div>';
          ret += '</div>';
          return ret;
        },
        targets: 4
      },
    ],
    order: [],
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
        url: "{{ url('popups/delete-popups') }}",
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

function getScript(token, title) {
  Swal({
    title: title,
    text: "{!! trans('leadpopups::g.script_info') !!}",
    input: 'textarea',
    inputValue: '<script src="<?php echo url('modules/popups/assets/scripts.js?token=') ?>' + token + '"><\/script>',
    width: '480px',
    showCancelButton: false,
    confirmButtonColor: '#3085d6',
    confirmButtonText: "{!! trans('g.close') !!}"
  }).then((result) => {
  })
}
</script>
@stop