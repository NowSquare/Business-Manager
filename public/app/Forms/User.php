<?php

namespace App\Forms;

use Kris\LaravelFormBuilder\Form;

class User extends Form
{
    public function buildForm()
    {
        $this
            ->add('header1', 'static', [
              'tag' => 'legend',
              'wrapper' => false,
              'attr' => ['class' => 'pt-5 pb-3'],
              'value' => trans('g.login'),
              'label_show' => false,
            ])
            ->add('name', 'text', [
              'label' => trans('g.full_name'),
              'rules' => 'required|min:2|max:32',
              'crud' => [
                'list' => [
                  'sort' => 1,
                  'visible' => true,
                  'label' => trans('g.name'),
                  'sortable' => true,
                  'default_sort' => 'asc',
                  'search' => true
                ]
              ]
            ])
            ->add('email', 'email', [
              'label' => trans('g.email_address'),
              'rules' => 'required|email|unique:users,email', // Override in controller (add ',user_id') to allow users keeping same address
              'crud' => [
                'list' => [
                  'sort' => 3,
                  'visible' => true,
                  'label' => trans('g.contact'),
                  'sortable' => false,
                  'search' => true
                ]
              ]
            ]);

        if ($this->getData('create') === true) {
          $this
              ->add('password', 'password', [
                'rules' => 'required|min:6|max:32'       
              ])
              ->add('notify', 'boolean', [
                'label' => trans('g.notify_user'),
                'value' => 0,
                'default_value' => 0,
                'wrapper' => ['class' => 'custom-control custom-checkbox'],
                'label_attr' => ['class' => 'custom-control-label'],
                'attr' => ['class' => 'custom-control-input', 'id' => 'notify'],
                'help_block' => [
                  'text' => trans('g.send_user_notification_email'),
                  'tag' => 'small',
                  'attr' => ['class' => 'text-muted mt-1 mb-5 float-left w-100']
                ]
              ]);
        } else {
          $this
              ->add('password', 'password', [
                'rules' => 'nullable|min:6|max:32',
                'help_block' => [
                  'text' => trans('g.password_update_help'),
                  'tag' => 'small',
                  'attr' => ['class' => 'text-muted mt-1 mb-5 float-left w-100']
                ]         
              ]);
        }

        $this
            ->add('active', 'boolean', [
              'default_value' => 1,
              'wrapper' => ['class' => 'custom-control custom-checkbox'],
              'label_attr' => ['class' => 'custom-control-label'],
              'attr' => ['class' => 'custom-control-input', 'id' => 'active'],
              'help_block' => [
                'text' => trans('g.active_user_help'),
                'tag' => 'small',
                'attr' => ['class' => 'text-muted mt-1 mb-3 float-left w-100']
              ]
            ])

            ->add('header2', 'static', [
              'tag' => 'legend',
              'wrapper' => false,
              'attr' => ['class' => 'pt-5 pb-3'],
              'value' => trans('g.role'),
              'label_show' => false,
            ]);

        $roles = \App\Role::all()->map(function ($model) {
          return ['id' => $model->id, 'data' => ['data-data' => '{"color": "' . $model->color . '"}']];
        })->toArray();

        $roles_data = [];
        foreach ($roles as $role) {
          $roles_data[$role['id']] = $role['data'];
        }

        $this
            ->add('role', 'data-select', [
              'label' => trans('g.select_a_user_role'),
              'rules' => 'required',
              'attr' => ['class' => 'form-control selectize-color', 'id' => 'role'],
              'default_value' => 5,
              'selected' => ($this->getData('model') !== null) ? $this->getData('model')->roles->pluck('id')[0] : null,
              'choices' => \App\Role::all()->pluck('name', 'id')->toArray(),
              'data' => $roles_data
            ])

            ->add('header3', 'static', [
              'tag' => 'legend',
              'wrapper' => false,
              'attr' => ['class' => 'pt-5 pb-3'],
              'value' => trans('g.image'),
              'label_show' => false,
            ])
            ->add('avatar', 'image', [
              'rules' => 'nullable|mimes:jpeg,gif,png',
              'file_label' => trans('g.select_image'),
              'preview' => [
                'class' => 'my-5 avatar',
                'width' => 'auto',
                'height' => '142px',
              ],
              'crud' => [
                'list' => [
                  'sort' => 0,
                  'visible' => true,
                  'width' => 50,
                  'search' => true,
                  'show_head' => false
                ]
              ]
            ]);

/*
            ->add('header4', 'static', [
              'tag' => 'legend',
              'wrapper' => false,
              'attr' => ['class' => 'pt-5 pb-3'],
              'value' => trans('g.relations'),
              'label_show' => false,
            ]);

          $choices = \Platform\Models\Company::get()->sortBy(function ($model, $key) {
            return ! $model->active . ' ' . $model->name;
          })->pluck('active_name', 'id')->toArray();

          $this
            ->add('companies', 'entity', [
              'label' => trans('g.user_select_companies'),
              'choices' => $choices,
              'property' => 'name',
              'selected' => ($this->getData('model') !== null) ? $this->getData('model')->companies->pluck('id')->toArray() : [],
              'attr' => ['class' => 'form-control selectize', 'id' => 'companies'],
              'wrapper' => ['id' => 'companies_wrapper'],
              'expanded' => false,
              'multiple' => true
            ]);

            $choices = \Platform\Models\Project::get()->sortBy(function ($model, $key) {
              if (isset($model->client)) {
                return ! $model->active . ' ' . $model->client->name . ' ' . $model->name;
              } else {
                return ! $model->active . ' ' . $model->name;
              }
            })->pluck('active_name', 'id')->toArray();

          $this
            ->add('projects', 'entity', [
              'label' => trans('g.user_select_projects'),
              'choices' => $choices,
              'property' => 'name',
              'selected' => ($this->getData('model') !== null) ? $this->getData('model')->projects->pluck('id')->toArray() : [],
              'attr' => ['class' => 'form-control selectize', 'id' => 'projects'],
              'wrapper' => ['id' => 'projects_wrapper'],
              'expanded' => false,
              'multiple' => true
            ])
*/ 
          $this
            ->add('header11', 'static', [
              'tag' => 'legend',
              'wrapper' => false,
              'attr' => ['class' => 'pt-5 pb-3'],
              'value' => trans('g.personal'),
              'label_show' => false,
            ])
            ->add('salutation', 'text', [
              'rules' => 'nullable|min:1|max:32',
              'attr' => ['placeholder' => trans('g.salutation_placeholder')]
            ])
            ->add('first_name', 'text', [
              'rules' => 'nullable|min:1|max:64'
            ])
            ->add('last_name', 'text', [
              'rules' => 'nullable|min:1|max:64'
            ])
            ->add('job_title', 'text', [
              'rules' => 'nullable|min:1|max:64',
              'crud' => [
                'list' => [
                  'sort' => 2,
                  'visible' => true,
                  'column' => false,
                  'search' => true
                ]
              ]
            ])
            ->add('date_of_birth', 'date', [
              'rules' => 'nullable',
              'attr' => ['readonly' => 1, 'style' => 'background-color: #fff']
            ])

            ->add('header5', 'static', [
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
              'rules' => 'nullable|min:1|max:64'
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

            ->add('header6', 'static', [
              'tag' => 'legend',
              'wrapper' => false,
              'attr' => ['class' => 'pt-5 pb-3'],
              'value' => trans('g.contact'),
              'label_show' => false,
            ])
            ->add('phone', 'text', [
              'rules' => 'nullable|min:1|max:32',
              'prefix' => 'phone',
              'crud' => [
                'list' => [
                  'sort' => 4,
                  'visible' => true,
                  'column' => false,
                  'search' => true
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
            ])

            ->add('header7', 'static', [
              'tag' => 'legend',
              'wrapper' => false,
              'attr' => ['class' => 'pt-5 pb-3'],
              'value' => trans('g.language'),
              'label_show' => false,
            ])
            ->add('language', 'select', [
              'label' => trans('g.system_language'),
              'rules' => 'required',
              'attr' => ['class' => 'form-control selectize'],
              'default_value' => config('system.default_language'),
              'choices' => config('system.available_languages')
            ])

            ->add('header8', 'static', [
              'tag' => 'legend',
              'wrapper' => false,
              'attr' => ['class' => 'pt-5 pb-3'],
              'value' => trans('g.date_and_time'),
              'label_show' => false,
            ])
            ->add('date_format', 'select', [
              'rules' => 'required',
              'attr' => ['class' => 'form-control selectize'],
              'default_value' => auth()->user()->getUserDateFormat(),
              'choices' => [
                'Y-m-d' => 'YYYY-MM-DD (' . date('Y-m-d') . ')',
                'y-m-d' => 'YY-MM-DD (' . date('y-m-d') . ')',
                'm/d/Y' => 'MM/DD/YYYY (' . date('m/d/Y') . ')',
                'm/d/y' => 'MM/DD/YY (' . date('m/d/y') . ')',
                'm-d-Y' => 'MM-DD-YYYY (' . date('m-d-Y') . ')',
                'm-d-y' => 'MM-DD-YY (' . date('m-d-y') . ')',
                'd/m/Y' => 'DD/MM/YYYY (' . date('d/m/Y') . ')',
                'd/m/y' => 'DD/MM/YY (' . date('d/m/y') . ')',
                'd-m-Y' => 'DD-MM-YYYY (' . date('d-m-Y') . ')',
                'd-m-y' => 'DD-MM-YY (' . date('d-m-y') . ')'
              ]
            ])
            ->add('timezone', 'select', [
              'rules' => 'required',
              'attr' => ['class' => 'form-control selectize'],
              'default_value' => auth()->user()->getTimezone(),
              'choices' => array_flip(\Nehero\FriendlyTimezone\Facade::get())
            ])
            ->add('time_format', 'select', [
              'label' => trans('g.time_format'),
              'default_value' => auth()->user()->getUserTimeFormat(),
              'rules' => 'required',
              'choices' => [
                'g:i a' => trans('g.12_hour_clock') . ' (' . date('g:i a') . ')',
                'g:i A' => trans('g.12_hour_clock') . ' (' . date('g:i A') . ')',
                'H:i' => trans('g.24_hour_clock') . ' (' . date('H:i') . ')'
              ],
              'attr' => ['class' => 'form-control selectize'],
            ])

            ->add('header9', 'static', [
              'tag' => 'legend',
              'wrapper' => false,
              'attr' => ['class' => 'pt-5 pb-3'],
              'value' => trans('g.money'),
              'label_show' => false,
            ])
            ->add('currency_code', 'select', [
              'label' => trans('g.currency'),
              'rules' => 'required',
              'attr' => ['class' => 'form-control selectize'],
              'default_value' => auth()->user()->getCurrency(),
              'choices' => \Platform\Controllers\Core\Localization::getAllCurrencies()
            ])

            ->add('header10', 'static', [
              'tag' => 'legend',
              'wrapper' => false,
              'attr' => ['class' => 'pt-5 pb-3'],
              'value' => trans('g.numbers'),
              'label_show' => false,
            ]);

            $user = ($this->getData('model') !== null) ? $this->getData('model') : auth()->user();
/*
        $this
            ->add('decimals', 'select', [
              'label' => trans('g.decimal_precision'),
              'default_value' => $user->getDecimals(),
              'rules' => 'required|integer|min:0|max:3',
              'choices' => [
                '0' => '0',
                '1' => '1',
                '2' => '2',
                '3' => '3'
              ],
              'attr' => ['class' => 'form-control selectize'],
            ]);
*/
            $dec_sep = $user->getDecimalSep();
            $thousands_sep = $user->getThousandsSep();

            if ($dec_sep == '.' && $thousands_sep == ',') {
              $val = '.,';
            } elseif ($dec_sep == ',' && $thousands_sep == '.') {
              $val = ',.';
            } elseif ($dec_sep == ',' && $thousands_sep == ' ') {
              $val = ',';
            } elseif ($dec_sep == '.' && $thousands_sep == ' ') {
              $val = '.';
            }
      
        $this
            ->add('seperators', 'select', [
              'label' => trans('g.notation'),
              'default_value' => $val,
              'rules' => 'required',
              'choices' => [
                '.,' => number_format(10000000, $user->getDecimals(), '.', ','),
                ',.' => number_format(10000000, $user->getDecimals(), ',', '.'),
                ',' => number_format(10000000, $user->getDecimals(), ',', ' '),
                '.' => number_format(10000000, $user->getDecimals(), '.', ' ')
              ],
              'attr' => ['class' => 'form-control selectize'],
            ])/*
            ->add('decimals', 'number', [
              'label' => trans('g.decimal_precision'),
              'default_value' => auth()->user()->getDecimals(),
              'rules' => 'required|integer|min:0|max:4'
            ])
            ->add('decimal_seperator', 'select', [
              'label' => trans('g.decimal_seperator'),
              'default_value' => auth()->user()->getDecimalSep(),
              'rules' => 'required',
              'choices' => [
                '.' => '.',
                ',' => ','
              ],
              'attr' => ['class' => 'form-control selectize'],
            ])
            ->add('thousands_seperator', 'select', [
              'label' => trans('g.thousands_seperator'),
              'default_value' => auth()->user()->getThousandsSep(),
              'rules' => 'required',
              'choices' => [
                '.' => '.',
                ',' => ','
              ],
              'attr' => ['class' => 'form-control selectize'],
            ])*/

            ->add('notes', 'tinymce', [
              'rules' => 'nullable',
              'label_show' => false
            ])

            ->add('back', 'button', [
              'attr' => ['class' => 'btn btn-secondary mr-1', 'onclick="document.location = \'' . url('users') . '\'"'],
              'label' => trans('g.back'),
            ])
            ->add('submit', 'submit', [
              'attr' => ['class' => 'btn btn-primary', 'id' => 'submit_form_with_tabs'],
              'label' => trans('g.save'),
            ]);

    }
}