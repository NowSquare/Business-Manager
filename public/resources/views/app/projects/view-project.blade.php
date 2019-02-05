@extends('../../layouts.app')

@section('page_title', trans('g.view_project') . ' - ' . $project->name . ' - ' . config('system.name'))

@section('page_head')
  <link rel="stylesheet" href="{{ url('assets/css/wysiwyg.css?' . config('system.client_side_timestamp')) }}">
  <script src="{{ url('assets/js/wysiwyg.js?' . config('system.client_side_timestamp')) }}"></script>

<?php if (auth()->user()->can('view-and-upload-all-project-files') || auth()->user()->can('user-view-and-upload-personal-project-files', $project)) { ?>

@include('layouts.modules.elfinder-init')

<script type="text/javascript">
  $(function() {
    $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
      if ($(e.target).attr('id') == 'files-tab') {
        $('#elfinder').trigger('resize');
      }
    });
  });
</script>

<?php } // view-and-upload-all-project-files || view-and-upload-personal-project-files ?>
@stop

@section('content')

  <div class="my-3 my-md-5">
    <div class="container">
      <div class="row">

        <div class="col-12">

          {!! form_start($form) !!}

          <div class="card">
            <div class="card-header">
              <h3 class="card-title"><span class="text-muted d-none d-md-inline">{{ $project->client->active_name }}  -</span> {{ $project->name }}</h3>
              <div class="card-options">
                <span class="text-muted small">{!! $project->status->bullet_name !!}</span>
              </div>
            </div>

            <div class="card-body p-0">

              <ul class="nav nav-tabs mx-0" role="tablist">
                <li class="nav-item pl-5">
                  <a class="nav-link" id="general-tab" data-toggle="tab" href="#general" role="tab" aria-selected="false">{{ trans('g.general') }}</a>
                </li>
<?php if (auth()->user()->can('user-view-project-tasks', $project) || auth()->user()->can('view-personal-project-tasks')) { ?>
                <li class="nav-item">
                  <a class="nav-link" id="tasks-tab" data-toggle="tab" href="#tasks" role="tab" aria-selected="false">{{ trans('g.tasks') }}</a>
                </li>
<?php } // view-project-tasks || view-personal-project-tasks ?>
<?php if (auth()->user()->can('user-view-project-proposition', $project) && isset($project->propositions[0])) { ?>
                <li class="nav-item">
                  <a class="nav-link" id="proposition-tab" data-toggle="tab" href="#proposition" role="tab" aria-selected="false">{{ trans('g.proposition') }}</a>
                </li>
<?php } // view-project-proposition ?>
<?php if (auth()->user()->can('view-and-upload-all-project-files') || auth()->user()->can('user-view-and-upload-personal-project-files', $project)) { ?>
                <li class="nav-item">
                  <a class="nav-link" id="files-tab" data-toggle="tab" href="#files" role="tab" aria-selected="false">{{ trans('g.files') }}</a>
                </li>
<?php } // view-and-upload-all-project-files || view-and-upload-personal-project-files ?>
<?php if (auth()->user()->can('user-view-project-comments', $project)) { ?>
                <li class="nav-item">
                  <a class="nav-link" id="comments-tab" data-toggle="tab" href="#comments" role="tab" aria-selected="false">{{ trans('g.comments') }} ({{ $project->totalCommentsCount() }})</a>
                </li>
<?php } // user-view-project-comments ?>
              </ul>

              <div class="tab-content">
                <div class="tab-pane px-5 pt-5 pb-0" id="general" role="tabpanel" aria-labelledby="general-tab">
                  <div class="row">
                    <div class="col-md-6 col-lg-4">
                      {!! form_row($form->start_date) !!}
                      {!! form_row($form->due_date) !!}
                      {!! form_row($form->completed_date) !!}
                    </div>
                    <div class="col-md-6 col-lg-2">
                    </div>
                    <div class="col-md-6 col-lg-2">
                    </div>


                    <div class="col-md-6 col-lg-4">

                      <div class="form-group">
                        <label class="form-label">{{ trans_choice('g.manager_s_', $project->managers->count()) }}</label>
<?php if ($project->managers->count() > 0) { ?>
<?php 
foreach ($project->managers->sortBy('name') as $manager) {
  echo '<span class="m-1">' . $manager->getAvatarHtml() . '</span>';
} 
?>
<?php } else { ?>
 -
<?php } ?>
                      </div>

