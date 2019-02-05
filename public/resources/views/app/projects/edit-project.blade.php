@extends('../../layouts.app')

@section('page_title', trans('g.edit_project') . ' - ' . $project->name . ' - ' . config('system.name'))

@section('page_head')
  <link rel="stylesheet" href="{{ url('assets/css/wysiwyg.css?' . config('system.client_side_timestamp')) }}">
  <script src="{{ url('assets/js/wysiwyg.js?' . config('system.client_side_timestamp')) }}"></script>

@include('layouts.modules.elfinder-init')

<script type="text/javascript">
  $(function() {

    $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
      if ($(e.target).attr('id') == 'files-tab') {
        $('#elfinder').trigger('resize');
      }
    });

    var submitted = false;

    $("form").submit(function() {
      submitted = true;
    });

    $(window).bind('beforeunload', function() {
      if (! submitted && ($('#form_changes_detected').val() == '1' || $('#task_changes_detected').val() == '1')) {
        return true;
      }
    });

    $('body').on('keyup change paste', 'input, select, textarea', function(){
      $('#form_changes_detected').val('1');
    });

    $('#taskForm').on('keyup change paste', 'input, select, textarea', function(){
      $('#task_changes_detected').val('1');
    });
  });
</script>
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

          <input type="hidden" name="form_changes_detected" id="form_changes_detected" value="0">
          <input type="hidden" name="task_changes_detected" id="task_changes_detected" value="0">

          <div class="card">
            <div class="card-header">
              <h3 class="card-title"><span class="text-muted d-none d-md-inline">{{ $project->client->active_name }} -</span> {{ $project->name }}</h3>
              <div class="card-options">
                <span class="text-muted small">{!! $project->status->bullet_name !!}</span>
              </div>
            </div>

            <div class="card-body p-0">

              <ul class="nav nav-tabs mx-0" role="tablist">
                <li class="nav-item pl-5">
                  <a class="nav-link" id="general-tab" data-toggle="tab" href="#general" role="tab" aria-selected="false">{{ trans('g.general') }}</a>
                </li>
                <li class="nav-item">
                  <a class="nav-link" id="description-tab" data-toggle="tab" href="#project-description" role="tab" aria-selected="false">{{ trans('g.description') }}</a>
                </li>
                <li class="nav-item">
                  <a class="nav-link" id="tasks-tab" data-toggle="tab" href="#tasks" role="tab" aria-selected="false">{{ trans('g.tasks') }}</a>
                </li>
                <li class="nav-item">
                  <a class="nav-link" id="proposition-tab" data-toggle="tab" href="#proposition" role="tab" aria-selected="false">{{ trans('g.proposition') }}</a>
                </li>
                <li class="nav-item">
                  <a class="nav-link" id="files-tab" data-toggle="tab" href="#files" role="tab" aria-selected="false">{{ trans('g.files') }}</a>
                </li>
              </ul>

              <div class="tab-content">
                <div class="tab-pane px-5 pt-5 pb-3" id="general" role="tabpanel" aria-labelledby="general-tab">
                  <div class="row">
                    <div class="col-md-6 col-lg-4">
                      {!! form_until($form, 'completed_date') !!}
                    </div>

                    <div class="col-md-6 col-lg-4">
                      {!! form_until($form, 'completed_by_id') !!}
                    </div>

                    <div class="col-md-6 col-lg-4">
                      {!! form_until($form, 'notify_people_involved') !!}
                    </div>
                  </div>
                </div>

                <div class="tab-pane px-5 pt-5 pb-5" id="project-description" role="tabpanel" aria-labelledby="description-tab">
                  {!! form_until($form, 'description') !!}
                </div>

                <div class="tab-pane p-5" id="tasks" role="tabpanel" aria-labelledby="tasks-tab">

                  <div class="card mb-0">
                    <table class="table table-hover table-striped table-outline table-vcenter text-nowrap card-table table-sm mb-0" id="table-tasks">
                      <thead class="bg-light">
                        <tr>
                          <th width="36"></th>
                          <th width="120">{{ trans('g.status') }}</th>
                          <th>{{ trans('g.task') }}</th>
                          <th>{{ trans('g.assigned_to') }}</th>
                          <th width="120">{{ trans('g.due_date') }}</th>
                          <th width="120">{{ trans('g.completed') }}</th>
                          <th width="104"></th>
                        </tr>
                      </thead>
                      <tbody id="task-items" class="reorder-rows">

                      </tbody>
                      <tbody>
                        <tr>
                          <td colspan="7" style="padding: 0">
                            <button type="button" class="btn btn-lg btn-block btn-success rounded-0 btn-add-task">{{ trans('g.add_new_task') }}</button>
                          </td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
