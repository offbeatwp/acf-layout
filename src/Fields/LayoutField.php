<?php

namespace OffbeatWP\AcfLayout\Fields;

use OffbeatWP\AcfCore\Fields\AcfField;

class LayoutField extends AcfField
{
    public function init(): void
    {
        $rowComponent = offbeat('acf_page_builder')->getActiveRowComponent();

        if ($rowComponent) {
            $rowComponent = offbeat('components')->get($rowComponent);

            if (method_exists($rowComponent, 'variations')) {
                $variations = collect($rowComponent::variations());
                $variations = $variations->map(function ($item, $key) {
                    return $item['label'];
                });
            }
        }

        $rowThemes  = offbeat('design')->getRowThemesList();
        $margins    = offbeat('design')->getMarginsList('row');
        $paddings   = offbeat('design')->getPaddingsList('row');

        $this->attributes = [
            'acffield' => [
                'type' => 'repeater',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => [
                    'width' => '',
                    'class' => 'page-layout-editor',
                    'id' => '',
                ],
                'collapsed' => '',
                'min' => 0,
                'max' => 0,
                'layout' => 'block',
                'button_label' => '',
                'sub_fields' => [
                    [
                        'key' => 'tab_components',
                        'label' => 'Components',
                        'name' => '',
                        'type' => 'tab',
                        'instructions' => '',
                        'required' => 0,
                        'conditional_logic' => 0,
                        'wrapper' => [
                            'width' => '',
                            'class' => '',
                            'id' => '',
                        ],
                        'placement' => 'top',
                        'endpoint' => 0,
                    ],
                    [
                        'key' => 'field_components',
                        'label' => 'Components',
                        'name' => 'components',
                        '_name' => 'components',
                        'type' => 'offbeat_components',
                        'instructions' => '',
                        'required' => 0,
                        'conditional_logic' => 0,
                        'wrapper' => [
                            'width' => '',
                            'class' => '',
                            'id' => '',
                        ],
                        'min' => '',
                        'max' => '',
                        'button_label' => 'Add Component',
                    ],
                    [
                        'key' => 'tab_row_settings',
                        'label' => 'Row Settings',
                        'name' => '',
                        'type' => 'tab',
                        'instructions' => '',
                        'required' => 0,
                        'conditional_logic' => 0,
                        'wrapper' => [
                            'width' => '',
                            'class' => '',
                            'id' => '',
                        ],
                        'placement' => 'top',
                        'endpoint' => 0,
                    ],
                    apply_filters('acf_layout_editor/rowsettings/appearance', [
                        'key' => 'group_row_appearance',
                        'label' => 'Appearance',
                        'name' => 'appearance',
                        '_name' => 'appearance',
                        'type' => 'group',
                        'instructions' => '',
                        'required' => 0,
                        'conditional_logic' => 0,
                        'wrapper' => [
                            'width' => '',
                            'class' => '',
                            'id' => '',
                        ],
                        'layout' => 'block',
                        'sub_fields' => [
                            [
                                'key' => 'field_row_appearance_width',
                                'label' => 'Width',
                                'name' => 'width',
                                '_name' => 'width',
                                'type' => 'select',
                                'instructions' => '',
                                'required' => 0,
                                'conditional_logic' => 0,
                                'wrapper' => [
                                    'width' => '',
                                    'class' => '',
                                    'id' => '',
                                ],
                                'choices' => (isset($variations)) ? $variations->toArray() : [],
                                'default_value' => [
                                ],
                                'allow_null' => 0,
                                'multiple' => 0,
                                'ui' => 0,
                                'return_format' => 'value',
                                'ajax' => 0,
                                'placeholder' => '',
                            ],
                            [
                                'key' => 'field_row_appearance_row_theme',
                                'label' => 'Row theme',
                                'name' => 'row_theme',
                                '_name' => 'row_theme',
                                'type' => 'select',
                                'instructions' => '',
                                'required' => 0,
                                'conditional_logic' => 0,
                                'wrapper' => [
                                    'width' => '',
                                    'class' => '',
                                    'id' => '',
                                ],
                                'choices' => $rowThemes,
                                'default_value' => [
                                ],
                                'allow_null' => 0,
                                'multiple' => 0,
                                'ui' => 0,
                                'return_format' => 'value',
                                'ajax' => 0,
                                'placeholder' => '',
                            ],
                        ],
                    ]),
                    apply_filters('acf_layout_editor/rowsettings/margins', [
                        'key' => 'group_row_margins',
                        'label' => 'Margins',
                        'name' => 'margins',
                        '_name' => 'margins',
                        'type' => 'group',
                        'instructions' => '',
                        'required' => 0,
                        'conditional_logic' => 0,
                        'wrapper' => [
                            'width' => '',
                            'class' => '',
                            'id' => '',
                        ],
                        'layout' => 'block',
                        'sub_fields' => [
                            [
                                'key' => 'field_row_margins_margin_top',
                                'label' => 'Margin top',
                                'name' => 'margin_top',
                                '_name' => 'margin_top',
                                'type' => 'select',
                                'instructions' => '',
                                'required' => 0,
                                'conditional_logic' => 0,
                                'wrapper' => [
                                    'width' => '',
                                    'class' => '',
                                    'id' => '',
                                ],
                                'choices' => $margins,
                                'default_value' => [
                                ],
                                'allow_null' => 0,
                                'multiple' => 0,
                                'ui' => 0,
                                'return_format' => 'value',
                                'ajax' => 0,
                                'placeholder' => '',
                            ],
                            [
                                'key' => 'field_row_margins_margin_bottom',
                                'label' => 'Margin bottom',
                                'name' => 'margin_bottom',
                                '_name' => 'margin_bottom',
                                'type' => 'select',
                                'instructions' => '',
                                'required' => 0,
                                'conditional_logic' => 0,
                                'wrapper' => [
                                    'width' => '',
                                    'class' => '',
                                    'id' => '',
                                ],
                                'choices' => $margins,
                                'default_value' => [
                                ],
                                'allow_null' => 0,
                                'multiple' => 0,
                                'ui' => 0,
                                'return_format' => 'value',
                                'ajax' => 0,
                                'placeholder' => '',
                            ],
                        ],
                    ]),
                    apply_filters('acf_layout_editor/rowsettings/paddings', [
                        'key' => 'group_row_paddings',
                        'label' => 'Paddings',
                        'name' => 'paddings',
                        '_name' => 'paddings',
                        'type' => 'group',
                        'instructions' => '',
                        'required' => 0,
                        'conditional_logic' => 0,
                        'wrapper' => [
                            'width' => '',
                            'class' => '',
                            'id' => '',
                        ],
                        'layout' => 'block',
                        'sub_fields' => [
                            [
                                'key' => 'field_row_paddings_padding_top',
                                'label' => 'Padding top',
                                'name' => 'padding_top',
                                '_name' => 'padding_top',
                                'type' => 'select',
                                'instructions' => '',
                                'required' => 0,
                                'conditional_logic' => 0,
                                'wrapper' => [
                                    'width' => '',
                                    'class' => '',
                                    'id' => '',
                                ],
                                'choices' => $paddings,
                                'default_value' => [
                                ],
                                'allow_null' => 0,
                                'multiple' => 0,
                                'ui' => 0,
                                'return_format' => 'value',
                                'ajax' => 0,
                                'placeholder' => '',
                            ],
                            [
                                'key' => 'field_row_paddings_padding_bottom',
                                'label' => 'Padding bottom',
                                'name' => 'padding_bottom',
                                '_name' => 'padding_bottom',
                                'type' => 'select',
                                'instructions' => '',
                                'required' => 0,
                                'conditional_logic' => 0,
                                'wrapper' => [
                                    'width' => '',
                                    'class' => '',
                                    'id' => '',
                                ],
                                'choices' => $paddings,
                                'default_value' => [
                                ],
                                'allow_null' => 0,
                                'multiple' => 0,
                                'ui' => 0,
                                'return_format' => 'value',
                                'ajax' => 0,
                                'placeholder' => '',
                            ],
                        ],
                    ]),
                    apply_filters('acf_layout_editor/rowsettings/misc', [
                        'key' => 'group_row_misc',
                        'label' => 'Other',
                        'name' => 'misc',
                        '_name' => 'misc',
                        'type' => 'group',
                        'instructions' => '',
                        'required' => 0,
                        'conditional_logic' => 0,
                        'wrapper' => [
                            'width' => '',
                            'class' => '',
                            'id' => '',
                        ],
                        'layout' => 'block',
                        'sub_fields' => [
                            [
                                'key' => 'field_row_misc_id',
                                'label' => 'ID',
                                'name' => 'id',
                                '_name' => 'id',
                                'type' => 'text',
                                'instructions' => '',
                                'required' => 0,
                                'conditional_logic' => 0,
                                'wrapper' => [
                                    'width' => '',
                                    'class' => '',
                                    'id' => '',
                                ],
                                'default_value' => '',
                                'placeholder' => '',
                                'prepend' => '',
                                'append' => '',
                                'maxlength' => '',
                            ],
                            [
                                'key' => 'field_row_misc_css_class',
                                'label' => 'Class',
                                'name' => 'css_class',
                                '_name' => 'css_class',
                                'type' => 'text',
                                'instructions' => '',
                                'required' => 0,
                                'conditional_logic' => 0,
                                'wrapper' => [
                                    'width' => '',
                                    'class' => '',
                                    'id' => '',
                                ],
                                'default_value' => '',
                                'placeholder' => '',
                                'prepend' => '',
                                'append' => '',
                                'maxlength' => '',
                            ],
                        ],
                    ]),
                ],
                
            ]
        ];
    }
}



