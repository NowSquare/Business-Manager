<?php

namespace App\Forms;

use Kris\LaravelFormBuilder\Form;

class Settings extends Form
{
    public function buildForm()
    {
        $this
            ->add('system_name', 'text', [
              'label' => trans('g.name'),
              'rules' => 'required|min:2|max:32'
            ])
            ->add('system_icon', 'image', [
              'label' => trans('g.icon'),
              'rules' => 'nullable|mimes:jpeg,gif,png',
              'file_label' => trans('g.select_image'),
              'preview' => [
                'class' => 'my-5',
                'width' => 'auto',
                'height' => '64px',
              ]
            ])

            ->add('system_signup', 'boolean', [
              'default_value' => 1,
              'attr' => ['class' => 'custom-control-input', 'id' => 'system_signup'],
              'label' => trans('g.users_can_signup'),
              'wrapper' => ['class' => 'custom-control custom-checkbox'],
              'label_attr' => ['class' => 'custom-control-label'],
              'help_block' => [
                'text' => trans('g.users_can_signup_help', ['client' => '<strong>' . \App\Role::find(config('system.default_signup_role'))->name . '</strong>']),
                'tag' => 'small',
                'attr' => ['class' => 'text-muted mt-1 mb-3 float-left w-100']
              ]
            ])

            ->add('submit', 'submit', [
              'attr' => ['class' => 'btn btn-primary', 'id' => 'submit_form_with_tabs'],
              'label' => trans('g.update_settings'),
            ]);

    }
}