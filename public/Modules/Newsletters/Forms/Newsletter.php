<?php

namespace Modules\Newsletters\Forms;

use Platform\Controllers\Core;
use Kris\LaravelFormBuilder\Form;

class Newsletter extends Form
{
    public function buildForm()
    {
        $users = \App\User::orderBy('name', 'asc')->where('active', true)->whereNotNull('name')->get()->pluck('name', 'id')->toArray();
        $sources = \App\User::select('lead_source')->orderBy('lead_source', 'asc')->where('active', true)->whereNotNull('lead_source')->groupBy('lead_source')->get()->pluck('lead_source', 'lead_source')->toArray();
        $roles = \App\Role::orderBy('name', 'asc')->get()->pluck('name', 'id')->toArray();

        if (empty($sources)) {
          $sources = [[]];
          $sources_placeholder = trans('newsletters::g.no_lead_sources');
        } else {
          $sources_placeholder = '';
        }

        $this
            ->add('active', 'boolean', [
              'default_value' => 1,
              'wrapper' => ['class' => 'custom-control custom-checkbox'],
              'label_attr' => ['class' => 'custom-control-label'],
              'attr' => ['class' => 'custom-control-input', 'id' => 'active']
            ])
            ->add('content', 'textarea', [
              'label_show' => false,
              'rules' => 'nullable',
              'attr' => ['id' => 'content', 'style' => 'display:none']
            ])
            ->add('style', 'textarea', [
              'label_show' => false,
              'rules' => 'nullable',
              'attr' => ['id' => 'style', 'style' => 'display:none']
            ])
            ->add('name', 'text', [
              'label' => trans('g.name'),
              'rules' => 'required|min:1|max:128',
              'help_block' => [
                'text' => trans('newsletters::g.newsletter_name_info'),
                'tag' => 'small',
                'attr' => ['class' => 'text-muted mt-1 mb-5 float-left w-100']
              ]
            ])
            ->add('subject', 'text', [
              'label' => trans('newsletters::g.email_subject'),
              'rules' => 'required|min:1|max:200',
            ])
            ->add('from_name', 'text', [
              'default_value' => auth()->user()->getDefaultCompany()->name,
              'label' => trans('newsletters::g.from_name'),
              'rules' => 'required|min:1|max:128',
            ])
            ->add('from_email', 'email', [
              'default_value' => auth()->user()->getDefaultCompany()->email,
              'label' => trans('newsletters::g.from_email'),
              'rules' => 'required|min:1|max:128',
            ])

            ->add('users', 'entity', [
              'label' => trans('g.people'),
              'choices' => $users,
              'property' => 'name',
              'selected' => ($this->getData('model') !== null) ? $this->getData('model')->users->pluck('id')->toArray() : [],
              'attr' => ['class' => 'form-control selectize', 'id' => 'users'],
              'expanded' => false,
              'multiple' => true
            ])
            ->add('roles', 'entity', [
              'label' => trans('newsletters::g.roles'),
              'choices' => $roles,
              'property' => 'name',
              'selected' => ($this->getData('model') !== null) ? $this->getData('model')->roles->pluck('id')->toArray() : [],
              'attr' => ['class' => 'form-control selectize', 'id' => 'roles'],
              'expanded' => false,
              'multiple' => true
            ])
            ->add('sources', 'entity', [
              'label' => trans('newsletters::g.lead_sources'),
              'choices' => $sources,
              'property' => 'lead_source',
              'selected' => ($this->getData('model') !== null) ? $this->getData('model')->sources->pluck('lead_source')->toArray() : [],
              'attr' => ['class' => 'form-control selectize', 'id' => 'sources', 'placeholder' => $sources_placeholder],
              'expanded' => false,
              'multiple' => true
            ])

            ->add('back', 'button', [
              'attr' => ['class' => 'btn btn-secondary mr-1', 'onclick="document.location = \'' . url('newsletters') . '\'"'],
              'label' => trans('g.back'),
            ]);

      
        $this
            ->add('submit', 'submit', [
              'attr' => ['class' => 'btn btn-primary', 'id' => 'submit_form_with_tabs'],
              'label' => trans('g.save'),
            ]);

    }
}