<?php if ($project->created_at !== null && $project->created_by !== null) { ?>
                      <div class="form-group">
                        <label class="form-label">{{ trans('g.created') }}</label>
                        <table cellpadding="0" cellspacing="0">
                          <tr>
                            <td class="align-middle">
                              <span class="m-1">
                              {!! $project->createdBy->getAvatarHtml() !!}
                              </span>
                            </td>
                            <td class="align-middle">
                              <div class="text-truncate d-block ml-1">
                                {{ $project->created_at->timezone(auth()->user()->getTimezone())->diffForHumans() }}
                              </div>
                            </td>
                          </tr>
                        </table>
                      </div>
<?php } ?>
<?php if ($project->updated_at !== null && $project->updated_by !== null) { ?>
                      <div class="form-group">
                        <label class="form-label">{{ trans('g.last_update') }}</label>
                        <table cellpadding="0" cellspacing="0">
                          <tr>
                            <td class="align-middle">
                              <span class="m-1">
                              {!! $project->updatedBy->getAvatarHtml() !!}
                              </span>
                            </td>
                            <td class="align-middle">
                              <div class="text-truncate d-block ml-1">
                                {{ $project->updated_at->timezone(auth()->user()->getTimezone())->diffForHumans() }}
                              </div>
                            </td>
                          </tr>
                        </table>
                      </div>
<?php } ?>

                    </div>


                  </div>
<?php if (auth()->user()->can('user-view-project-description', $project)) { ?>
<?php if ($project->short_description != '') { ?>
                <div class="card">
                  <div class="card-status card-status-left bg-gray"></div>
                  <div class="card-body">
                      {!! str_replace(PHP_EOL, '<br>', $project->short_description) !!}
                  </div>
                </div>
<?php } ?>

<?php if ($project->description != '') { ?>
                <div class="card">
                  <div class="card-body">
                      {!! $project->description !!}
                  </div>
                </div>
<?php } ?>

<?php } // can('view-project-description') ?>

                </div>
