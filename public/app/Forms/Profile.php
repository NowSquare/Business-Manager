<?php

namespace App\Forms;

use Kris\LaravelFormBuilder\Form;

class Profile extends Form
{
    public function buildForm()
    {
        $this
            ->add('header1', 'static', [
              'tag' => 'legend',
              'wrapper' => false,
              'attr' => ['class' => 'pt-5 pb-3'],
              'value' => trans('g.account'),
              'label_show' => false,
            ])
            ->add('name', 'text', [
              'label' => trans('g.full_name'),
              'rules' => 'required|min:2|max:32'
            ])
            ->add('email', 'email', [
              'label' => trans('g.email_address'),
              'rules' => 'required|email|unique:users,email,' . auth()->user()->id
            ])
            ->add('password', 'password', [
              'rules' => 'nullable|min:6|max:32',
              'help_block' => [
                'text' => trans('g.password_update_help'),
                'tag' => 'small',
                'attr' => ['class' => 'text-muted mt-2 float-left']
              ]
            ])

            ->add('header2', 'static', [
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
                'width' => '142px',
                'height' => '142px',
              ]
            ])

            ->add('header3', 'static', [
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
              'rules' => 'nullable|min:1|max:64'
            ])
            ->add('date_of_birth', 'date', [
              'rules' => 'nullable',
              'attr' => ['readonly' => 1, 'style' => 'background-color: #fff']
            ])

            ->add('header4', 'static', [
              'tag' => 'legend',
              'wrapper' => false,
              'attr' => ['class' => 'pt-5 pb-3'],
              'value' => trans('g.contact'),
              'label_show' => false,
            ])
            ->add('phone', 'text', [
              'rules' => 'nullable|min:1|max:32',
              'prefix' => 'phone'
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

            ->add('header7', 'static', [
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

            ->add('header8', 'static', [
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

            ->add('header9', 'static', [
              'tag' => 'legend',
              'wrapper' => false,
              'attr' => ['class' => 'pt-5 pb-3'],
              'value' => trans('g.numbers'),
              'label_show' => false,
            ])/*
            ->add('decimals', 'select', [
              'label' => trans('g.decimal_precision'),
              'default_value' => auth()->user()->getDecimals(),
              'rules' => 'required|integer|min:0|max:3',
              'choices' => [
                '0' => '0',
                '1' => '1',
                '2' => '2',
                '3' => '3'
              ],
              'attr' => ['class' => 'form-control selectize'],
            ])*/;

            $dec_sep = auth()->user()->getDecimalSep();
            $thousands_sep = auth()->user()->getThousandsSep();

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
                '.,' => number_format(10000000, auth()->user()->getDecimals(), '.', ','),
                ',.' => number_format(10000000, auth()->user()->getDecimals(), ',', '.'),
                ',' => number_format(10000000, auth()->user()->getDecimals(), ',', ' '),
                '.' => number_format(10000000, auth()->user()->getDecimals(), '.', ' ')
              ],
              'attr' => ['class' => 'form-control selectize'],
            ])/*
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
                ',' => ',',
                ' ' => '&nbsp;'
              ],
              'attr' => ['class' => 'form-control selectize'],
            ])*/

            ->add('submit', 'submit', [
              'attr' => ['class' => 'btn btn-primary', 'id' => 'submit_form_with_tabs'],
              'label' => trans('g.update_profile'),
            ]);

    }
}