<?php
namespace OffbeatWP\AcfLayout\Fields;

class DisplaySettings
{
    public static function get()
    {
        $sections = [];

        $margins  = offbeat('design')->getMarginsList('component');

        array_push($sections, [
            'id'     => 'margins',
            'title'  => __('Margins', 'raow'),
            'fields' => [
                [
                    'name'    => 'margin_top',
                    'label'   => __('Margin top', 'raow'),
                    'type'    => 'select',
                    'options' => $margins,
                ],
                [
                    'name'    => 'margin_bottom',
                    'label'   => __('Margin bottom', 'raow'),
                    'type'    => 'select',
                    'options' => $margins,
                ],
            ],
        ]);

        array_push($sections, [
            'id'     => 'misc',
            'title'  => __('Other', 'raow'),
            'fields' => [
                [
                    'name'    => 'id',
                    'label'   => __('ID', 'raow'),
                    'type'    => 'text',
                ],
                [
                    'name'    => 'css_classes',
                    'label'   => __('Class', 'raow'),
                    'type'    => 'text',
                ],
            ],
        ]);

        return [
            'id'       => 'display_settings',
            'title'    => 'Display',
            'sections' => $sections,
        ];
    }
}
