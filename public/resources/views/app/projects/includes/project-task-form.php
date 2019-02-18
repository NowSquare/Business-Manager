<?php
$statuses = \Platform\Models\ProjectStatus::orderBy('sort', 'asc')->get()->map(function ($model) {
  return ['id' => $model->id, 'data' => ['data-data' => '{"color": "' . $model->color . '"}']];
})->toArray();

$status_data = [];
foreach ($statuses as $status) {
  $status_data[$status['id']] = $status['data'];
}

$statuses = \Platform\Models\ProjectStatus::all();
$statuses_choices = $statuses->pluck('name', 'id')->toArray();

$assignees = \App\User::orderBy('name', 'asc')->whereHas('roles', function($role) {
  $role->where('id', '=', 1);
  $role->orWhere('id', '=', 2);
  $role->orWhere('id', '=', 3);
  $role->orWhere('id', '=', 4);
})->get();

?>
<script>
var assignees = [];
<?php if (count($assignees) > 0) { ?>
<?php foreach ($assignees as $assginee) { ?>
assignees[<?php echo $assginee->id; ?>] = {
  id: <?php echo $assginee->id; ?>,
  active: "<?php echo str_replace('"', '&quot;', $assginee->active); ?>",
  avatar: "<?php echo (string) $assginee->getAvatar(); ?>",
  email: "<?php echo str_replace('"', '&quot;', $assginee->email); ?>",
  name: "<?php echo str_replace('"', '&quot;', $assginee->name); ?>",
  job_title: <?php echo ($assginee->job_title !== null) ? '"' . str_replace('"', '&quot;', $assginee->job_title) . '"' : 'null'; ?>,
  phone: <?php echo ($assginee->phone !== null) ? '"' . str_replace('"', '&quot;', $assginee->phone) . '"' : 'null'; ?>,
  role: "<?php echo str_replace('"', '&quot;', $assginee->getRoleNames()[0]); ?>",
  role_color: "<?php echo str_replace('"', '&quot;', $assginee->roles[0]->color); ?>",
  recently_online: <?php echo ($assginee->getRecentlyOnline()) ? 'true' : 'false'; ?>
};
<?php } ?>
<?php } ?>

var statuses = [];
<?php if (count($statuses) > 0) { ?>
<?php foreach ($statuses as $status) { ?>
statuses[<?php echo $status->id; ?>] = '<div>' +
      '<span style=\'display:inline-block;position:relative;top:-1px;border-radius:50%;width:8px;height:8px;margin-right:8px;background-color:<?php echo $status->color; ?>\'></span>' +
      '<span class="title"><?php echo str_replace("'", "\'", $status->name); ?></span>' +
      '</div>';
<?php } ?>
<?php } ?>
</script>
<?php
$disabled = (auth()->user()->can('edit-project-task')) ? false : true;
$disabled_attr = ($disabled) ? true : false;

$default_task_status = \Platform\Models\ProjectStatus::getDefaultTask();