<script>
var task_row_id = 1;
var priorities = ['<?php echo implode ("', '", trans('g.status_code')); ?>'];

$(function() {
<?php
// Add tasks
if (count($project->tasks) > 0) {
?>
  var source = document.getElementById('task-item').innerHTML;
  var template = Handlebars.compile(source);
<?php
  foreach($project->tasks as $task) {
?>
  var html = template({
    id: task_row_id,
    task_changed: 0,
    task_new: 0,
    subject: "<?php echo str_replace('"', '&quot;', $task->subject) ?>",
    description: "<?php echo str_replace(PHP_EOL, '', str_replace('"', '&quot;', $task->description)) ?>",
    due_date_friendly: <?php echo ($task->due_date != null) ? "moment('" . $task->due_date . "').format('MMM Do YYYY')" : "'-'";  ?>,
    start_date: '<?php echo $task->start_date ?>',
    due_date: '<?php echo $task->due_date ?>',
    completed_date: '<?php echo $task->completed_date ?>',
    completed_date_friendly: <?php echo ($task->completed_date != null) ? "moment('" . $task->completed_date . "').format('MMM Do YYYY')" : "'-'";  ?>,
    completed_by_id: '<?php echo $task->completed_by_id ?>',
    project_status_id: '<?php echo $task->project_status_id ?>',
    project_status: statuses['<?php echo $task->project_status_id ?>'],
    priority_id: <?php echo $task->priority ?>,
    priority: priorities[<?php echo $task->priority ?>],
    assigned_to_id: '<?php echo trim(implode($task->assignees()->get()->pluck('id')->toArray(), ','), ','); ?>',
    assigned_to_html: getUserAvatars('<?php echo trim(implode($task->assignees()->get()->pluck('id')->toArray(), ','), ','); ?>')
  });
  $(html).appendTo($('#task-items'));

  task_row_id++;
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

  // Open add task modal
  $('#table-tasks .btn-add-task').on('click', function() {
    resetTaskForm();
    $('#taskForm .modal-title').text("<?php echo trans('g.new_task') ?>");
    $('#taskForm .btn-save-task').hide();
    $('#taskForm .btn-add-task').show();

    $('#taskForm').modal('show');
    setTimeout(function() { $('#form_task_subject').focus(); }, 500);
  });

  // Open update task modal
  $('#task-items').on('click', '.btn-edit-task', function() {
    $('#taskForm .modal-title').text("<?php echo trans('g.edit_task') ?>");
    $('#taskForm .btn-save-task').show();
    $('#taskForm .btn-add-task').hide();

    var row_id = $(this).attr('data-row');
    $('#taskForm .btn-save-task').attr('data-row', row_id);

    // Populate form
    var $row = $('#task-row-' + row_id);

    $('#form_task_subject').val($row.find('[name="task_subject[]"]').val());
    tinymce.get('form_task_description').setContent($row.find('[name="task_description[]"]').val());
    $('#form_task_start_date').val($row.find('[name="task_start_date[]"]').val());
    $('[name=form_task_start_date_field]').datepicker('update', $row.find('[name="task_start_date[]"]').val());
    $('#form_task_due_date').val($row.find('[name="task_due_date[]"]').val());
    $('[name=form_task_due_date_field]').datepicker('update', $row.find('[name="task_due_date[]"]').val());
    $('#form_task_assigned_to_id')[0].selectize.setValue($row.find('[name="task_assigned_to_id[]"]').val().split(','));

    // Set time to null before showing time
    $('#form_task_completed_date_time').val('');
    $('#form_task_completed_date').val($row.find('[name="task_completed_date[]"]').val());
    $('[name=form_task_completed_date_field]').datepicker('update', $row.find('[name="task_completed_date[]"]').val());
    $('[name=form_task_completed_date_field]').datepicker().trigger('changeDate');
    $('#form_task_completed_by_id')[0].selectize.setValue($row.find('[name="task_completed_by_id[]"]').val());
    $('#form_task_project_status_id')[0].selectize.setValue($row.find('[name="task_project_status_id[]"]').val());
    $('#form_task_priority')[0].selectize.setValue($row.find('[name="task_priority[]"]').val());

    $('#taskForm').modal('show');
  });

  // Delete task
  $('#task-items').on('click', '.btn-delete-task', function() {
    var row_id = $(this).attr('data-row');
    $('#task-row-' + row_id).remove();
  });

  // Add task
  $('#taskForm .btn-add-task').on('click', addTask);

  // Update task
  $('#taskForm .btn-save-task').on('click', function() {
    var row_id = $(this).attr('data-row');

    updateTask(row_id);
  });

  function addTask() {
    var source = document.getElementById('task-item').innerHTML;
    var template = Handlebars.compile(source);

    var subject = $('#form_task_subject').val();
    var description = tinymce.get('form_task_description').getContent();
    var start_date = $('#form_task_start_date').val();
    var due_date = $('#form_task_due_date').val();
    var priority_id = $('#form_task_priority').val();
    var priority = priorities[priority_id];
    var assigned_to_id = $('#form_task_assigned_to_id').val();
    var assigned_to = $('#form_task_assigned_to_id')[0].selectize;
    var user = assigned_to.options[assigned_to_id];
    var project_status_id = $('#form_task_project_status_id').val();
    var project_status = $('#form_task_project_status_id')[0].selectize;
    project_status = project_status.getItem(project_status.getValue())[0].innerHTML;
    var completed_date = $('#form_task_completed_date').val();
    var completed_by_id = $('#form_task_completed_by_id').val();

    if (subject == '') {
      $('#form_task_subject').addClass('is-invalid');
      $('#form_task_subject').focus();
      return;
    } else {
      $('#form_task_subject').removeClass('is-invalid');
    }

    $('#taskForm').modal('hide');

    var context = {
      id: task_row_id,
      task_changed: 0,
      task_new: 1,
      subject: subject,
      description: description,
      due_date_friendly: (due_date != '') ? moment(due_date).format('MMM Do YYYY') : '-',
      start_date: start_date,
      due_date: due_date,
      project_status_id: project_status_id,
      project_status: project_status,
      priority_id: priority_id,
      priority: priority,
      completed_date: completed_date,
      completed_date_friendly: (completed_date != '') ? moment(completed_date).format('MMM Do YYYY') : '-',
      completed_by_id: completed_by_id,
      assigned_to_id: assigned_to_id + "",
      assigned_to_html: getUserAvatars(assigned_to_id + "")
    };

    var html = template(context);
    $(html).appendTo($('#task-items'));

    task_row_id++;

    resetTaskForm();
  }

  function updateTask(row_id) {
    var source = document.getElementById('task-item').innerHTML;
    var template = Handlebars.compile(source);

    var subject = $('#form_task_subject').val();
    var description = tinymce.get('form_task_description').getContent();
    var start_date = $('#form_task_start_date').val();
    var due_date = $('#form_task_due_date').val();
    var priority_id = $('#form_task_priority').val();
    var priority = priorities[priority_id];
    var assigned_to_id = $('#form_task_assigned_to_id').val();
    var assigned_to = $('#form_task_assigned_to_id')[0].selectize;
    var user = assigned_to.options[assigned_to_id];
    var project_status_id = $('#form_task_project_status_id').val();
    var project_status = $('#form_task_project_status_id')[0].selectize;
    project_status = project_status.getItem(project_status.getValue())[0].innerHTML;
    var completed_date = $('#form_task_completed_date').val();
    var completed_by_id = $('#form_task_completed_by_id').val();

    if (subject == '') {
      $('#form_task_subject').addClass('is-invalid');
      $('#form_task_subject').focus();
      return;
    } else {
      $('#form_task_subject').removeClass('is-invalid');
    }

    $('#taskForm').modal('hide');

    var context = {
      id: row_id,
      task_changed: 1,
      task_new: 0,
      subject: subject,
      description: description,
      due_date_friendly: (due_date != '') ? moment(due_date).format('MMM Do YYYY') : '-',
      start_date: start_date,
      due_date: due_date,
      project_status_id: project_status_id,
      project_status: project_status,
      priority_id: priority_id,
      priority: priority,
      completed_date: completed_date,
      completed_date_friendly: (completed_date != '') ? moment(completed_date).format('MMM Do YYYY') : '-',
      completed_by_id: completed_by_id,
      assigned_to_id: assigned_to_id + "",
      assigned_to_html: getUserAvatars(assigned_to_id + "")
    };

    var html = template(context);
    $('#task-items tr#task-row-' + row_id).replaceWith($(html));

    resetTaskForm();
  }

  function resetTaskForm() {
    // Reset form
    $('#form_task_subject').val('');
    tinymce.get('form_task_description').setContent('');
    $('[name=form_task_start_date_field]').val('').datepicker('update');
    $('#form_task_start_date').val('');
    $('[name=form_task_due_date_field]').val('').datepicker('update');
    $('#form_task_due_date').val('');
    $('#form_task_project_status_id')[0].selectize.setValue(<?php echo \Platform\Models\ProjectStatus::getDefaultTask(); ?>);
    $('#form_task_priority')[0].selectize.setValue(1);
    $('#form_task_assigned_to_id').val('');
    $('#form_task_assigned_to_id')[0].selectize.clear();

    $('#form_task_completed_date').val('');
    $('#form_task_completed_date_time').val('');
    $('[name=form_task_completed_date_field]').val('').datepicker('update');
    $('[name=form_task_completed_date_field]').datepicker().trigger('changeDate');

    $('#form_task_completed_by_id').val('');
    $('#form_task_completed_by_id')[0].selectize.clear();

    $('[data-toggle="popover"]').popover({
      boundary: 'window',
      trigger: 'hover',
      html: true
    });
  }
});

function getUserAvatars(ids) {
  var ret = '';
  if (typeof ids === 'undefined' || ids === '') return ret;

  var assignee = ids.split(',');
  for (var i in assignee) {
    if (typeof assignees[assignee[i]] !== 'undefined') {
      var user = assignees[assignee[i]];

      var title = (user.active == 0) ? ' (<?php echo strtolower(trans('g.inactive')) ?>)' : '';

      ret += '<div class="m-1" style=\'border-radius:50%;display: inline-block;border: 2px solid ' + user.role_color + ';\'>';
      ret += '<div class="avatar" style="background-image: url(' + user.avatar + '); border: 1px solid #fff;" data-placement="top" data-title="' + 
        '<div class=\'text-center\'><span style=\'display:inline-block;position:relative;top:-1px;border-radius:50%;width:8px;height:8px;margin-right:2px;background-color:' + user.role_color + '\'></span> ' + user.role + title + '</div>' + 
        '" data-content="' + 
        '<div class=\'text-center\'><img src=\'' + user.avatar + '\' class=\'avatar avatar-xxl\'></div>' + 
        '<div class=\'text-center mt-2\'>' + user.name + '</div>';
      if (user.job_title !== null) {
        ret += '<div class=\'text-center text-muted mt-1\'>' + user.job_title + '</div>';
      }
      ret += '<div class=\'text-center mt-2\'><i class=\'material-icons\' style=\'font-size:12px; position:relative; top: 2px\'>alternate_email</i> ' + user.email + '</div>';

      if (user.phone != null) {
        ret += '<div class=\'text-center mt-1\'><i class=\'material-icons\' style=\'font-size:12px; position:relative; top: 1px\'>phone</i> ' + user.phone + '</div>';
      }

      ret += '" data-toggle="popover">';
      if (user.recently_online) ret += '<span class="avatar-status bg-green"></span>';
      ret += '</div>';
      ret += '</div>';

    }
  }
  return ret;
}
</script>


                </div>

                <div class="tab-pane p-5" id="proposition" role="tabpanel" aria-labelledby="proposition-tab">
                  <div class="pb-3">
                    <div class="row">
                      <div class="col-md-6 col-lg-4">
                        {!! form_until($form, 'reference') !!}
                      </div>
                      <div class="col-md-6 col-lg-4">
                      </div>
                      <div class="col-md-6 col-lg-4">
                        {!! form_until($form, 'currency_code') !!}
                        {!! form_until($form, 'proposition_valid_until') !!}
                      </div>
                    </div>
                  </div>

                  <table class="table table-borderless table-sm mb-0 mt-5" id="table-proposition">
                    <thead>
                      <tr>
                        <th width="36"></th>
                        <th width="120">{{ trans('g.type') }}</th>
                        <th>{{ trans('g.description') }}</th>
                        <th width="90">{{ trans('g.quantity') }}</th>
                        <th width="100">{{ trans('g.unit') }}</th>
                        <th width="100">{{ trans('g.price_per_unit') }}</th>
                        <th width="100">{{ trans('g.tax') }}</th>
                        <th width="100" class="text-right">{{ trans('g.total_excl_tax') }}</th>
                        <th width="52"></th>
                      </tr>
                    </thead>
                    <tbody id="proposition-items" class="reorder-rows">
                    </tbody>
                    <tbody>
                      <tr>
                        <td colspan="9">
                          <button type="button" class="btn btn-success btn-block btn-lg btn-add-item rounded-0 my-1">{{ trans('g.add_new_line') }}</button>
                        </td>
                      </tr>
                    </tbody>
                    <tbody id="proposition-sub">
                      <tr>
                        <td colspan="5"></td>
                        <td colspan="2" class="text-left align-middle">{{ trans('g.subtotal_excl_taxes') }}</td>
                        <td class="text-right align-middle proposition-total-ex-tax"></td>
                        <td></td>
                      </tr>
                    </tbody>
                    <tbody id="proposition-totals-discount">
                    </tbody>
                    <tbody id="proposition-totals-taxes">
                    </tbody>
                    <tbody>
                      <tr>
                        <td colspan="5"></td>
                        <td colspan="2" class="text-left align-middle font-weight-bold border-top border-bottom">{{ trans('g.total') }} <span class="currency_code"></span></td>
                        <td class="text-right align-middle proposition-total font-weight-bold border-top border-bottom"></td>
                        <td></td>
                      </tr>
                      <tr>
                        <td colspan="5"></td>
                        <td colspan="2" class="text-left align-middle"><small>{{ trans('g.total_taxes') }} <span class="currency_code"></span> <span class="proposition-total-taxes"></span></small></td>
                        <td></td>
                        <td></td>
                      </tr>
                    </tbody>
                  </table>

                </div>

                <div class="tab-pane" id="files" role="tabpanel" aria-labelledby="files-tab">

                  <div id="elfinder"></div>

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
<?php
include base_path() . '/resources/views/app/projects/includes/project-task-form.php';
?>
@stop

@section('page_bottom')

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

  $('#proposition-items').on('keypress', 'input', function(e) {
    if (e.keyCode == 13) {
      $('.btn-add-item').trigger('click');
      return false;
    }
  });

  setCurrencyCode();

  $('.proposition-total-ex-tax').text(formatCurrency(0));
  $('.proposition-total').text(formatCurrency(0));

  $('.btn-add-item').on('click', addItem);
  $('.btn-add-item').on('change', calculateEstimate);

  $('#proposition-items').on('change', '.select-type', changeRowType);

  $('#proposition-items').on('change keydown', '.select-type,.input-quantity,.input-unit_price,.select-discount-unit,.select-tax', calculateEstimate);

  $('#proposition-items').on('click', '.btn-delete-row', function() {
    var row_id = $(this).attr('data-row');
    $('#' + row_id).remove();
    calculateEstimate();
  });

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

  function changeRowType() {
    var type = $(this).val();
    var row_id = $(this).attr('data-row-id');

    if (type == 'item') {
      $('#' + row_id).find('.select-unit').show();
      $('#' + row_id).find('.input-unit_price').show();
      $('#' + row_id).find('.select-discount-unit').hide();
    } else if (type == 'discount') {
      $('#' + row_id).find('.select-unit').hide();
      $('#' + row_id).find('.input-unit_price').hide();
      $('#' + row_id).find('.select-discount-unit').show();
    }
  }
});
</script>


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

