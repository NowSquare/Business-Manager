<?php

namespace App\Forms;

use Kris\LaravelFormBuilder\Form;

class Project extends Form
{
    public function buildForm()
    {
        $statuses = \Platform\Models\ProjectStatus::orderBy('sort', 'asc')->get()->map(function ($model) {
          return ['id' => $model->id, 'data' => ['data-data' => '{"color": "' . $model->color . '"}']];
        })->toArray();

        $status_data = [];
        foreach ($statuses as $status) {
          $status_data[$status['id']] = $status['data'];
        }

        $assginees = \App\User::orderBy('name', 'asc')->whereHas('roles', function($role) {
          $role->where('id', '=', 1);
          $role->orWhere('id', '=', 2);
          $role->orWhere('id', '=', 3);
          $role->orWhere('id', '=', 4);
        })->get();

        $clients = \Platform\Models\Company::get()->sortBy(function ($model, $key) {
          return ! $model->active . ' ' . $model->name;
        }, SORT_FLAG_CASE)->pluck('active_name', 'id')->toArray();

        $managers = \App\User::orderBy('name', 'asc')->whereHas('roles', function($role) {
          $role->where('id', 1);
          $role->orWhere('id', 2);
        })->get()->sortBy(function ($user, $key) {
          return ! $user->active . ' ' . $user->roles->pluck('id')->min() . ' ' . $user->name;
        }, SORT_FLAG_CASE)->pluck('active_name', 'id')->toArray();

        $this
            ->add('name', 'text', [
              'label' => trans('g.project_name'),
              'rules' => 'required|min:2|max:64',
              'crud' => [
                'list' => [
                  'sort' => 0,
                  'label' => trans('g.project'),
                  'visible' => true,
                  'sortable' => false,
                  'column' => false,
                  'search' => true,
                  'show_head' => false
                ]
              ]
            ])/*
            ->add('category', 'text', [
              'label' => trans('g.category'),
              'rules' => 'nullable|min:2|max:128',
              /*'attr' => ['placeholder' => trans('g.category_placeholder')]* /
            ])*/
            ->add('managers', 'entity', [
              'label' => trans('g.project_manager_s_'),
              'choices' => $managers,
              'property' => 'name',
              'selected' => ($this->getData('model') !== null) ? $this->getData('model')->managers->pluck('id')->toArray() : [],
              'attr' => ['class' => 'form-control selectize', 'id' => 'managers'],
              'expanded' => false,
              'multiple' => true
            ])
            ->add('start_date', 'date', [
              'rules' => 'nullable',
              'attr' => ['readonly' => 1, 'style' => 'background-color: #fff']
            ])
            ->add('completed_date', 'date', [
              'label' => trans('g.date_completed'),
              'rules' => 'nullable',
              'attr' => ['readonly' => 1, 'style' => 'background-color: #fff']
            ])
            ->add('project_status_id', 'data-select', [
              'label' => trans('g.status'),
              'rules' => 'required',
              'attr' => ['class' => 'form-control selectize-project-statuses', 'id' => 'project_status_id'],
              'default_value' => 6,
              'selected' => ($this->getData('model') !== null) ? $this->getData('model')->project_status_id : \Platform\Models\ProjectStatus::getDefaultProject(),
              'choices' => \Platform\Models\ProjectStatus::all()->pluck('name', 'id')->toArray(),
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
            ->add('company_id', 'select', [
              'label' => trans('g.client'),
              'rules' => 'required',
              'empty_value' => ' ',
              'attr' => ['class' => 'form-control selectize', 'id' => 'company_id'],
              'default_value' => 0,
              'selected' => ($this->getData('model') !== null) ? $this->getData('model')->company_id : 0,
              'choices' => $clients
            ])
            ->add('due_date', 'date', [
              'rules' => 'nullable',
              'attr' => ['readonly' => 1, 'style' => 'background-color: #fff']
            ])
            ->add('completed_by_id', 'select', [
              'label' => trans('g.completed_by'),
              'rules' => 'nullable',
              'choices' => $assginees->sortBy(function ($user, $key) {
                return ! $user->active . ' ' . $user->roles->pluck('id')->min() . ' ' . $user->name;
              }, SORT_FLAG_CASE)->pluck('active_role_name', 'id')->toArray(),
              'selected' => null,
              'attr' => ['class' => 'form-control selectize', 'id' => 'form_task_completed_by_id'],
              'expanded' => false,
              'multiple' => true,
              'empty_value' => ' '
            ])/*
            ->add('desc1', 'static', [
              'tag' => 'div',
              'wrapper' => false,
              'attr' => ['class' => 'pb-3 text-muted'],
              'value' => trans('g.project_client_help'),
              'label_show' => false,
            ])*/
            ->add('client_can_comment', 'boolean', [
              'default_value' => 1,
              'label' => trans('g.client_can_comment'),
              'wrapper' => ['class' => 'custom-control custom-checkbox my-2'],
              'label_attr' => ['class' => 'custom-control-label'],
              'attr' => ['class' => 'custom-control-input', 'id' => 'client_can_comment']
            ])
            ->add('client_can_view_proposition', 'boolean', [
              'default_value' => 1,
              'label' => trans('g.client_can_view_proposition'),
              'wrapper' => ['class' => 'custom-control custom-checkbox my-2'],
              'label_attr' => ['class' => 'custom-control-label'],
              'attr' => ['class' => 'custom-control-input', 'id' => 'client_can_view_proposition']
            ])
            ->add('client_can_upload_files', 'boolean', [
              'default_value' => 0,
              'label' => trans('g.client_can_upload_files'),
              'wrapper' => ['class' => 'custom-control custom-checkbox my-2'],
              'label_attr' => ['class' => 'custom-control-label'],
              'attr' => ['class' => 'custom-control-input', 'id' => 'client_can_upload_files']
            ])
            ->add('client_can_view_tasks', 'boolean', [
              'default_value' => 0,
              'label' => trans('g.client_can_view_tasks'),
              'wrapper' => ['class' => 'custom-control custom-checkbox my-2'],
              'label_attr' => ['class' => 'custom-control-label'],
              'attr' => ['class' => 'custom-control-input', 'id' => 'client_can_view_tasks']
            ])
            ->add('notify_people_involved', 'boolean', [
              'default_value' => 1,
              'label' => ($this->getData('model') !== null) ? trans('g.project_update_notification_client') : trans('g.project_notification_client'),
              'wrapper' => ['class' => 'custom-control custom-checkbox my-2'],
              'label_attr' => ['class' => 'custom-control-label'],
              'attr' => ['class' => 'custom-control-input', 'id' => 'notify_people_involved']
            ])/*
            ->add('client_can_edit_tasks', 'boolean', [
              'default_value' => 0,
              'label' => trans('g.client_can_edit_tasks'),
              'wrapper' => ['class' => 'custom-control custom-checkbox my-1'],
              'label_attr' => ['class' => 'custom-control-label'],
              'attr' => ['class' => 'custom-control-input', 'id' => 'client_can_edit_tasks']
            ])*/;
      /*
            ->add('image', 'image', [
              'rules' => 'nullable|mimes:jpeg,gif,png',
              'file_label' => trans('g.select_image'),
              'preview' => [
                'class' => 'mt-5 mb-3',
                'width' => 'auto',
                'height' => '120px',
              ]
            ])*/;

        $currencies = \Platform\Controllers\Core\Localization::getAllCurrencies(false, true);

        $currency_data = [];
        foreach ($currencies as $currency) {
          $currency_data[$currency['id']] = $currency['data'];
        }

        $this
            ->add('short_description', 'textarea', [
              'rules' => 'nullable',
              'attr' => ['rows' => 3],
            ])
            ->add('description', 'tinymce', [
              'label' => trans('g.description'),
              'rules' => 'nullable'
            ])

            ->add('reference', 'text', [
              'label' => trans('g.reference_code'),
              'rules' => 'nullable|min:1|max:128'
            ])
            ->add('currency_code', 'data-select', [
              'label' => trans('g.currency'),
              'rules' => 'required',
              'attr' => ['class' => 'form-control selectize'],
              'default_value' => auth()->user()->getCurrency(),
              'choices' => \Platform\Controllers\Core\Localization::getAllCurrencies(),
              'data' => $currency_data
            ])
            ->add('proposition_valid_until', 'date', [
              'rules' => 'nullable',
              'value' => ($this->getData('model') !== null && isset($this->getData('model')->propositions[0])) ? $this->getData('model')->propositions[0]->proposition_valid_until : null,
              'attr' => ['readonly' => 1, 'style' => 'background-color: #fff']
            ])

            ->add('back', 'button', [
              'attr' => ['class' => 'btn btn-secondary mr-1', 'onclick="document.location = \'' . url('projects') . '\'"'],
              'label' => trans('g.back'),
            ])
            ->add('submit', 'submit', [
              'attr' => ['class' => 'btn btn-primary', 'id' => 'submit_form_with_tabs'],
              'label' => trans('g.save'),
            ]);

    }
}