$task_form = \FormBuilder::plain(['language_name' => 'g'])
  ->add('form_task_assigned_to_id', 'choice', [
    'label' => trans('g.assign_to'),
    'choices' => $assignees->sortBy(function ($user, $key) {
  return ! $user->active . ' ' . $user->roles->pluck('id')->min() . ' ' . $user->name;
}, SORT_FLAG_CASE)->pluck('active_role_name', 'id')->toArray(),
    'selected' => [],
    'attr' => ['class' => 'form-control selectize', 'id' => 'form_task_assigned_to_id', 'disabled' => $disabled_attr],
    'expanded' => false,
    'multiple' => true
  ])
  ->add('form_task_project_status_id', 'data-select', [
    'label' => trans('g.status'),
    'rules' => 'required',
    'attr' => ['class' => 'form-control selectize-project-statuses', 'id' => 'form_task_project_status_id', 'disabled' => $disabled_attr],
    'default_value' => $default_task_status,
    'selected' => $default_task_status,
    'choices' => $statuses_choices,
    'data' => $status_data,
    'crud' => [
      'list' => [
        'sort' => 1,
        'visible' => true,
        'sortable' => false,
        'column' => false,
        'show_head' => false
      ]
    ]
  ])
  ->add('form_task_start_date', 'date', [
    'label' => trans('g.start_date'),
    'rules' => 'nullable',
    'attr' => ['readonly' => 1, 'style' => 'background-color: #fff', 'disabled' => $disabled_attr]
  ])
  ->add('form_task_due_date', 'date', [
    'label' => trans('g.due_date'),
    'rules' => 'nullable',
    'attr' => ['readonly' => 1, 'style' => 'background-color: #fff', 'disabled' => $disabled_attr]
  ])
  ->add('form_task_completed_date', 'date-time', [
    'label' => trans('g.completed'),
    'rules' => 'nullable',
    'attr' => ['readonly' => 1, 'style' => 'background-color: #fff', 'disabled' => $disabled_attr]
  ])
  ->add('form_task_completed_by_id', 'select', [
    'label' => trans('g.completed_by'),
    'rules' => 'nullable',
    'choices' => $assignees->sortBy(function ($user, $key) {
  return ! $user->active . ' ' . $user->roles->pluck('id')->min() . ' ' . $user->name;
}, SORT_FLAG_CASE)->pluck('active_role_name', 'id')->toArray(),
    'selected' => null,
    'attr' => ['class' => 'form-control selectize', 'id' => 'form_task_completed_by_id', 'disabled' => $disabled_attr],
    'expanded' => false,
    'multiple' => true,
    'empty_value' => ' '
  ])
  ->add('form_task_priority', 'select', [
    'label' => trans('g.priority'),
    'default_value' => 1,
    'rules' => 'nullable|integer|min:0|max:3',
    'choices' => [
      '0' => trans('g.status_code.0'),
      '1' => trans('g.status_code.1'),
      '2' => trans('g.status_code.2'),
      '3' => trans('g.status_code.3')
    ],
    'attr' => ['class' => 'form-control selectize', 'disabled' => $disabled_attr],
  ])
  ->add('form_task_subject', 'text', [
    'label' => trans('g.subject'),
    'rules' => 'required|min:1|max:250',
    'attr' => ['disabled' => $disabled_attr]
  ])
  ->add('form_task_description', 'tinymce', [
    'label' => trans('g.description'),
    'rules' => 'nullable',
    'height' => 180,
    'attr' => ['disabled' => $disabled_attr]
  ])	
  ->add('form_task_hours', 'number', [
    'label' => (auth()->user()->can('edit-project-task') && (! isset($view_project) || ! $view_project)) ? trans('g.hours') : trans('g.hours_spent'),
    'rules' => 'nullable',
    'attr' => ['style' => 'background-color: #fff', 'min' => 0, 'max' => 100000, 'step' => .25, 'disabled' => $disabled_attr],
		'help_block' => (auth()->user()->can('edit-project-task') && (! isset($view_project) || ! $view_project)) ? [
			'text' => trans('g.form_task_hours_help'),
			'tag' => 'small',
			'attr' => ['class' => 'text-muted mt-1 mb-3 float-left w-100']
		] : null
  ])
  ->add('form_task_hourly_rate', 'number', [
    'label' => trans('g.rate'),
    'rules' => 'nullable',
    'attr' => ['style' => 'background-color: #fff', 'min' => 0, 'max' => 100000, 'step' => .25, 'disabled' => $disabled_attr],
		'help_block' => [
			'text' => trans('g.form_task_rate_help'),
			'tag' => 'small',
			'attr' => ['class' => 'text-muted mt-1 mb-3 float-left w-100']
		]
  ])
  ->add('form_task_billable', 'boolean', [
    'default_value' => 0,
    'label' => trans('g.billable'),
    'label_attr' => ['class' => 'custom-control-label mt-5'],
    'wrapper' => ['class' => 'custom-control custom-checkbox'],
    'attr' => ['class' => 'custom-control-input', 'id' => 'form_task_billable', 'disabled' => $disabled_attr]
  ]);