<?php if (auth()->user()->can('user-view-project-tasks', $project) || auth()->user()->can('view-personal-project-tasks')) { ?>
                <div class="tab-pane pt-5 pl-5 pr-5 pb-0" id="tasks" role="tabpanel" aria-labelledby="tasks-tab">

                  <div class="row row-cards row-deck" id="task-items">
<?php
// All project tasks
$tasks = $project->tasks;

// Task statuses
$task_statuses = $tasks->sortBy('status.sort')->mapWithKeys(function($query) {
  return [$query->status->id => ['name' => $query->status->name, 'color' => $query->status->color]];
})->toArray();

foreach ($task_statuses as $project_status_id => $status) {
?>
<div class="col-md-6 col-xl-4">
  <div class="card">
    <div class="card-status bg-dark" style="background-color: {{ $status['color'] }} !important"></div>
    <div class="card-header">
      <h3 class="card-title">{{ $status['name'] }}</h3>
    </div>
    <div class="card-body p-0">
      <table class="table table-hover table-striped table-outline card-table table-vcenter table-sm mb-0">
<?php 
  foreach($tasks->where('project_status_id', $project_status_id)->sortByDate('completed_date', true)->sortByDate('due_date', false)->sortByDesc('priority') as $task) {
?>
        <tr>
          <td><?php 
if ($task->priority == 3) {
  echo '<i class="fas fa-exclamation-circle text-danger mr-1" data-toggle="tooltip" data-title="' . trans('g.priority') . ': ' . trans('g.status_code')[$task->priority] . '"></i> ';
} elseif ($task->priority == 2) {
  echo '<i class="fas fa-exclamation-circle text-warning mr-1" data-toggle="tooltip" data-title="' . trans('g.priority') . ': ' . trans('g.status_code')[$task->priority] . '"></i> ';
}
    
    $assignees = $task->assignees()->get()->pluck('id')->toArray();
    $class = 'text-inherit';
    if (in_array(auth()->user()->id, $assignees)) {
      $class = 'text-inherit';
    } else {
      if (! auth()->user()->can('edit-project-task')) {
        $class = 'text-muted';
      }
    }

?><a href="javascript:void(0);" class="{{ $class }} btn-view-task" data-row="{{ $task->id }}">{{ $task->subject }}</a></td>
          <td style="width: 120px"><?php 
$assignees = $task->assignees()->get();
if ($assignees->count() > 0) {
  foreach ($assignees as $assignee) {
    echo '<div class="m-1 float-right">' . $assignee->getAvatarHtml() . '</div>';
  }
}
?></td>
        </tr>
      
<?php
  }
?>
      </table>
    </div>
  </div>
</div>
<?php
}
?>
                  </div>

<script>
var priorities = ['<?php echo implode ("', '", trans('g.status_code')); ?>'];
var tasks = {};
 
$(function() {
<?php 
if (auth()->user()->can('edit-project-task') || auth()->user()->can('mark-project-task-complete')) {
  // Add form tag to task form
  $edit_form_tags = true;
}

// Add tasks
if ($tasks->count() > 0) {
?>
<?php
  foreach($tasks as $task) {
    $assignees = $task->assignees()->get()->pluck('id')->toArray();
?>
  tasks[<?php echo $task->id ?>] = {
    id: <?php echo $task->id ?>,
    task_changed: 0,
    task_new: 0,
    subject: "<?php echo str_replace('"', '&quot;', $task->subject) ?>",
    description: "<?php echo str_replace(PHP_EOL, '', str_replace('"', '&quot;', $task->description)) ?>",
    due_date_friendly: <?php echo ($task->due_date != null) ? "moment('" . $task->due_date . "').format('MMM Do YYYY')" : "'-'";  ?>,
    start_date: '<?php echo $task->start_date ?>',
    due_date: '<?php echo $task->due_date ?>',
    completed_date: '<?php echo $task->completed_date ?>',
    completed_by_id: '<?php echo $task->completed_by_id ?>',
    project_status_id: <?php echo $task->project_status_id ?>,
    project_status: statuses['<?php echo $task->project_status_id ?>'],
    priority_id: <?php echo $task->priority ?>,
    priority: priorities[<?php echo $task->priority ?>],
    assigned_to_id: '<?php echo trim(implode($assignees, ','), ','); ?>',
    assigned_to_me: <?php echo (in_array(auth()->user()->id, $assignees)) ? 'true' : 'false'; ?>
  };

<?php
  }
?>
  $('[data-toggle="popover"]').popover({
    boundary: 'window',
    trigger: 'hover',
    html: true
  });
<?php
}
?>

  $('#form_project_id').val('<?php echo $project->id ?>');

  $('#taskForm').on('keypress', 'input', function(e) {
    if (e.keyCode == 13) {
      if ($('#taskForm .btn-add-task').is(':visible')) {
        $('.btn-add-task').trigger('click');
      } else if ($('#taskForm .btn-save-task').is(':visible')) {
        $('.btn-save-task').trigger('click');
      }
      return false;
    }
  });

  // Open task
<?php if (is_numeric(request()->get('task', null))) { ?>
  setTimeout(function() {
    openTask({{ request()->get('task', null) }});
  }, 200);
<?php } ?>

  // Set task as complete
  $('#task-items').on('click', '.btn-view-task', function() {
    var id = $(this).attr('data-row');
    openTask(id);
  });

  // View task modal
  $('#taskForm .btn-complete-task').on('click', function() {
    var id = $(this).attr('data-row');
    if (typeof id !== 'undefined') {
      var now = Date.now();

      $('#form_task_completed_date').val(moment(now).format('YYYY-MM-DD HH:mm:ss'));
      $('[name=form_task_completed_date_field]').datepicker('update', moment(now).format('YYYY-MM-DD HH:mm:ss'));

      $('#form_task_completed_by_id')[0].selectize.setValue({{ auth()->user()->id }});

      $('.btn-save-task').trigger('click');
    }
  });

$('#taskForm .btn-complete-task')

  function openTask(id) {
    $('#taskForm .modal-title').text("<?php echo trans('g.view_task') ?>");
    $('#taskForm .btn-save-task').show();
    $('#taskForm .btn-add-task').hide();

    var task = tasks[id];
    $('#taskForm .btn-save-task').attr('data-row', id);
    $('#taskForm .btn-complete-task').attr('data-row', id);
    $('#taskForm .btn-save-task').attr('type', 'submit');

    $('#form_task_id').val(id);

    if (task.completed_date == '') {
      $('#taskForm .btn-complete-task').show();
    } else {
      $('#taskForm .btn-complete-task').hide();
    }

<?php if (! auth()->user()->can('edit-project-task')) { ?>
<?php if (auth()->user()->can('mark-project-task-complete')) { ?>
    if (task.assigned_to_me) {
      $('[name=form_task_completed_date_field]').prop('disabled', null);
    } else {
      $('[name=form_task_completed_date_field]').prop('disabled', 1);
    }
<?php } ?>
<?php } ?>

    // Populate form
    $('#form_task_subject').val(task.subject);
    tinymce.get('form_task_description').setContent(task.description);
    $('#form_task_start_date').val(task.start_date);
    $('[name=form_task_start_date_field]').datepicker('update', task.start_date);
    $('#form_task_due_date').val(task.due_date);
    $('[name=form_task_due_date_field]').datepicker('update', task.due_date);
    $('#form_task_assigned_to_id')[0].selectize.setValue(task.assigned_to_id.split(','));

    // Set time to null before showing time
    $('#form_task_completed_date_time').val('');
    $('#form_task_completed_date').val(task.completed_date);
    $('[name=form_task_completed_date_field]').datepicker('update', task.completed_date);
    $('[name=form_task_completed_date_field]').datepicker().trigger('changeDate');
    $('#form_task_completed_by_id')[0].selectize.setValue(task.completed_by_id);
    $('#form_task_project_status_id')[0].selectize.setValue(task.project_status_id);
    $('#form_task_priority')[0].selectize.setValue(task.priority_id);

    // Set status change to false
    $('#form_task_status_changed').val(0);

    $('#taskForm').modal('show');
  }

  $('#form_task_project_status_id').on('change', function() { $('#form_task_status_changed').val('1'); });
});
</script>

                </div>
<?php } // view-project-tasks || view-personal-project-tasks ?>

