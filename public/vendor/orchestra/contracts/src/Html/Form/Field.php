<?php

namespace Orchestra\Contracts\Html\Form;

interface Field
{
    /**
     * Get value of column.
     *
     * @param  mixed  $row
     * @param  array  $templates
     *
     * @return string
     */
    public function getField($row, array $templates = []);
}
