<?php

namespace OffbeatWP\AcfLayout\Fields;

use OffbeatWP\AcfCore\Fields\AcfField;

class LayoutField extends AcfField
{
    public function __construct()
    {
        $this->attributes = [
            'acffield' => [
                'type' => 'clone',
                'clone' => ['field_5c16d18ae5382'],
                'display' => 'seamless',
                'layout' => 'block',
                'prefix_label' => 0,
                'prefix_name' => 0,
            ]
        ];
    }
}
