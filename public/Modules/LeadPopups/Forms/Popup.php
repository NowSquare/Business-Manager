<?php

namespace Modules\LeadPopups\Forms;

use Platform\Controllers\Core;
use Kris\LaravelFormBuilder\Form;

class Popup extends Form
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
              'label' => trans('g.name'),
              'rules' => 'required|min:1|max:128',
              'help_block' => [
                'text' => trans('leadpopups::g.name_info'),
                'tag' => 'small',
                'attr' => ['class' => 'text-muted mt-1 mb-3 float-left w-100']
              ]
            ])
            ->add('form_fields', 'textarea', [
              'rules' => 'required',
              'label_show' => false,
              'attr' => ['id' => 'form_fields', 'style' => 'display:none'],
            ])

            ->add('content', 'tinymce', [
              'label' => trans('leadpopups::g.popup_content'),
              'rules' => 'nullable',
              'default_value' => trans('leadpopups::g.content_default')
            ])
            ->add('additional_fields_submit_button', 'text', [
              'label' => trans('leadpopups::g.submit_button_label'),
              'default_value' => trans('leadpopups::g.submit_button_default'),
              'rules' => 'required|min:1|max:128'
            ])

            ->add('additional_fields_after_submit_message', 'text', [
              'label' => trans('leadpopups::g.after_submit_message'),
              'default_value' => trans('leadpopups::g.after_submit_message_default'),
              'rules' => 'required|min:1|max:128'
            ])
