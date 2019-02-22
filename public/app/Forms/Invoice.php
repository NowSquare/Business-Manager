<?php

namespace App\Forms;

use Platform\Controllers\Core;
use Kris\LaravelFormBuilder\Form;

class Invoice extends Form
{
    public function buildForm()
    {
        $last_invoice = \Platform\Models\Invoice::orderBy('issue_date', 'desc')->first();
        $last_number = ($last_invoice !== null) ? $last_invoice->reference : '-';

        $clients = \Platform\Models\Company::get()->sortBy(function ($model, $key) {
          return ! $model->active . ' ' . $model->name;
        }, SORT_FLAG_CASE)->pluck('active_name', 'id')->toArray();

        $invoice_payment_methods = \Platform\Controllers\Core\Settings::get('invoice_payment_methods', 'json');

        $this
            ->add('issue_date', 'date', [
              'rules' => 'required',
              'default_value' => \Carbon\Carbon::now(),
              'attr' => ['readonly' => 1, 'style' => 'background-color: #fff']
            ])
            ->add('due_date', 'date', [
              'rules' => 'required',
              'attr' => ['readonly' => 1, 'style' => 'background-color: #fff']
            ])
            ->add('company_id', 'select', [
              'label' => trans('g.to'),
              'rules' => 'required',
              'empty_value' => ' ',
              'attr' => ['class' => 'form-control selectize', 'id' => 'company_id'],
              'default_value' => 0,
              'selected' => ($this->getData('model') !== null) ? $this->getData('model')->company_id : 0,
              'choices' => $clients
            ]);

        $currencies = \Platform\Controllers\Core\Localization::getAllCurrencies(false, true);

        $currency_data = [];
        foreach ($currencies as $currency) {
          $currency_data[$currency['id']] = $currency['data'];
        }

        $this
            ->add('description_head', 'textarea', [
              'rules' => 'nullable',
              'attr' => ['rows' => 3],
            ])
            ->add('notes', 'textarea', [
              'label' => trans('g.invoice_footer_note'),
              'value' => ($this->getData('model') !== null) ? $this->getData('model')->notes : Core\Settings::get('invoice_notes', 'text'),
              'rules' => 'nullable',
              'attr' => ['rows' => 5]
            ])
            ->add('save_notes', 'boolean', [
              'label' => trans('g.save_invoice_notes'),
              'value' => 1,
              'default_value' => 1,
              'wrapper' => ['class' => 'custom-control custom-checkbox'],
              'label_attr' => ['class' => 'custom-control-label'],
              'attr' => ['class' => 'custom-control-input', 'id' => 'save_notes']
            ])
            ->add('payment_methods', 'select', [
              'label' => trans('g.payment_methods'),
              'rules' => 'nullable',
              'empty_value' => trans('g.add_payment_methods'),
              'attr' => ['class' => 'form-control selectize'],
              'choices' => trans('g.payment_methods_choices')
            ])
            ->add('payment_method_terms', 'textarea', [
              'label' => false,
              'value' => ($this->getData('model') !== null) ? $this->getData('model')->additional_fields['payment_method_terms'] ?? null : $invoice_payment_methods['payment_method_terms'] ?? null,
              'rules' => 'nullable',
              'attr' => ['rows' => 3]
            ])
            ->add('payment_method_bank_bic_swift', 'text', [
              'label' => trans('g.bic_swift'),
              'value' => ($this->getData('model') !== null) ? $this->getData('model')->additional_fields['payment_method_bank_bic_swift'] ?? null : $invoice_payment_methods['payment_method_bank_bic_swift'] ?? null,
              'rules' => 'nullable|min:8|max:11'
            ])
            ->add('payment_method_bank_iban', 'text', [
              'label' => trans('g.iban'),
              'value' => ($this->getData('model') !== null) ? $this->getData('model')->additional_fields['payment_method_bank_iban'] ?? null : $invoice_payment_methods['payment_method_bank_iban'] ?? null,
              'rules' => 'nullable|min:8|max:34'
            ])
            ->add('payment_method_bank_name', 'text', [
              'label' => trans('g.name'),
              'value' => ($this->getData('model') !== null) ? $this->getData('model')->additional_fields['payment_method_bank_name'] ?? null : $invoice_payment_methods['payment_method_bank_name'] ?? null,
              'rules' => 'nullable|min:2|max:64'
            ])
            ->add('save_payment_methods', 'boolean', [
              'label' => trans('g.save_invoice_payment_methods'),
              'value' => 1,
              'default_value' => 1,
              'wrapper' => ['class' => 'custom-control custom-checkbox'],
              'label_attr' => ['class' => 'custom-control-label'],
              'attr' => ['class' => 'custom-control-input', 'id' => 'save_payment_methods']
            ])
            ->add('reference', 'text', [
              'label' => trans('g.invoice_number'),
              'rules' => 'required|min:1|max:128',
              'help_block' => [
                'text' => trans('g.previous_number') . ': ' . $last_number,
                'tag' => 'small',
                'attr' => ['class' => 'text-muted mt-1 mb-3 float-left w-100']
              ]
            ])
            ->add('currency_code', 'data-select', [
              'label' => trans('g.currency'),
              'rules' => 'required',
              'attr' => ['class' => 'form-control selectize'],
              'default_value' => auth()->user()->getCurrency(),
              'choices' => \Platform\Controllers\Core\Localization::getAllCurrencies(),
              'data' => $currency_data
            ])

            ->add('back', 'button', [
              'attr' => ['class' => 'btn btn-secondary mr-1', 'onclick="document.location = \'' . url('invoices') . '\'"'],
              'label' => trans('g.back'),
            ]);

        if ($this->getData('sl') !== null && auth()->user()->can('view-invoice')) {
          $this
              ->add('view', 'button', [
                'attr' => ['class' => 'btn btn-secondary mr-1 tab-hash', 'onclick="document.location = \'' . url('invoices/view/' . $this->getData('sl')) . '\'"'],
                'label' => trans('g.view_project'),
              ]);
        }
      
        $this
            ->add('submit', 'submit', [
              'attr' => ['class' => 'btn btn-primary', 'id' => 'btn_save'],
              'label' => trans('g.save_draft'),
            ]);
      
        $this
            ->add('save_and_send', 'button', [
              'attr' => ['class' => 'btn btn-success send-invoice'],
              'label' => trans('g.send'),
            ]);

    }
}