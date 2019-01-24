<?php
namespace OffbeatWP\AcfLayout\Layout;

use OffbeatWP\AcfCore\FieldsMapper;

class LayoutEditor {

    protected $service;

    public function __construct($service)
    {
        $this->service = $service;

        add_action('acf/init', function () {
            $this->make();
        });
    }

    public function makeComponents() {
        $componentComponent = offbeat('components')->get($this->service->getActiveComponentComponent());

        $components = [];

        if(!empty($this->service->components)) foreach ($this->service->components as $name => $component) {
            $componentSettings = $component::settings();

            $fields = [];

            $formFields = $component::getForm();
            if (empty($formFields)) $formFields = [];

            if (!empty($componentComponent::getForm())) {
                $formFields = array_merge($formFields, $componentComponent::getForm());
            }

            if (!empty($formFields)) {
                $fieldsMapper = new FieldsMapper($formFields, $componentSettings['slug']);
                $mappedFields = $fieldsMapper->map();

                if (!empty($mappedFields)) {
                    $fields = $mappedFields;
                }
            }

            $acfDefinedFields = $this->getAcfDefinedFields($name);

            if (!empty($acfDefinedFields)) {
                $fields = array_merge($acfDefinedFields, $fields);
            }

            $componentKey = 'component_' . $name;

            $components[$componentKey] = [
                'key' => $componentKey,
                'name' => $name,
                'label' => $componentSettings['name'],
                'display' => 'row',
                'sub_fields' => $fields,
                'min' => '',
                'max' => '',
            ];
        }

        return $components;
    }

    public function getAcfDefinedFields($component)
    {
        $fieldGroups = acf_get_field_groups(['offbeatwp_component' => $component]);

        if (empty($fieldGroups)) return false;

        $fields = [];

        foreach ($fieldGroups as $fieldGroup) {
            $fieldGroupFields = acf_get_fields($fieldGroup['key']);

            if(!empty($fieldGroupFields))
                $fields = array_merge($fields, $fieldGroupFields);
        }

        return $fields;
    }

    public function makeRowFields()
    {
        $rowFields = [
            array(
                'key' => 'field_5c16d2ee4177f',
                'label' => __('Components', 'offbeatwp'),
                'name' => '',
                'type' => 'tab',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array(
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ),
                'placement' => 'top',
                'endpoint' => 0,
            ),
            array(
                'key' => 'field_5c16d191e5383',
                'label' => __('Component', 'offbeatwp'),
                'name' => 'component',
                'type' => 'flexible_content',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array(
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ),
                'layouts' => $this->makeComponents(),
                'button_label' => __('Add component', 'offbeatwp'),
                'min' => '',
                'max' => '',
            ),
        ];

        $rowFields = array_merge($rowFields, $this->makeRowSettings());

        return $rowFields;
    }

