<?php namespace App\Forms\Fields;

use Kris\LaravelFormBuilder\Fields\FormField;

class DateTimeType extends FormField {

    protected function getTemplate()
    {
        // At first it tries to load config variable,
        // and if fails falls back to loading view
        // resources/views/fields/image.blade.php
        return 'fields.date-time';
    }

    public function render(array $options = [], $showLabel = true, $showField = true, $showError = true)
    {
        return parent::render($options, $showLabel, $showField, $showError);
    }

    /**
     * Get value property.
     *
     * @param mixed|null $default
     * @return mixed
     */
    public function getValue($default = null)
    {
        $value = $this->getOption($this->valueProperty, $default);

        if (auth()->check() && $value !== null) {
          $value = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $value, 'UTC');
          if ($value !== false) {
            $value->setTimezone(auth()->user()->getTimezone());
            $value = $value->format('Y-m-d H:i:s');
            $this->setValue($value);
          }
        }
        return $value;
    }
}