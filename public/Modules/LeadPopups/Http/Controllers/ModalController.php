<?php

namespace Modules\LeadPopups\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Hash;

use Platform\Controllers\Core;

class ModalController extends Controller {

  /**
   * View modal
   */

  public function getModal($template = 'default', $id = null) {
    $locale = request()->get('locale', 'en');
    $content = request()->get('content', null);
    $form_fields = request()->get('form_fields', null);
    $submit_button = request()->get('submit_button', null);
    $after_submit_message = request()->get('after_submit_message', null);

    if ($id !== null) {
      $id = Core\Secure::staticHashDecode($id);
      $popup = \Modules\LeadPopups\Models\Popup::find($id);
      $popup->views = $popup->views + 1;
      $popup->save();

      $locale = $popup->language;
      $content = $popup->content;

      $form_data = [
        'template' => $template,
        'id' => $id,
        'locale' => $locale,
        'content' => $content,
        'form_fields' => json_encode($popup->form_fields),
        'submit_button' => $popup->additional_fields['submit_button'] ?? null,
        'after_submit_message' => $popup->additional_fields['after_submit_message'] ?? null
      ];
    } else {
      $form_data = [
        'template' => $template,
        'id' => $id,
        'locale' => $locale,
        'content' => $content,
        'form_fields' => $form_fields,
        'submit_button' => $submit_button,
        'after_submit_message' => $after_submit_message
      ];
    }

    app()->setLocale($locale);

    $form = $this->generateForm($form_data);

    return view('leadpopups::modals.' . $template, compact('id', 'content', 'form'));
  }

  /**
   * Post modal
   */

  public function postModal($template = 'default', $id = null) {
    $locale = request()->get('locale', 'en');
    $content = request()->get('content', null);
    $form_fields = request()->get('form_fields', null);
    $submit_button = request()->get('submit_button', null);
    $after_submit_message = request()->get('after_submit_message', null);
    $success = null;
    $error = null;

    if ($id !== null) {
      //$id = Core\Secure::staticHashDecode($id);
      $popup = \Modules\LeadPopups\Models\Popup::find($id);
      $popup->views = $popup->views + 1;
      $popup->save();

      $locale = $popup->language;
      $content = $popup->content;

      $form_data = [
        'template' => $template,
        'id' => $id,
        'locale' => $locale,
        'content' => $content,
        'form_fields' => json_encode($popup->form_fields),
        'submit_button' => $popup->additional_fields['submit_button'] ?? null,
        'after_submit_message' => $popup->additional_fields['after_submit_message'] ?? null
      ];
    } else {
      $form_data = [
        'template' => $template,
        'id' => $id,
        'locale' => $locale,
        'content' => $content,
        'form_fields' => $form_fields,
        'submit_button' => $submit_button,
        'after_submit_message' => $after_submit_message
      ];
    }

    app()->setLocale($locale);

    // Form and model
    $form = $this->generateForm($form_data);

    // Validate form
    if (! $form->isValid()) {
      return redirect()->back()->withErrors($form->getErrors())->withInput();
    }

    if ($id !== null) {
      // Get form post
      $form_fields = $form->getFieldValues();

      array_forget($form_fields, [
        'form_fields', 
        'locale'
      ]);

      $lead_source = $popup->name;

      $user = \App\User::where('email', '=', $form_fields['email'])->where('lead_source', '=', $lead_source)->first();

      $form_fields['account_id'] = 1;
      $form_fields['locale'] = $locale;
      $form_fields['lead_source'] = $lead_source;
      $form_fields['signup_ip_address'] = request()->ip();
      $form_fields['active'] = true;

      if ($user === null) {
        $user = \App\User::create($form_fields);

        // Assign lead role
        $lead_role = \App\Role::find(7);
        $user->assignRole($lead_role);

        // Add conversion
        $popup->conversions = $popup->conversions + 1;
        $popup->save();

        $success = $form_data['after_submit_message'];
      } else {
        $error = trans('leadpopups::g.email_already_exists');
      }
    }

    return view('leadpopups::modals.' . $template, compact('id', 'content', 'form', 'success', 'error'));
  }

  /**
   * Generate form
   */