<?php if (auth()->user()->can('user-view-project-proposition', $project)) { ?>
                <div class="tab-pane px-5" id="proposition" role="tabpanel" aria-labelledby="proposition-tab">

                  <div class="pt-5 pb-5">
                    <div class="row">
                      <div class="col-md-6 col-lg-4">
<?php if ($project->reference !== null) { ?>
                        {!! form_until($form, 'reference') !!}
<?php } ?>
                        {!! form_until($form, 'currency_code') !!}
<?php if (isset($project->propositions[0]) && $project->propositions[0]->proposition_valid_until !== null) { ?>
                        {!! form_until($form, 'proposition_valid_until') !!}
<?php } ?>
                      </div>
                      <div class="col-md-6 col-lg-4">
                      </div>
                      <div class="col-md-6 col-lg-4 text-right">
<?php if (auth()->user()->can('user-approve-project-proposition', $project)) { ?>
  <?php if (isset($project->propositions[0]) && $project->propositions[0]->approved === null && auth()->user()->roles[0]->id != 1) { ?>
                        <button type="button" class="btn btn-success btn-lg btn-block btn-approve-proposition"><i class="material-icons" style="position: relative; top:1px;">check_circle_outline</i> {{ trans('g.approve_proposition') }}</button>
<script>
$(function() {
  $('.btn-approve-proposition').on('click', function() {
    Swal({
      title: "{!! trans('g.approve_proposition') !!}",
      text: "{!! trans('g.approve_proposition_confirm') !!}",
      type: 'success',
      showCancelButton: true,
      confirmButtonColor: '#3085d6',
      cancelButtonColor: '#d33',
      confirmButtonText: "{!! trans('g.yes') !!}"
    }).then((result) => {
      if (result.value) {
        var jqxhr = $.ajax({
          url: "{{ url('projects/proposition/approve') }}",
          data: {id: <?php echo $project->id ?>, _token: '<?= csrf_token() ?>'},
          method: 'POST'
        })
        .done(function(data) {
          if(data === true) {
            document.location.reload();
          } else if (typeof data.msg !== 'undefined') {
            Swal(data.msg);
          }
        })
        .fail(function() {
          console.log('error');
        })
        .always(function() {
        });
      }
    });
  });
});
</script>
  <?php } ?>
<?php } ?>
<?php if (isset($project->propositions[0]) && $project->propositions[0]->approved !== null) { ?>
                        <div><span class="text-success"><i class="material-icons" style="position: relative; top:7px;">check_circle_outline</i> {{ trans('g.proposition_is_approved') }}</span></div>
  <?php if (auth()->user()->roles[0]->id == 1) { ?>
                        <div class="mt-2"><a href="javascript:void(0);" class="text-danger btn-reset-approval small">{{ trans('g.reset_approval') }}</a></div>
<script>
$(function() {
  $('.btn-reset-approval').on('click', function() {
    Swal({
      title: "{!! trans('g.reset_approval') !!}",
      text: "{!! trans('g.reset_approval_confirm') !!}",
      imageUrl: "{{ url('assets/img/icons/fe/trash-2.svg') }}",
      imageWidth: 48,
      showCancelButton: true,
      confirmButtonColor: '#3085d6',
      cancelButtonColor: '#d33',
      confirmButtonText: "{!! trans('g.yes') !!}"
    }).then((result) => {
      if (result.value) {
        var jqxhr = $.ajax({
          url: "{{ url('projects/proposition/reset-approval') }}",
          data: {id: <?php echo $project->id ?>, _token: '<?= csrf_token() ?>'},
          method: 'POST'
        })
        .done(function(data) {
          if(data === true) {
            document.location.reload();
          } else if (typeof data.msg !== 'undefined') {
            Swal(data.msg);
          }
        })
        .fail(function() {
          console.log('error');
        })
        .always(function() {
        });
      }
    });
  });
});
</script>
  <?php } ?>
<?php } ?>
                      </div>
                    </div>
                  </div>

                  <style type="text/css">
                    #table-proposition th,
                    #table-proposition td {
                      padding-left: 0;
                    }
                  </style>
                  <table class="table table-borderless table-sm mb-0 mt-5" id="table-proposition">
                    <thead>
                      <tr>
                        <th>{{ trans('g.description') }}</th>
                        <th width="90">{{ trans('g.quantity') }}</th>
                        <th width="100">{{ trans('g.unit') }}</th>
                        <th width="100">{{ trans('g.price_per_unit') }}</th>
                        <th width="100">{{ trans('g.tax') }}</th>
                        <th width="100" class="text-right">{{ trans('g.total_excl_tax') }}</th>
                      </tr>
                    </thead>
                    <tbody id="proposition-items">
                    </tbody>
                    <tbody>
                      <tr>
                        <td colspan="6">&nbsp;</td>
                      </tr>
                    </tbody>
                    <tbody id="proposition-sub">
                      <tr>
                        <td colspan="3"></td>
                        <td colspan="2" class="text-left align-middle">{{ trans('g.subtotal_excl_taxes') }}</td>
                        <td class="text-right align-middle proposition-total-ex-tax"></td>
                      </tr>
                    </tbody>
                    <tbody id="proposition-totals-discount">
                    </tbody>
                    <tbody id="proposition-totals-taxes">
                    </tbody>
                    <tbody>
                      <tr>
                        <td colspan="3"></td>
                        <td colspan="2" class="text-left align-middle font-weight-bold border-top border-bottom">{{ trans('g.total') }} <span class="currency_code"></span></td>
                        <td class="text-right align-middle proposition-total font-weight-bold border-top border-bottom"></td>
                      </tr>
                      <tr>
                        <td colspan="3"></td>
                        <td colspan="2" class="text-left align-middle"><small>{{ trans('g.total_taxes') }} <span class="currency_code"></span> <span class="proposition-total-taxes"></span></small></td>
                        <td></td>
                      </tr>
                      <tr>
                        <td colspan="6">&nbsp;</td>
                      </tr>
                    </tbody>
                  </table>

                </div>