<script id="proposition-item" type="text/x-handlebars-template">
  <tr id="item-row-@{{ id }}" class="item-row">
    <td class="align-middle">
      <a href="javascript:void(0);" style="margin:7px 0 0 0; float:left; cursor: move" class="sortable-handle"><i class="material-icons text-muted">reorder</i></a>
    </td>
    <td class="align-middle">
      <select class="form-control select-type" name="proposition_type[]" data-row-id="item-row-@{{ id }}">
        <option value="item"@{{#ifvalue type value="item"}} selected="1"@{{/ifvalue}}><?php echo trans('g.item') ?></option>
        <option value="discount"@{{#ifvalue type value="discount"}} selected@{{/ifvalue}}><?php echo trans('g.discount') ?></option>
      </select>
    </td>
    <td class="align-middle">
      <input class="form-control" type="text" value="@{{ description }}" maxlength="150" name="proposition_description[]">
    </td>
    <td class="align-middle">
      <input class="form-control text-right input-quantity" type="number" min="-100000" max="100000" value="@{{ quantity }}" name="proposition_quantity[]">
    </td>
    <td class="align-middle">
      <select class="form-control select-unit" name="proposition_unit[]" @{{#ifvalue type value="discount"}} style="display:none"@{{/ifvalue}}>
        <option value=""></option>
<?php
$units = \Platform\Models\Core\Unit::orderBy('name', 'asc')->get();

foreach ($units as $unit) {
?>
        <option value="{{ $unit->name }}"@{{#ifvalue unit value="<?php echo $unit->name; ?>"}} selected@{{/ifvalue}}>{{ __($unit->name) }}</option>
<?php } ?>
      </select>

      <select class="form-control select-discount-unit" name="proposition_discount_unit[]" @{{#ifvalue type value="item"}} style="display:none"@{{/ifvalue}}>
        <option value="currency" class="currency_code"@{{#ifvalue discount_type value="currency"}} selected@{{/ifvalue}}></option>
        <option value="%"@{{#ifvalue discount_type value="%"}} selected@{{/ifvalue}}>%</option>

      </select>
    </td>
    <td class="align-middle"><input class="form-control text-right input-unit_price" name="proposition_unit_price[]" type="number" min="-10000" max="10000" value="@{{ unit_price }}" @{{#ifvalue type value="discount"}} style="display:none"@{{/ifvalue}}></td>
    <td class="align-middle">
      <select class="form-control select-tax" name="proposition_tax_rate[]">
        <option value=""></option>
<?php
$tax_rates = \Platform\Models\Core\TaxRate::orderBy('rate', 'desc')->get();

foreach ($tax_rates as $rate) {
?>
        <option value="{{ $rate->rate }}"@{{#ifvalue tax_rate value="<?php echo $rate->rate; ?>"}} selected@{{/ifvalue}}>{{ $rate->percentage }}</option>
<?php } ?>
      </select>
    </td>
    <td class="text-right align-middle row-total">
      @{{ total }}
    </td>
    <td class="text-right align-middle">
      <a href="javascript:void(0);" class="btn btn-danger rounded-0 btn-delete-row" data-row="item-row-@{{ id }}"><i class="material-icons">delete</i></a>
    </td>
  </tr>
</script>

<script id="proposition-item-discount" type="text/x-handlebars-template">
  <tr class="item-discount-row">
    <td colspan="5"></td>
    <td colspan="2" class="text-left align-middle"><?php echo trans('g.discount') ?></td>
    <td class="text-right align-middle">@{{ total }}</td>
    <td></td>
  </tr>
</script>

<script id="proposition-item-tax" type="text/x-handlebars-template">
  <tr id="item-tax-row-@{{ id }}" class="item-tax-row">
    <td colspan="5"></td>
    <td colspan="2" class="text-left align-middle"><?php echo trans('g.tax_of_row', ['percentage' => '{{ rate }}', 'amount' => '{{ of }}', 'currency' => '<span class="currency_code"></span>']) ?></td>
    <td class="text-right align-middle">@{{ total }}</td>
    <td></td>
  </tr>
</script>

<script id="task-item" type="text/x-handlebars-template">
  <tr id="task-row-@{{ id }}" class="task-row">
    <td class="align-middle">
      <a href="javascript:void(0);" style="margin:7px 0 0 0; float:left; cursor: move" class="sortable-handle"><i class="material-icons text-muted">reorder</i></a>
    </td>
    <td class="align-middle text-truncate task-priority small">
      <textarea name="task_completed_date[]" style="display: none">@{{ completed_date }}</textarea>
      <textarea name="task_completed_by_id[]" style="display: none">@{{ completed_by_id }}</textarea>
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
    <td class="align-middle">
      <span class="task-assigned_to">@{{{ assigned_to_html }}}</span>
      <textarea name="task_assigned_to_id[]" style="display: none">@{{ assigned_to_id }}</textarea>
    </td>
    <td class="align-middle task-start_date small">
      <textarea name="task_start_date[]" style="display: none">@{{ start_date }}</textarea>
      <textarea name="task_due_date[]" style="display: none">@{{ due_date }}</textarea>
      @{{ due_date_friendly }}
    </td>
    <td class="align-middle task-completed_date small">
      @{{ completed_date_friendly }}
    </td>
    <td class="text-right align-middle" width="104">
      <a href="javascript:void(0);" class="btn btn-primary rounded-0 btn-edit-task mr-1" data-row="@{{ id }}"><i class="material-icons">edit</i></a>
      <a href="javascript:void(0);" class="btn btn-danger rounded-0 btn-delete-task" data-row="@{{ id }}"><i class="material-icons">delete</i></a>
    </td>
  </tr>
</script>
@stop