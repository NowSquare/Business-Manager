<?php

return [
    'defaults'      => [
        'wrapper_class'       => 'form-group',
        'wrapper_error_class' => 'has-error',
        'label_class'         => 'control-label',
        'field_class'         => 'form-control',
        'field_error_class'   => '',
        'help_block_class'    => 'text-muted mt-1 mb-2',
        'error_class'         => 'form-error-msg text-danger mt-1 mb-2',
        'required_class'      => 'required'

        // Override a class from a field.
        //'text'                => [
        //    'wrapper_class'   => 'form-field-text',
        //    'label_class'     => 'form-field-text-label',
        //    'field_class'     => 'form-field-text-field',
        //]
        //'radio'               => [
        //    'choice_options'  => [
        //        'wrapper'     => ['class' => 'form-radio'],
        //        'label'       => ['class' => 'form-radio-label'],
        //        'field'       => ['class' => 'form-radio-field'],
        //],
    ],
    // Templates
    'form'          => 'laravel-form-builder::form',
    'text'          => 'laravel-form-builder::text',
    'textarea'      => 'laravel-form-builder::textarea',
    'button'        => 'laravel-form-builder::button',
    'buttongroup'   => 'laravel-form-builder::buttongroup',
    'radio'         => 'laravel-form-builder::radio',
    'checkbox'      => 'laravel-form-builder::checkbox',
    'select'        => 'laravel-form-builder::select',
    'choice'        => 'laravel-form-builder::choice',
    'repeated'      => 'laravel-form-builder::repeated',
    'child_form'    => 'laravel-form-builder::child_form',
    'collection'    => 'laravel-form-builder::collection',
    'static'        => 'laravel-form-builder::static',

    // Remove the laravel-form-builder:: prefix above when using template_prefix
    'template_prefix'   => '',

    'default_namespace' => '',

    'custom_fields' => [
      'image' => App\Forms\Fields\ImageType::class,
      'date' => App\Forms\Fields\DateType::class,
      'date-time' => App\Forms\Fields\DateTimeType::class,
      'text-icon' => App\Forms\Fields\TextIconType::class,
      'password' => App\Forms\Fields\PasswordType::class,
      'boolean' => App\Forms\Fields\BooleanType::class,
      'tinymce' => App\Forms\Fields\TinyMceType::class,
      'data-select' => App\Forms\Fields\DataSelectType::class,
      'hex-color' => App\Forms\Fields\HexColorType::class,
      'rgba-color' => App\Forms\Fields\RgbaColorType::class,
      'text-suffix' => App\Forms\Fields\TextSuffixType::class,
      'text-prefix' => App\Forms\Fields\TextPrefixType::class
    ]
];