<script>
var item_row_id = 1;
var item_discount_row_id = 1;
var item_tax_row_id = 1;

function formatCurrency(amount) {
  return currency(amount, { precision: "{{ auth()->user()->getDecimals() }}", separator: "{{ auth()->user()->getThousandsSep() }}", decimal: "{{ auth()->user()->getDecimalSep() }}", formatWithSymbol: false }).format();
}

$(function() {
<?php
// Add existing items
if (count($project->propositions) > 0) {
?>
  var source = document.getElementById('proposition-item').innerHTML;
  var template = Handlebars.compile(source);
<?php
  foreach($project->propositions->last()->items as $item) {
?>
    var context = {
      id: item_row_id,
      type: '<?php echo $item->type; ?>',
      description: "<?php echo str_replace('"', '&quot;', $item->description); ?>",
      quantity: "<?php echo $item->quantity; ?>",
      unit: "<?php echo $item->unit; ?>",
      unit_price: "<?php if ($item->unit_price !== null) echo $item->unit_price / 100; ?>",
      tax_rate: "<?php echo $item->tax_rate; ?>",
      discount_type: "<?php echo $item->discount_type; ?>",
      total: formatCurrency(0)
    };

    var html = template(context);
    $(html).appendTo($('#proposition-items'));
    item_row_id++;
<?php
  }
?>
  setTimeout(calculateEstimate, 500);
<?php
}
?>
  setCurrencyCode();

  $('.proposition-total-ex-tax').text(formatCurrency(0));
  $('.proposition-total').text(formatCurrency(0));

  function addItem() {
    var source = document.getElementById('proposition-item').innerHTML;
    var template = Handlebars.compile(source);

    var context = {
      id: item_row_id,
      type: "item",
      description: "",
      quantity: "",
      unit: "<?php echo \Platform\Models\Core\Unit::getDefault(); ?>",
      unit_price: "",
      tax_rate: "<?php echo \Platform\Models\Core\TaxRate::getDefault(); ?>",
      discount_type: "currency",
      total: formatCurrency(0)
    };

    var html = template(context);
    $(html).appendTo($('#proposition-items'));
    calculateEstimate();
    item_row_id++;
  }

  // Change currency code
  $('#currency_code').on('change', setCurrencyCode);

  function setCurrencyCode() {
    $('.currency_code').text($('#currency_code').val());
  }

  function calculateEstimate() {
    var sub_total = 0;
    var tax_total = 0;
    var grand_total = 0;
    var discount_total = 0;

    var taxes = [];

    // Calculate all rows
    $('.item-row').each(function() {
      var type = $(this).find('.select-type').val();
      var quantity = $(this).find('.input-quantity').val();
      var unit_price = $(this).find('.input-unit_price').val();
      var tax_rate = $(this).find('.select-tax').val();
      var discount_unit = $(this).find('.select-discount-unit').val();
      var $row_total = $(this).find('.row-total');

      if (type == 'item') {
        if (quantity != '' && unit_price != '' && tax_rate != '') {
          var row_total_excl_taxes = currency(unit_price).multiply(quantity);
          var tax = currency(row_total_excl_taxes).multiply(tax_rate / 10000);

          sub_total = currency(sub_total).add(row_total_excl_taxes);
          tax_total = currency(tax_total).add(tax);
          grand_total = currency(grand_total).add(currency(row_total_excl_taxes).add(tax));

          $row_total.text(formatCurrency(row_total_excl_taxes));
          taxes.push({rate: (tax_rate / 100), of: row_total_excl_taxes, amount: tax});
        } else {
          // Set total to 0
          $row_total.text(formatCurrency(0));
        }
      }
    });

    // Calculate discount
    $('.item-row').each(function() {
      var type = $(this).find('.select-type').val();
      var quantity = $(this).find('.input-quantity').val();
      var tax_rate = $(this).find('.select-tax').val();
      var discount_unit = $(this).find('.select-discount-unit').val();
      var $row_total = $(this).find('.row-total');

      if (type == 'discount') {
        if (quantity != '' && tax_rate != '') {
          if (discount_unit == '%') {
            var row_total_excl_taxes = currency(sub_total).multiply(quantity / 100);
          } else {
            var row_total_excl_taxes = currency(quantity);
          }

          var tax = currency(row_total_excl_taxes).multiply(tax_rate / 10000);

          discount_total = currency(discount_total).add(row_total_excl_taxes);
          tax_total = currency(tax_total).subtract(tax);
          grand_total = currency(grand_total).subtract(currency(row_total_excl_taxes).add(tax));

          $row_total.text(formatCurrency(row_total_excl_taxes));
          
          taxes.push({rate: (tax_rate / 100), of: -row_total_excl_taxes, amount: -tax});
        } else {
          // Set total to 0
          $row_total.text(formatCurrency(0));
        }
      }
    });

    // Add discount row, first remove existing
    $('#proposition-totals-discount').html('');

    if (discount_total > 0) {
      var source = document.getElementById('proposition-item-discount').innerHTML;
      var template = Handlebars.compile(source);

      var context = {
        total: formatCurrency(-discount_total)
      };

      var html = template(context);
      $(html).appendTo($('#proposition-totals-discount'));
      item_tax_row_id++;
    }

    // Add tax row(s), first remove existing
    $('#proposition-totals-taxes').html('');

    // Item tax
    var combined_taxes = [];

    for (var i in taxes) {
      var tax = taxes[i];

      var combined_tax = combined_taxes[tax.rate];

      if (typeof combined_tax === 'undefined') {
        combined_taxes[tax.rate] = {rate: tax.rate, of: tax.of, amount: tax.amount};
      } else {
        var of = currency(combined_tax.of).add(tax.of);
        var amount = currency(combined_tax.amount).add(tax.amount);

        combined_taxes[tax.rate] = {rate: combined_tax.rate, of: of, amount: amount};
      }
    }

    for (var i in combined_taxes) {
      var source = document.getElementById('proposition-item-tax').innerHTML;
      var template = Handlebars.compile(source);

      var context = {
        id: item_tax_row_id,
        rate: formatCurrency(combined_taxes[i].rate),
        of: formatCurrency(combined_taxes[i].of),
        total: formatCurrency(combined_taxes[i].amount)
      };

      var html = template(context);
      $(html).appendTo($('#proposition-totals-taxes'));
      item_tax_row_id++;
    }

    // Update totals
    $('.proposition-total-ex-tax').text(formatCurrency(sub_total));
    $('.proposition-total-taxes').text(formatCurrency(tax_total));
    $('.proposition-total').text(formatCurrency(grand_total));

    // Set currency
    setCurrencyCode();
  }
});
</script>

