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
            'title'  => __('Margins', 'offbeatwp'),
            'fields' => [
                [
                    'name'    => 'margin_top',
                    'label'   => __('Margin top', 'offbeatwp'),
                    'type'    => 'select',
                    'options' => $margins,
                ],
                [
                    'name'    => 'margin_bottom',
                    'label'   => __('Margin bottom', 'offbeatwp'),
                    'type'    => 'select',
                    'options' => $margins,
                ],
            ],
        ]);

        array_push($sections, [
            'id'     => 'misc',
            'title'  => __('Other', 'offbeatwp'),
            'fields' => [
                [
                    'name'    => 'id',
                    'label'   => __('ID', 'offbeatwp'),
                    'type'    => 'text',
                ],
                [
                    'name'    => 'css_classes',
                    'label'   => __('Class', 'offbeatwp'),
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