if (isset($edit_form_tags) && $edit_form_tags) {
  echo '<form action="' . url('projects/task/edit') . '" method="POST">';
  echo '<input type="hidden" name="form_project_id" id="form_project_id">';
  echo '<input type="hidden" name="form_task_id" id="form_task_id">';
  echo '<input type="hidden" name="form_task_status_changed" id="form_task_status_changed" value="0">';
}
?>
<div class="modal" tabindex="-1" role="dialog" id="taskForm">
  <div class="modal-dialog modal-xl modal-lg modal-dialog-centered" role="document">
    <div class="modal-content border-0 rounded-0 shadow-lg">
      <div class="modal-header">
        <h5 class="modal-title"><?php echo trans('g.new_task') ?></h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        </button>
      </div>
      <div class="modal-body">
        <div class="row">
          <div class="col-12 col-sm-9">
<?php
echo form_row($task_form->form_task_subject);
?>
          </div>
          <div class="col-12 col-sm-3">
<?php
echo form_row($task_form->form_task_priority);
?>
          </div>
        </div>

        <div class="row">
          <div class="col-12 col-sm-6">
<?php
echo form_row($task_form->form_task_assigned_to_id);
?>
          </div>
          <div class="col-12 col-sm-6">
<?php
echo form_row($task_form->form_task_project_status_id);
?>
          </div>
        </div>

        <div class="row">
          <div class="col-12 col-sm-6">
<?php
echo form_row($task_form->form_task_start_date);
?>
          </div>
          <div class="col-12 col-sm-6">
<?php
echo form_row($task_form->form_task_due_date);
?>
          </div>
        </div>

        <div class="row">
          <div class="col-12 col-sm-6">
<?php
echo form_row($task_form->form_task_completed_date);
?>
          </div>
          <div class="col-12 col-sm-6">
<?php
echo form_row($task_form->form_task_completed_by_id);
?>
          </div>
        </div>

<?php if (isset($view_project) && $view_project) { ?>
        <div class="row">
          <div class="col-12 col-sm-12">
<?php
echo form_row($task_form->form_task_hours);
?>
          </div>
        </div>
<?php } ?>

        <div class="row">
          <div class="col-12">
<?php
echo form_row($task_form->form_task_description);
?>
          </div>
        </div>
<?php if (auth()->user()->can('edit-project-task') && (! isset($view_project) || ! $view_project)) { ?>
        <div class="row mt-5">
          <div class="col-12 col-sm-4">
<?php
echo form_row($task_form->form_task_hours);
?>
          </div>
          <div class="col-12 col-sm-4">
<?php
echo form_row($task_form->form_task_hourly_rate);
?>
          </div>
          <div class="col-12 col-sm-4">
						<div class="mt-3">
<?php
echo form_row($task_form->form_task_billable);
?>
						</div>
          </div>
        </div>
<?php } ?>

      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary rounded-0" data-dismiss="modal"><?php echo trans('g.close') ?></button>
<?php if (auth()->user()->can('edit-project-task') || auth()->user()->can('mark-project-task-complete')) { ?>
        <button type="button" class="btn btn-success rounded-0 btn-add-task"><?php echo trans('g.add_new_task') ?></button>
        <button type="button" class="btn btn-primary rounded-0 btn-save-task" style="display: none"><?php echo trans('g.save') ?></button>
        <button type="button" class="btn btn-success rounded-0 btn-complete-task" style="display: none"><i class="material-icons" style="position: relative; top:1px;">check</i> <?php echo trans('g.set_complete') ?></button>
<?php } ?>
      </div>
    </div>

  </div>
</div>
<?php
if (isset($edit_form_tags) && $edit_form_tags) {
  echo '</form">';
}
?>