  public function generateForm($form_data) {
    if ($form_data['form_fields'] !== null && is_string($form_data['form_fields'])) {
      $form_fields = json_decode(urldecode($form_data['form_fields']));
    } else {
      $form_fields = $form_data['form_fields'];
    }

    $form = \FormBuilder::plain([
      'method' => 'POST',
      'enctype' => 'multipart/form-data',
      'url' => url('popups/get/' . $form_data['template'] . '/' . $form_data['id']),
      'language_name' => 'g'
    ]);

    if ($form_data['id'] === null) {
      $form->add('locale', 'textarea', [
        'label_show' => false,
        'value' => $form_data['locale'],
        'attr' => ['style' => 'display:none'],
      ]);

      $form->add('content', 'textarea', [
        'label_show' => false,
        'value' => $form_data['content'],
        'attr' => ['style' => 'display:none'],
      ]);

      $form->add('form_fields', 'textarea', [
        'label_show' => false,
        'value' => $form_data['form_fields'],
        'attr' => ['style' => 'display:none'],
      ]);

      $form->add('submit_button', 'textarea', [
        'label_show' => false,
        'value' => $form_data['submit_button'],
        'attr' => ['style' => 'display:none'],
      ]);

      $form->add('after_submit_message', 'textarea', [
        'label_show' => false,
        'value' => $form_data['after_submit_message'],
        'attr' => ['style' => 'display:none'],
      ]);
    }

    if ($form_fields !== null) {
      foreach ($form_fields as $i => $field) {
        $name = $field->field;
        $required = $field->required;
        $autofocus = ($i == 0) ? ['autofocus' => 1] : [];

        if ($name == 'email') {
          $form->add('email', 'email', [
            'label' => trans('g.email_address'),
            'rules' => 'required|email',
            'attr' => $autofocus
          ]);
        }

        if ($name == 'salutation') {
          $required = ($required == 1) ? 'required' : 'nullable';
          $form->add('salutation', 'text', [
            'rules' => $required . '|min:1|max:32',
            'attr' => array_merge($autofocus, ['placeholder' => trans('g.salutation_placeholder')])
          ]);
        }

        if ($name == 'name') {
          $required = ($required == 1) ? 'required' : 'nullable';
          $form->add('name', 'text', [
            'label' => trans('g.full_name'),
            'rules' => $required . '|min:2|max:32',
            'attr' => $autofocus
          ]);
        }

        if ($name == 'phone') {
          $required = ($required == 1) ? 'required' : 'nullable';
          $form->add('phone', 'text', [
            'rules' => $required . '|min:1|max:32',
            'prefix' => 'phone',
            'attr' => $autofocus
          ]);
        }

        if ($name == 'website') {
          $required = ($required == 1) ? 'required' : 'nullable';
          $form->add('website', 'text', [
            'rules' => $required . '|min:1|max:250',
            'attr' => ['placeholder' => 'https://'],
            'prefix' => 'language',
            'attr' => $autofocus
          ]);
        }

        if ($name == 'street') {
          $required = ($required == 1) ? 'required' : 'nullable';
          $form->add('street1', 'text', [
            'label' => trans('g.street'),
            'rules' => $required . '|min:1|max:250',
            'attr' => $autofocus
          ]);
        }

        if ($name == 'postal_code') {
          $required = ($required == 1) ? 'required' : 'nullable';
          $form->add('postal_code', 'text', [
            'rules' => $required . '|min:1|max:32',
            'attr' => $autofocus
          ]);
        }

        if ($name == 'city') {
          $required = ($required == 1) ? 'required' : 'nullable';
          $form->add('city', 'text', [
            'rules' => $required . '|min:1|max:64',
            'attr' => $autofocus
          ]);
        }

        if ($name == 'state') {
          $required = ($required == 1) ? 'required' : 'nullable';
          $form->add('state', 'text', [
            'rules' => $required . '|min:1|max:64',
            'attr' => $autofocus
          ]);
        }

        if ($name == 'country') {
          $required = ($required == 1) ? 'required' : 'nullable';
          $form->add('country_code', 'select', [
            'rules' => $required,
            'attr' => array_merge($autofocus, ['class' => 'custom-select']),
            'label' => trans('g.country'),
            'choices' => \Countries::getList(auth()->user()->getLanguage(), 'php'),
            'empty_value' => ' '
          ]);
        }
      }

      $form->add('submit', 'submit', [
        'attr' => ['class' => 'btn btn-primary btn-block'],
        'label' => $form_data['submit_button'],
      ]);
    }

    return $form;
  }

  /**
   * Get modal JSON settings
   */

  public function getModalSettings() {
    $token = request()->get('token', null);
    $lang = request()->get('lang', null);
    $host = request()->get('host', null);
    $path = request()->get('path', null);

    $id = Core\Secure::staticHashDecode($token);

    if (is_numeric($id)) {
      $popup = \Modules\LeadPopups\Models\Popup::where('id', $id)->first();
      if ($popup !== null && $popup->active) {
        $settings = $popup->additional_fields;
        if (isset($settings['after_submit_message'])) unset($settings['after_submit_message']);

        if ($settings['shadow'] == 1) $settings['contentClasses'] = '-lm-shadow--8dp';

        $settings['allowedHosts'] = array_filter(explode(',', $popup->hosts));
        $settings['allowedPaths'] = array_filter(explode(',', $popup->paths));

        return response()->json($settings);
      }
    }
  }
}