<script id="proposition-item" type="text/x-handlebars-template">
  <tr id="item-row-@{{ id }}" class="item-row">
    <td class="align-middle">
      <input type="hidden" name="proposition_type[]" class="select-type" value="@{{ type }}">
      <input class="form-control" type="text" value="@{{ description }}" maxlength="150" name="proposition_description[]" disabled>
    </td>
    <td class="align-middle">
      <input class="form-control text-right input-quantity" type="number" min="-100000" max="100000" value="@{{ quantity }}" name="proposition_quantity[]" disabled>
    </td>
    <td class="align-middle">
      <select class="form-control select-unit" name="proposition_unit[]" @{{#ifvalue type value="discount"}} style="display:none"@{{/ifvalue}} disabled>
        <option value=""></option>
<?php
$units = \Platform\Models\Core\Unit::orderBy('name', 'asc')->get();

foreach ($units as $unit) {
?>
        <option value="{{ $unit->name }}"@{{#ifvalue unit value="<?php echo $unit->name; ?>"}} selected@{{/ifvalue}}>{{ __($unit->name) }}</option>
<?php } ?>
      </select>

      <select class="form-control select-discount-unit" name="proposition_discount_unit[]" @{{#ifvalue type value="item"}} style="display:none"@{{/ifvalue}} disabled>
        <option value="currency" class="currency_code"@{{#ifvalue discount_type value="currency"}} selected@{{/ifvalue}}></option>
        <option value="%"@{{#ifvalue discount_type value="%"}} selected@{{/ifvalue}}>%</option>

      </select>
    </td>
    <td class="align-middle">
      <input class="form-control text-right input-unit_price" name="proposition_unit_price[]" disabled type="number" min="-10000" max="10000" value="@{{ unit_price }}" @{{#ifvalue type value="discount"}} style="display:none"@{{/ifvalue}}></td>
    <td class="align-middle">
      <select class="form-control select-tax" name="proposition_tax_rate[]" disabled>
        <option value=""></option>
<?php
$tax_rates = \Platform\Models\Core\TaxRate::orderBy('rate', 'asc')->get();

foreach ($tax_rates as $rate) {
?>
        <option value="{{ $rate->rate }}"@{{#ifvalue tax_rate value="<?php echo $rate->rate; ?>"}} selected@{{/ifvalue}}>{{ $rate->percentage }}</option>
<?php } ?>
      </select>
    </td>
    <td class="text-right align-middle row-total">
      @{{ total }}
    </td>
  </tr>
</script>

<script id="proposition-item-discount" type="text/x-handlebars-template">
  <tr class="item-discount-row">
    <td colspan="3"></td>
    <td colspan="2" class="text-left align-middle"><?php echo trans('g.discount') ?></td>
    <td class="text-right align-middle">@{{ total }}</td>
  </tr>
</script>

<script id="proposition-item-tax" type="text/x-handlebars-template">
  <tr id="item-tax-row-@{{ id }}" class="item-tax-row">
    <td colspan="3"></td>
    <td colspan="2" class="text-left align-middle"><?php echo trans('g.tax_of_row', ['percentage' => '{{ rate }}', 'amount' => '{{ of }}', 'currency' => '<span class="currency_code"></span>']) ?></td>
    <td class="text-right align-middle">@{{ total }}</td>
  </tr>
</script>
<?php } // view-project-proposition ?>

<?php if (auth()->user()->can('view-and-upload-all-project-files') || auth()->user()->can('user-view-and-upload-personal-project-files', $project)) { ?>
                <div class="tab-pane p-0" id="files" role="tabpanel" aria-labelledby="files-tab">
                  <div id="elfinder"></div>
                </div>
<?php } // view-and-upload-all-project-files || view-and-upload-personal-project-files ?>

<?php if (auth()->user()->can('user-view-project-comments', $project) || auth()->user()->can('create-comment')) { ?>
                <div class="tab-pane p-5" id="comments" role="tabpanel" aria-labelledby="comments-tab">

                <div class="card">
                  <div class="card-header">
                    <h3 class="card-title">{{ trans('g.people_in_conversation') }}</h3>
                  </div>
                  <div class="card-body p-4">
<?php
// People in conversation
$users = collect();

if ($project->client !== null) {
  $users = $users->merge($project->client->users()->get());
}

// Get managers
if ($project->managers !== null) {
  $users = $users->merge($project->managers);
}

// Get task(s) assignee
foreach($project->tasks as $task) {
  $users = $users->merge($task->assignees);
}

$users = $users->unique('id');

$users = $users->sortBy(function ($user, $key) {
  return ! $user->active . ' ' . $user->roles->pluck('id')->min() . ' ' . $user->name;
}, SORT_FLAG_CASE);

foreach ($users as $user) {
  if ($user->can('user-view-project-comments', $project) && $user->active) {
    echo '<div class="float-left m-2">' . $user->getAvatarHtml() . '</div>';
  }
}
?>
                  </div>
                </div>

<?php if ($project->totalCommentsCount() > 0) { ?>
<?php foreach ($project->comments as $comment) { ?>
                <div class="card">
                  <div class="card-body">
                    <div class="media">
                      <div class="mr-3">
                        {!! $comment->commented->getAvatarHtml() !!}
                      </div>
                      <div class="media-body">
                        <h5 class="mt-0 small">{{ $comment->commented->name }} <small class="text-muted">{{ $comment->created_at->diffForHumans() }}</small></h5>
                        {!! $comment->comment !!}
                      </div>
                    </div>
                  </div>
                </div>
<?php } ?>
<?php } ?>

<?php if (auth()->user()->can('create-comment')) { ?>
<div class="row">
  <div class="col-12">
    <div class="form-group">
      <textarea class="form-control" id="add_comment" rows="4"></textarea>
    </div>
    <button type="button" class="btn btn-primary btn-add-comment"><i class="material-icons mr-1" style="vertical-align: -4px;">insert_comment</i> {{ trans('g.add_comment') }}</button>
  </div>
</div>
<script>
$(function() {
  $('.btn-add-comment').on('click', function() {
    var $btn = $(this);
    $btn.prop('disabled', 1);
    $('#add_comment').removeClass('is-invalid');
    var comment = $('#add_comment').val();
    comment = comment.replace(/(?:\r\n|\r|\n)/g, '<br>');

    if (comment != '') {

      var jqxhr = $.ajax({
        url: "{{ url('comments/add') }}",
        data: {comment: comment, type: 'project', id: {{ $project->id }},_token: '<?= csrf_token() ?>'},
        method: 'POST'
      })
      .done(function(data) {
        document.location.reload();
      })
      .fail(function() {
        $btn.prop('disabled', null);
        alert('An error occurred. Please reload this page.');
      });
    } else {
      $('#add_comment').addClass('is-invalid');
      $('#add_comment').focus();
      $btn.prop('disabled', null);
    }
  });
});
</script>
<?php } // create-comment ?>
                </div>
<?php } // user-view-project-comments || create-comment ?>
              </div>

            </div>

            <div class="card-footer text-right">
                <button class="btn btn-secondary" type="button" onclick="document.location = '{{ url('projects') }}';">{{ trans('g.all_projects') }}</button>
<?php if ($can_access_project) { ?>
<?php if (auth()->user()->can('edit-project')) { ?>
              <a href="{{ url('projects/edit/' . $sl) }}" class="btn btn-primary ml-1 tab-hash">{{ trans('g.edit_project') }}</a>
<?php } ?>
<?php } ?>
            </div>
          </div>

          {!! form_end($form) !!}

        </div>

      </div>
    </div>
  </div>
<?php
include base_path() . '/resources/views/app/projects/includes/project-task-form.php';
?>
@stop

@section('page_bottom')
<style type="text/css">
  .reorder-rows .ui-sortable-helper {
    background-color: #fff;
    display: table;
    box-shadow: 0 3px 6px rgba(0, 0, 0, 0.16), 0 3px 6px rgba(0, 0, 0, 0.23);
  }
  .reorder-rows .reorder-rows-placeholder {
    background-color: #f5f5f5;
    box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.12), inset 0 1px 2px rgba(0, 0, 0, 0.24);
    height: 47px;
  }
</style>


<script>
$('.selectize-project-statuses').selectize({
  render: {
    option: function (data, escape) {
      return '<div>' +
      '<span style=\'display:inline-block;position:relative;top:-1px;border-radius:50%;width:8px;height:8px;margin-right:8px;background-color:' + data.color + '\'></span>' +
      '<span class="title">' + escape(data.text) + '</span>' +
      '</div>';
    },
  item: function (data, escape) {
    return '<div>' +
      '<span style=\'display:inline-block;position:relative;top:-1px;border-radius:50%;width:8px;height:8px;margin-right:8px;background-color:' + data.color + '\'></span>' +
      escape(data.text) +
      '</div>';
    }
  }
});
</script>

<script id="task-item" type="text/x-handlebars-template">
  <thead class="bg-light">
    <tr>
      <th width="120">{{ trans('g.status') }}</th>
      <th>{{ trans('g.task') }}</th>
      <th width="120">{{ trans('g.due_date') }}</th>
      <th>{{ trans('g.assigned_to') }}</th>
      <th width="104"></th>
    </tr>
  </thead>
  <tbody>
  <tr id="task-row-@{{ id }}" class="task-row">
    <td class="align-middle text-truncate task-priority small">
      <textarea name="task_new[]" style="display: none">@{{ task_new }}</textarea>
      <textarea name="task_changed[]" style="display: none">@{{ task_changed }}</textarea>
      <textarea name="task_project_status_id[]" style="display: none">@{{ project_status_id }}</textarea>
      <span style="max-width:120px" class="d-block text-truncate">@{{{ project_status }}}</span>
    </td>
    <td class="align-middle task-subject">
      <textarea name="task_priority[]" style="display: none">@{{ priority_id }}</textarea>
      <textarea name="task_subject[]" style="display: none">@{{ subject }}</textarea>
      <textarea name="task_description[]" style="display: none">@{{{ description }}}</textarea>
      @{{ subject }}
    </td>
    <td class="align-middle task-start_date small">
      <textarea name="task_start_date[]" style="display: none">@{{ start_date }}</textarea>
      <textarea name="task_due_date[]" style="display: none">@{{ due_date }}</textarea>
      @{{ due_date_friendly }}
    </td>
    <td class="align-middle">
      <span class="task-assigned_to">@{{{ assigned_to_html }}}</span>
      <textarea name="task_assigned_to_id[]" style="display: none">@{{ assigned_to_id }}</textarea>
    </td>
    <td class="text-right align-middle" width="64">
      <a href="javascript:void(0);" class="btn btn-primary rounded-0 btn-view-task" data-row="@{{ id }}"><i class="material-icons">search</i></a>
    </td>
  </tr>
  <tr id="task-row-@{{ id }}" class="task-row">
    <td class="align-middle" colspan="5">
    @{{{ description }}}
    </td>
  </tr>
  </tbody>
</script>
@stop