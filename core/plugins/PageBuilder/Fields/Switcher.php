<?php

namespace plugins\PageBuilder\Fields;

use plugins\PageBuilder\Helpers\Traits\FieldInstanceHelper;
use plugins\PageBuilder\PageBuilderField;

class Switcher extends PageBuilderField
{
    use FieldInstanceHelper;

    /**
     * Render field markup
     */
    public function render()
    {
        $output  = '';
        $output .= $this->field_before();
        $output .= $this->label();

        $checked = $this->value() === 'on' || $this->value() === '1' ? 'checked' : '';

        // Hidden field ensures "off" is saved when unchecked
        $output .= '<input type="hidden" name="' . $this->name() . '" value="off">';

        $output .= '<label class="switch">';
        $output .= '<input type="checkbox" name="' . $this->name() . '" value="on" ' . $checked . '>';
        $output .= '<span class="slider"></span>';
        $output .= '</label>';

        $output .= $this->field_after();

        return $output;
    }
}
