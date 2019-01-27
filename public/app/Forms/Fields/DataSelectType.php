<?php namespace App\Forms\Fields;

use Kris\LaravelFormBuilder\Fields\FormField;

class DataSelectType extends FormField {

    /**
     * The name of the property that holds the value.
     *
     * @var string
     */
    protected $valueProperty = 'selected';

    protected function getTemplate()
    {
        // At first it tries to load config variable,
        // and if fails falls back to loading view
        // resources/views/fields/image.blade.php
        return 'fields.data-select';
    }

    public function render(array $options = [], $showLabel = true, $showField = true, $showError = true)
    {
        return parent::render($options, $showLabel, $showField, $showError);
    }

    /**
     * @inheritdoc
     */
    public function getDefaults()
    {
        return [
            'choices' => [],
            'data' => [],
            'empty_value' => null,
            'selected' => null
        ];
    }
}