    public function makeRowSettings()
    {
        $rowSettings = [
            array(
                'key' => 'field_5c16d30841780',
                'label' => __('Row Settings', 'offbeatwp'),
                'name' => '',
                'type' => 'tab',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array(
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ),
                'placement' => 'top',
                'endpoint' => 0,
            ),
        ];

        $appearanceFields = [];
        $rowComponent = offbeat('components')->get($this->service->getActiveRowComponent());
        if (method_exists($rowComponent, 'variations')) {
            $variations = collect($rowComponent::variations());
            $variations = $variations->map(function ($item, $key) {
                return $item['label'];
            });

            $appearanceFields[] = [
                'key' => 'field_5c16d32c41789',
                'label' => __('Width', 'offbeatwp'),
                'name' => 'width',
                'type' => 'select',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array(
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ),
                'choices' => $variations->toArray(),
                'default_value' => array(
                ),
                'allow_null' => 0,
                'multiple' => 0,
                'ui' => 0,
                'return_format' => 'value',
                'ajax' => 0,
                'placeholder' => '',
            ];
        }

        $rowThemes   = offbeat('design')->getRowThemesList();
        if(is_array($rowThemes)) {
            $appearanceFields[] = [
                'key' => 'field_5c16d32c41786',
                'label' => __('Row theme', 'offbeatwp'),
                'name' => 'row_theme',
                'type' => 'select',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array(
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ),
                'choices' => $rowThemes,
                'default_value' => array(
                ),
                'allow_null' => 0,
                'multiple' => 0,
                'ui' => 0,
                'return_format' => 'value',
                'ajax' => 0,
                'placeholder' => '',
            ];
        }


        $rowSettings[] = [
            'key'           => 'field_5c16d30841789',
            'name'          => 'appearance',
            'label'         => __('Appearance', 'offbeatwp'),
            'type'          => 'group',
            'layout'        => 'row',
            'sub_fields'    => $appearanceFields
        ];


        $margins    = offbeat('design')->getMarginsList('row');

        $rowSettings[] = [
            'key'           => 'field_5c16d30841781',
            'name'          => 'margins',
            'label'         => __('Margins', 'offbeatwp'),
            'type'          => 'group',
            'layout'        => 'row',
            'sub_fields'    => [
                array(
                    'key' => 'field_5c16d32c41782',
                    'label' => __('Margin Top', 'offbeatwp'),
                    'name' => 'margin_top',
                    'type' => 'select',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'choices' => $margins,
                    'default_value' => array(
                    ),
                    'allow_null' => 0,
                    'multiple' => 0,
                    'ui' => 0,
                    'return_format' => 'value',
                    'ajax' => 0,
                    'placeholder' => '',
                ),
                array(
                    'key' => 'field_5c16d32c41783',
                    'label' => __('Margin Bottom', 'offbeatwp'),
                    'name' => 'margin_bottom',
                    'type' => 'select',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'choices' => $margins,
                    'default_value' => array(
                    ),
                    'allow_null' => 0,
                    'multiple' => 0,
                    'ui' => 0,
                    'return_format' => 'value',
                    'ajax' => 0,
                    'placeholder' => '',
                ),
            ]
        ];

        $paddings   = offbeat('design')->getPaddingsList('row');

        $rowSettings[] = [
            'key'           => 'field_5c16d30841782',
            'name'          => 'paddings',
            'label'         => __('Paddings', 'offbeatwp'),
            'type'          => 'group',
            'layout'        => 'row',
            'sub_fields'    => [
                array(
                    'key' => 'field_5c16d32c41784',
                    'label' => __('Padding top', 'offbeatwp'),
                    'name' => 'padding_top',
                    'type' => 'select',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'choices' => $paddings,
                    'default_value' => array(
                    ),
                    'allow_null' => 0,
                    'multiple' => 0,
                    'ui' => 0,
                    'return_format' => 'value',
                    'ajax' => 0,
                    'placeholder' => '',
                ),
                array(
                    'key' => 'field_5c16d32c41785',
                    'label' => __('Padding bottom', 'offbeatwp'),
                    'name' => 'padding_bottom',
                    'type' => 'select',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'choices' => $paddings,
                    'default_value' => array(
                    ),
                    'allow_null' => 0,
                    'multiple' => 0,
                    'ui' => 0,
                    'return_format' => 'value',
                    'ajax' => 0,
                    'placeholder' => '',
                ),
            ]
        ];

        $rowSettings[] = [
            'key'           => 'field_5c16d30841784',
            'name'          => 'misc',
            'label'         => __('Other', 'offbeatwp'),
            'type'          => 'group',
            'layout'        => 'row',
            'sub_fields'    => [
                array(
                    'key' => 'field_5c16d32c41787',
                    'label' => __('ID', 'offbeatwp'),
                    'name' => 'id',
                    'type' => 'text',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'default_value' => '',
                    'allow_null' => 0,
                    'multiple' => 0,
                    'ui' => 0,
                    'return_format' => 'value',
                    'ajax' => 0,
                    'placeholder' => '',
                ),
                array(
                    'key' => 'field_5c16d32c41788',
                    'label' => __('Class', 'offbeatwp'),
                    'name' => 'css_class',
                    'type' => 'text',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'default_value' => '',
                    'allow_null' => 0,
                    'multiple' => 0,
                    'ui' => 0,
                    'return_format' => 'value',
                    'ajax' => 0,
                    'placeholder' => '',
                ),
            ]
        ];

        return $rowSettings;
    }

    public function make()
    {
        acf_add_local_field_group(array(
            'key' => 'group_layout',
            'title' => 'Layout',
            'fields' => array(
                array(
                    'key' => 'field_5c16c331388e0',
                    'label' => __('Use Layout editor', 'offbeatwp'),
                    'name' => 'layout_enabled',
                    'type' => 'true_false',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'message' => '',
                    'default_value' => 0,
                    'ui' => 0,
                    'ui_on_text' => '',
                    'ui_off_text' => '',
                ),


                array(
                    'key' => 'field_5c16d18ae5382',
                    'label' => __('Rows', 'offbeatwp'),
                    'name' => 'layout_row',
                    'type' => 'repeater',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => [
                        array(
                            array(
                                'field' => 'field_5c16c331388e0',
                                'operator' => '==',
                                'value' => '1',
                            ),
                        ),
                    ],
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'collapsed' => '',
                    'min' => 0,
                    'max' => 0,
                    'layout' => 'block',
                    'button_label' => __('Add row', 'offbeatwp'),
                    'sub_fields' => $this->makeRowFields(),
                ),
            ),
            'location' => array(
                array(
                    array(
                        'param' => 'post_type',
                        'operator' => '==',
                        'value' => 'page',
                    ),
                ),
            ),
            'menu_order' => 0,
            'position' => 'normal',
            'style' => 'default',
            'label_placement' => 'top',
            'instruction_placement' => 'label',
            'hide_on_screen' => '',
            'active' => 1,
            'description' => '',
        ));
    }
}