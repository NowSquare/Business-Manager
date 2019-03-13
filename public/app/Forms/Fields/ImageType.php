<?php namespace App\Forms\Fields;

use Kris\LaravelFormBuilder\Fields\FormField;

class ImageType extends FormField {

    protected function getTemplate()
    {
        // At first it tries to load config variable,
        // and if fails falls back to loading view
        // resources/views/fields/image.blade.php
        return 'fields.image';
    }

    public function render(array $options = [], $showLabel = true, $showField = true, $showError = true)
    {
        $options['attr']['accept'] = 'image/png, image/jpeg';
        $options['attr']['class'] = 'custom-file-input';

        $options['unique'] = ucfirst($this->name);
        $options['attr']['onchange'] = 'setImage' . $options['unique'] . '(this)';

        $model = $this->parent->getData('model');

        if (isset($model->{$this->name}) && is_object($model->{$this->name})) {
          $img_column = $model->{$this->name}->getInstance();
          $img_column_name = ($img_column->value_image !== null) ? 'value_image' : $this->name;

          $options['file_name'] = $img_column->{$img_column_name . '_file_name'};
          $options['file_url'] = ($img_column->{$img_column_name} !== null) ? $img_column->{$img_column_name}->url('form') : null;
          $options['value'] = '';
        } else {
          $options['file_name'] = (isset($model->{$this->name . '_file_name'})) ? $model->{$this->name . '_file_name'} : null;
          $options['file_url'] = (isset($model->{$this->name})) ? $model->{$this->name}->url('form') : null;
        }

        return parent::render($options, $showLabel, $showField, $showError);
    }

    /**
     * @inheritdoc
     */
    public function getDefaults()
    {
        return [
            'remote_preview' => false,
            'empty_value' => null,
            'selected' => null
        ];
    }
}