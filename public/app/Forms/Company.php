<?php

namespace App\Forms;

use Kris\LaravelFormBuilder\Form;

class Company extends Form
{
    public function buildForm()
    {
        $this
            ->add('header1', 'static', [
              'tag' => 'legend',
              'wrapper' => false,
              'attr' => ['class' => 'pt-5 pb-3'],
              'value' => trans('g.general'),
              'label_show' => false,
            ])
            ->add('name', 'text', [
              'label' => trans('g.company_name'),
              'rules' => 'required|min:2|max:64',
              'crud' => [
                'list' => [
                  'sort' => 0,
                  'label' => trans('g.company'),
                  'visible' => true,
                  'sortable' => true,
                  'default_sort' => 'asc',
                  'search' => true
                ]
              ]
            ])
            ->add('industry', 'select', [
              'label' => trans('g.industry'),
              'rules' => 'required',
              'attr' => ['class' => 'form-control selectize'],
              'default_value' => '',
              'choices' => \Platform\Models\Industry::all()->pluck('name_translated', 'name')->toArray()
            ])
/*
            ->add('industry', 'text', [
              'rules' => 'required|min:1|max:64',
              'crud' => [
                'list' => [
                  'visible' => true,
                  'search' => true,
                  'column' => false
                ]
              ]
            ])*/;

        $attr = (! auth()->user()->can('all-users')) ? ['class' => 'form-control selectize disabled locked', 'id' => 'users', 'disabled' => 1] : ['class' => 'form-control selectize', 'id' => 'users'];

        $choices = \App\User::orderBy('name', 'asc')->whereHas('roles', function($role) {
          $role->where('id', '=', 1);
          $role->orWhere('id', '=', 5);
        })->get()->sortBy(function ($user, $key) {
          return ! $user->active . ' ' . $user->roles->pluck('id')->min() . ' ' . $user->name;
        }, SORT_FLAG_CASE)->pluck('active_name', 'id')->toArray();

        $this
            ->add('users', 'choice', [
              'label' => trans('g.company_select_users'),
              'choices' => $choices,
              'property' => 'name',
              'selected' => ($this->getData('model') !== null) ? $this->getData('model')->users->pluck('id')->toArray() : [],
              'attr' => $attr,
              'expanded' => false,
              'multiple' => true
            ])
            ->add('logo', 'image', [
              'rules' => 'nullable|mimes:jpeg,gif,png',
              'file_label' => trans('g.select_image'),
              'preview' => [
                'class' => 'mt-5 mb-3 logo',
                'width' => 'auto',
                'height' => '65px',
              ]
            ])
            ->add('default', 'boolean', [
              'label' => trans('g.default_company'),
              'default_value' => 0,
              'wrapper' => ['class' => 'custom-control custom-checkbox'],
              'label_attr' => ['class' => 'custom-control-label'],
              'attr' => ['class' => 'custom-control-input', 'id' => 'default'],
              'help_block' => [
                'text' => trans('g.default_company_help'),
                'tag' => 'small',
                'attr' => ['class' => 'text-muted mt-1 mb-3 float-left w-100']
              ]
            ])
            ->add('active', 'boolean', [
              'default_value' => 1,
              'wrapper' => ['class' => 'custom-control custom-checkbox'],
              'label_attr' => ['class' => 'custom-control-label'],
              'attr' => ['class' => 'custom-control-input', 'id' => 'active'],
              'help_block' => [
                'text' => trans('g.active_company_help'),
                'tag' => 'small',
                'attr' => ['class' => 'text-muted mt-1 mb-3 float-left w-100']
              ]
            ])

            ->add('header2', 'static', [
              'tag' => 'legend',
              'wrapper' => false,
              'attr' => ['class' => 'pt-5 pb-3'],
              'value' => trans('g.address'),
              'label_show' => false,
            ])
            ->add('street1', 'text', [
              'label' => trans('g.street'),
              'rules' => 'nullable|min:1|max:250'
            ])
            ->add('postal_code', 'text', [
              'rules' => 'nullable|min:1|max:32'
            ])
            ->add('city', 'text', [
              'rules' => 'nullable|min:1|max:64',
              'crud' => [
                'list' => [
                  'visible' => true,
                  'search' => true,
                  'column' => false
                ]
              ]
            ])
            ->add('state', 'text', [
              'rules' => 'nullable|min:1|max:64'
            ])
            ->add('country_code', 'select', [
              'rules' => 'nullable',
              'attr' => ['class' => 'form-control selectize'],
              'label' => trans('g.country'),
              'choices' => \Countries::getList(auth()->user()->getLanguage(), 'php'),
              'empty_value' => ' '
            ])

            ->add('header3', 'static', [
              'tag' => 'legend',
              'wrapper' => false,
              'attr' => ['class' => 'pt-5 pb-3'],
              'value' => trans('g.contact'),
              'label_show' => false,
            ])
            ->add('email', 'email', [
              'label' => trans('g.email_address'),
              'rules' => 'nullable|email|unique:companies,email', // Override in controller (add ',company_id') to allow companies updating the same address
            ])
            ->add('phone', 'text', [
              'rules' => 'nullable|min:1|max:32',
              'prefix' => 'phone',
              'crud' => [
                'list' => [
                  'sort' => 2,
                  'visible' => true,
                  'search' => true,
                  'column' => false
                ]
              ]
            ])
            ->add('mobile', 'text', [
              'rules' => 'nullable|min:1|max:32',
              'prefix' => 'smartphone'
            ])
            ->add('website', 'text', [
              'rules' => 'nullable|min:1|max:250',
              'attr' => ['placeholder' => 'https://'],
              'prefix' => 'language'
            ])
            ->add('fax', 'text', [
              'rules' => 'nullable|min:1|max:32',
              'prefix' => 'print'
            ]);
/*
          $attr = (! auth()->user()->can('all-projects')) ? ['class' => 'form-control selectize disabled locked', 'id' => 'projects', 'disabled' => 1] : ['class' => 'form-control selectize', 'id' => 'projects'];

          $this
            ->add('header5', 'static', [
              'tag' => 'div',
              'wrapper' => false,
              'attr' => ['style' => 'width:100%;float:left;height:80px;'],
              'label_show' => false,
            ])
            ->add('projects', 'entity', [
              'label' => trans('g.company_select_projects'),
              'choices' => \Platform\Models\Project::orderBy('name', 'asc')->get()->pluck('active_name', 'id')->toArray(),
              'property' => 'name',
              'selected' => ($this->getData('model') !== null) ? $this->getData('model')->projects->pluck('id')->toArray() : [],
              'attr' => $attr,
              'expanded' => false,
              'multiple' => true
            ])
*/
        $this
            ->add('notes', 'tinymce', [
              'rules' => 'nullable',
              'label_show' => false
            ])

            ->add('back', 'button', [
              'attr' => ['class' => 'btn btn-secondary mr-1', 'onclick="document.location = \'' . url('companies') . '\'"'],
              'label' => trans('g.back'),
            ])
            ->add('submit', 'submit', [
              'attr' => ['class' => 'btn btn-primary', 'id' => 'submit_form_with_tabs'],
              'label' => trans('g.save'),
            ]);

    }
}