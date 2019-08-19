@extends('layouts.app')

@section('page_title', trans('newsletters::g.newsletters') . ' - ' . config('system.name'))

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
              <h3 class="card-title">{{ trans('newsletters::g.newsletters') }}</h3>
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
                    <th>{{ trans('newsletters::g.newsletter') }}</th>
                    <th class="text-center">{{ trans('newsletters::g.recepients') }}</th>
                    <th>{{ trans('newsletters::g.last_sent') }}</th>
                    <th>{{ trans('g.created') }}</th>
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
  a .img-thumbnail:hover {
    border-color: #146eff;
    transition: all 0.5s ease;
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
    ajax: "{{ url('newsletters/json') }}",
    stateSaveCallback: function(settings,data) {
      localStorage.setItem('newsletters_{{ auth()->user()->id }}_' + settings.sInstance, JSON.stringify(data));
    },
    stateLoadCallback: function(settings) {
      return JSON.parse(localStorage.getItem( 'newsletters_{{ auth()->user()->id }}_' + settings.sInstance ));
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
        text: "<i class=\"material-icons\" style=\"position:relative;top:1px\">add</i><span class=\"d-none d-md-inline\"> {{ trans('newsletters::g.newsletter') }}</span>",
        attr: {
            title : '{{ trans('newsletters::g.create_newsletter') }}',
            'data-toggle' : 'tooltip'
        },
        className: 'btn btn-secondary rounded-0',
        action: function ( e, dt, node, config ) {
          createNewsletter();
          //document.location = '{{ url('newsletters/create') }}';
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
        sortable: false
      },
      {
        data: "number_of_recepients",
        sortable: true,
        width: 80
      },
      {
        data: "last_sent_date",
        sortable: true,
        width: 130
      },
      {
        data: "created_at",
        sortable: true,
        width: 130
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
          ret += '{{ trans('newsletters::g.subject') }}: ' + row.subject + '';
          ret += '</div>';
          ret += '<div class="text-muted small">';
          ret += '{{ trans('newsletters::g.recepients') }}: ' + row.recepients + '';
          ret += '</div>';

          return ret;
        },
        targets: [1]
      },
      {
        className: 'align-middle d-none d-md-table-cell',
        render: function (data, type, row) {
          return '<div class="text-center">' + data + '</div>';
        },
        targets: [2]
      },
      {
        className: 'align-middle d-none d-md-table-cell',
        render: function (data, type, row) {
          return '<div class="small">' + data + '</div>';
        },
        targets: [3]
      },
      {
        className: 'align-middle d-none d-lg-table-cell',
        render: function (data, type, row) {
          return '<div class="small">' + data + '</div>';
        },
        targets: [4]
      },
      {
        className: 'align-middle',
        render: function (data, type, row) {
          var ret = '';

          ret += '<div class="item-action dropdown">';
          ret += '<a href="javascript:void(0)" data-toggle="dropdown" class="icon"><i class="fe fe-more-vertical"></i></a>';
          ret += '<div class="dropdown-menu dropdown-menu-right">';
          ret += '  <a href="<?php echo url('newsletters/edit/') ?>/' + data + '" class="dropdown-item"><i class="dropdown-icon fe fe-edit-2"></i> {{ trans('g.edit') }}</a>';
          ret += '  <a href="javascript:duplicateRecord(\'' + data + '\')" class="dropdown-item"><i class="dropdown-icon fe fe-copy"></i> {{ trans('newsletters::g.duplicate') }}</a>';
          ret += '  <div class="dropdown-divider"></div>';
          ret += '  <a href="javascript:sendNewsletter(\'' + data + '\', \'' + row.name.replace(/"/g, '&quot;').replace(/'/g, '\\\'') + '\', \'' + row.recepients.replace(/"/g, '&quot;').replace(/'/g, '\\\'') + '\')" class="dropdown-item"><i class="dropdown-icon fe fe-send"></i> {{ trans('newsletters::g.send_newsletter') }}</a>';
          ret += '  <div class="dropdown-divider"></div>';
          ret += '  <a href="javascript:deleteRecords([' + row.id + '])" class="dropdown-item"><i class="dropdown-icon fe fe-trash"></i> {{ trans('g.delete_record') }}</a>';
          ret += '</div>';
          ret += '</div>';
          return ret;
        },
        targets: 5
      },
    ],
    order: [[4, 'desc']],
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
        url: "{{ url('newsletters/delete-newsletters') }}",
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

function sendNewsletter(sl, name, recepients) {
  Swal({
    title: name,
    text: "{!! trans('newsletters::g.send_newsletter_msg') !!} " + recepients,
    imageUrl: "{{ url('assets/img/icons/fe/send.svg') }}",
    imageWidth: 48,
    showCancelButton: true,
    confirmButtonColor: '#3085d6',
    cancelButtonColor: '#d33',
    confirmButtonText: "{!! trans('g.send') !!}"
  }).then((result) => {
    if (result.value) {
      var jqxhr = $.ajax({
        url: "{{ url('newsletters/send') }}",
        data: {sl: sl, _token: '<?= csrf_token() ?>'},
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
}

function duplicateRecord(sl) {
  var jqxhr = $.ajax({
    url: "{{ url('newsletters/duplicate') }}",
    data: {sl: sl, _token: '<?= csrf_token() ?>'},
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

function createNewsletter() {
  Swal({
    title: "{{ trans('newsletters::g.create_newsletter') }}",
    text: "{!! trans('newsletters::g.script_info') !!}",
    html: `
<?php foreach ($templates as $template) { ?>
<a href="{{ url('newsletters/create/' . $template['url']) }}" class="m-1"><img src="{{ $template['thumb'] }}" style="width: 120px" class="img-thumbnail"></a>
<?php } ?>
`,
    width: '720px',
    showCancelButton: false,
    confirmButtonColor: '#3085d6',
    confirmButtonText: "{!! trans('g.close') !!}"
  }).then((result) => {
  });
}

</script>
@stop