/*
            ->add('additional_fields_action_after_submit', 'select', [
              'label' => trans('leadpopups::g.action_after_submit'),
              'rules' => 'required',
              'attr' => ['class' => 'form-control selectize'],
              'choices' => [
                'onload' => trans('leadpopups::g.onload'), 
                'onleave' => trans('leadpopups::g.onleave'), 
                'onscroll' => trans('leadpopups::g.onscroll')
              ],
              'default_value' => 'onleave',
            ])
*/
            ->add('additional_fields_trigger', 'select', [
              'label' => trans('leadpopups::g.trigger'),
              'rules' => 'required',
              'attr' => ['class' => 'form-control selectize'],
              'choices' => [
                'onload' => trans('leadpopups::g.onload'), 
                'onleave' => trans('leadpopups::g.onleave'), 
                'onscroll' => trans('leadpopups::g.onscroll')
              ],
              'default_value' => 'onleave',
            ])

            ->add('additional_fields_scrollTop', 'text-suffix', [
              'type' => 'number',
              'default_value' => 40,
              'label' => trans('leadpopups::g.scroll_down'),
              'text' => '%',
              'rules' => 'required|numeric|min:0|max:100',
              'help_block' => [
                'text' => trans('leadpopups::g.scroll_info'),
                'tag' => 'small',
                'attr' => ['class' => 'text-muted mt-1 mb-3 float-left w-100']
              ]
            ])
            ->add('additional_fields_delay', 'text-suffix', [
              'type' => 'number',
              'default_value' => 0,
              'label' => trans('leadpopups::g.delay'),
              'text' => trans('leadpopups::g.milliseconds'),
              'rules' => 'required|numeric|min:0|max:3600',
              'help_block' => [
                'text' => trans('leadpopups::g.delay_info'),
                'tag' => 'small',
                'attr' => ['class' => 'text-muted mt-1 mb-3 float-left w-100']
              ]
            ])
            ->add('additional_fields_ignoreAfterCloses', 'text-suffix', [
              'type' => 'number',
              'default_value' => 1,
              'label' => trans('leadpopups::g.show'),
              'text' => trans('leadpopups::g.times'),
              'rules' => 'required|numeric|min:0|max:100',
              'help_block' => [
                'text' => trans('leadpopups::g.show_info'),
                'tag' => 'small',
                'attr' => ['class' => 'text-muted mt-1 mb-3 float-left w-100']
              ]
            ])

            ->add('hosts', 'text', [
              'label' => trans('leadpopups::g.allowed_hosts'),
              'rules' => 'nullable',
              'attr' => ['class' => 'form-control selectize-tags', 'placeholder' => trans('leadpopups::g.allowed_hosts_placeholder')],
              'help_block' => [
                'text' => trans('leadpopups::g.allowed_hosts_info'),
                'tag' => 'small',
                'attr' => ['class' => 'text-muted mt-1 mb-3 float-left w-100']
              ]
            ])

            ->add('paths', 'text', [
              'label' => trans('leadpopups::g.allowed_paths'),
              'rules' => 'nullable',
              'attr' => ['class' => 'form-control selectize-tags', 'placeholder' => trans('leadpopups::g.allowed_paths_placeholder')],
              'help_block' => [
                'text' => trans('leadpopups::g.allowed_paths_info'),
                'tag' => 'small',
                'attr' => ['class' => 'text-muted mt-1 mb-3 float-left w-100']
              ]
            ])

            ->add('additional_fields_position', 'select', [
              'label' => trans('leadpopups::g.position'),
              'rules' => 'required',
              'attr' => ['class' => 'form-control selectize'],
              'choices' => [
                'center' => trans('leadpopups::g.center'),
                'right-bottom' => trans('leadpopups::g.right_bottom') 
              ],
              'default_value' => 'center',
            ])
            ->add('additional_fields_shadow', 'boolean', [
              'label' => trans('leadpopups::g.shadow'),
              'default_value' => 1,
              'wrapper' => ['class' => 'custom-control custom-checkbox'],
              'label_attr' => ['class' => 'custom-control-label'],
              'attr' => ['class' => 'custom-control-input', 'id' => 'additional_fields_shadow']
            ])

            ->add('additional_fields_width', 'text-suffix', [
              'type' => 'number',
              'default_value' => 640,
              'label' => trans('leadpopups::g.width'),
              'text' => trans('leadpopups::g.pixels'),
              'rules' => 'required|numeric|min:200|max:1920'
            ])
            ->add('additional_fields_height', 'text-suffix', [
              'type' => 'number',
              'default_value' => 420,
              'label' => trans('leadpopups::g.height'),
              'text' => trans('leadpopups::g.pixels'),
              'rules' => 'required|numeric|min:1|max:1280'
            ])
            ->add('additional_fields_backdropVisible', 'boolean', [
              'label' => trans('leadpopups::g.show_backdrop'),
              'default_value' => 1,
              'wrapper' => ['class' => 'custom-control custom-checkbox'],
              'label_attr' => ['class' => 'custom-control-label'],
              'attr' => ['class' => 'custom-control-input', 'id' => 'additional_fields_backdropVisible']
            ])
            ->add('additional_fields_showLoader', 'boolean', [
              'label' => trans('leadpopups::g.show_loader'),
              'default_value' => 1,
              'wrapper' => ['class' => 'custom-control custom-checkbox'],
              'label_attr' => ['class' => 'custom-control-label'],
              'attr' => ['class' => 'custom-control-input', 'id' => 'additional_fields_showLoader']
            ])

            ->add('additional_fields_backdropBgColor', 'rgba-color', [
              'default_value' => 'rgba(0,0,0,0.85)',
              'label' => trans('leadpopups::g.backdrop_color'),
              'rules' => 'required|min:10|max:32'
            ])
            ->add('additional_fields_loaderColor', 'hex-color', [
              'default_value' => '#FFFFFF',
              'label' => trans('leadpopups::g.loader_color'),
              'rules' => 'required|min:7|max:7'
            ])
            ->add('additional_fields_closeBtnColor', 'hex-color', [
              'default_value' => '#000000',
              'label' => trans('leadpopups::g.close_button_color'),
              'rules' => 'required|min:7|max:7'
            ])
            ->add('additional_fields_closeBtnMargin', 'text-suffix', [
              'type' => 'number',
              'default_value' => 15,
              'label' => trans('leadpopups::g.close_button_margin'),
              'text' => trans('leadpopups::g.pixels'),
              'rules' => 'required|numeric|min:0|max:100'
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