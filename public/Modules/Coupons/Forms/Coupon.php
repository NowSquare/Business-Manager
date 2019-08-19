<?php

namespace Modules\Coupons\Forms;

use Platform\Controllers\Core;
use Kris\LaravelFormBuilder\Form;

class Coupon extends Form
{
    public function buildForm()
    {
        $this
            ->add('active', 'boolean', [
              'default_value' => 1,
              'wrapper' => ['class' => 'custom-control custom-checkbox'],
              'label_attr' => ['class' => 'custom-control-label'],
              'attr' => ['class' => 'custom-control-input', 'id' => 'active']
            ])
            ->add('name', 'text', [
              'label' => trans('coupons::g.title'),
              'rules' => 'required|min:1|max:128',
              'attr' => ['id' => 'name', 'placeholder' => trans('coupons::g.title_placeholder')],
            ])
            ->add('slug', 'text-prefix', [
              'label' => trans('coupons::g.slug'),
              'text' => '<small>' . url('coupon/') . '/' . '</small>',
              'rules' => 'required|min:1|max:128|unique:coupons,slug|regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/i',
              'attr' => ['id' => 'slug', 'pattern' => '^$|^[a-z0-9-_]+(?:-[a-z0-9-_]+)*$'],
            ])
            ->add('image', 'image', [
              'rules' => 'nullable|mimes:jpeg,gif,png',
              'file_label' => trans('g.select_image'),
              'remote_preview' => true,
              'preview' => [
                'class' => 'mt-5 mb-3 image',
                'width' => 'auto',
                'height' => '65px',
              ],
              'help_block' => [
                'text' => trans('coupons::g.image_help'),
                'tag' => 'small',
                'attr' => ['class' => 'text-muted mt-1 float-left mb-3 w-100']
              ]
            ])
            ->add('favicon', 'image', [
              'label' => trans('coupons::g.icon'),
              'rules' => 'nullable|mimes:jpeg,gif,png',
              'file_label' => trans('g.select_image'),
              'remote_preview' => true,
              'preview' => [
                'class' => 'mt-5 mb-3 image',
                'width' => 'auto',
                'height' => '65px',
              ],
              'help_block' => [
                'text' => trans('coupons::g.icon_help'),
                'tag' => 'small',
                'attr' => ['class' => 'text-muted mt-1 float-left mb-3 w-100']
              ]
            ])
            ->add('form_fields', 'textarea', [
              'rules' => 'required',
              'label_show' => false,
              'attr' => ['id' => 'form_fields', 'style' => 'display:none'],
            ])

            ->add('content', 'tinymce', [
              'label' => trans('coupons::g.details'),
              'rules' => 'required',
              'height' => 240,
              'default_value' => trans('coupons::g.details_placeholder')
            ])
            ->add('redemption_code', 'text-help', [
              'label' => trans('coupons::g.redemption_code'),
              'help' => trans('coupons::g.redeem_info'),
              'rules' => 'required|min:1|max:128',
              'attr' => ['id' => 'redemption_code'],
            ])

            ->add('additional_fields_primary_bg_color', 'hex-color', [
              'default_value' => '#58bd24',
              'label' => trans('coupons::g.primary_background_color'),
              'rules' => 'required|min:7|max:7'
            ])
            ->add('additional_fields_primary_text_color', 'hex-color', [
              'default_value' => '#ffffff',
              'label' => trans('coupons::g.primary_text_color'),
              'rules' => 'required|min:7|max:7'
            ])

            ->add('additional_fields_secondary_bg_color', 'hex-color', [
              'default_value' => '#146eff',
              'label' => trans('coupons::g.secondary_background_color'),
              'rules' => 'required|min:7|max:7'
            ])
            ->add('additional_fields_secondary_text_color', 'hex-color', [
              'default_value' => '#ffffff',
              'label' => trans('coupons::g.secondary_text_color'),
              'rules' => 'required|min:7|max:7'
            ])

            ->add('location', 'text-icon', [
              'label' => trans('coupons::g.location'),
              'prefix' => 'location_on',
              'rules' => 'nullable'
            ])

            ->add('expiration_date', 'date-time', [
              'label' => trans('coupons::g.expires'),
              'rules' => 'nullable'
            ])

            ->add('phone', 'text-icon', [
              'label' => trans('coupons::g.phone'),
              'prefix' => 'phone',
              'rules' => 'nullable'
            ])
            ->add('website', 'text-icon', [
              'label' => trans('coupons::g.website'),
              'prefix' => 'info',
              'rules' => 'nullable',
              'attr' => ['id' => 'website', 'placeholder' => 'http://']
            ])

            ->add('back', 'button', [
              'attr' => ['class' => 'btn btn-secondary mr-1', 'onclick="document.location = \'' . url('popups') . '\'"'],
              'label' => trans('g.back'),
            ]);

        if ($this->getData('sl') !== null) {
          $this
              ->add('view', 'button', [
                'attr' => ['class' => 'btn btn-secondary mr-1 tab-hash', 'onclick="document.location = \'' . url('popups/view/' . $this->getData('sl')) . '\'"'],
                'label' => trans('g.test_modal'),
              ]);
        }
      
        $this
            ->add('submit', 'submit', [
              'attr' => ['class' => 'btn btn-primary', 'id' => 'submit_form_with_tabs'],
              'label' => trans('g.save'),
            ]);

    }
}