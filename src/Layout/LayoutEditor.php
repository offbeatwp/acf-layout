<?php
namespace OffbeatWP\AcfLayout\Layout;

use OffbeatWP\AcfCore\ComponentFields;
use OffbeatWP\AcfCore\FieldsMapper;
use OffbeatWP\AcfLayout\Repositories\AcfLayoutComponentRepository;

class LayoutEditor {

    protected $service;

    public function __construct($service)
    {
        $this->service = $service;

        add_action('acf/init', function () {
            $this->make();
        }, 9999);

        add_filter('acf/pre_update_value', [$this, 'preUpdateValue'], 10, 4);
        add_filter('acf/pre_load_value', [$this, 'preLoadValue'], 10, 3);
        add_filter('acf/pre_load_reference', [$this, 'preLoadReference'], 10, 3);
        add_filter('acf/load_meta', [$this, 'loadMeta'], 10, 2);
    }

    public function preUpdateValue($check, $value, $postId, $field )
    {
        global $post;

        if ($field['name'] === 'layout_row') {

            $value = $this->normalizeAcfInputField($value, true);

            acf_update_metadata($postId, 'acf_layout_editor_content', base64_encode(serialize($value)));

            $check = false;
        }

        return $check;
    }

    public function normalizeAcfInputField($values, $toIndexedArray = false) {

        if ($toIndexedArray && is_array($values)) {
            $values = array_values($values);
        }

        if (!empty($values) && is_array($values)) foreach ($values as $key => $value) {
            $toIndexedArray = false;

            $fieldObject = get_field_object($key);

            if (is_array($fieldObject) && isset($fieldObject['type']) && in_array($fieldObject['type'], ['flexible_content', 'repeater'])) {
                $toIndexedArray = true;
            }

            $values[$key] = $this->normalizeAcfInputField($value, $toIndexedArray);
        } elseif (is_string($values)) {
            $values = stripslashes($values);
        }

        return $values;
    }

    public function preLoadValue($value, $postId, $field)
    {
        if ($field['name'] === 'layout_row') {
            $layoutEditorContent = get_post_meta($postId, 'acf_layout_editor_content', true);

            if (!empty($layoutEditorContent)) {
                if ($layoutEditorContentDecoded = unserialize(base64_decode($layoutEditorContent))) {
                    return $layoutEditorContentDecoded;
                } elseif ($layoutEditorContentDecoded = json_decode($layoutEditorContent, true)) {
                    return $layoutEditorContentDecoded;
                }
            }
        }

        return $value;
    }

    public function preLoadReference($reference, $fieldName, $postId)
    {
        if ($fieldName === 'layout_row') {
            return 'field_5c16d18ae5382';
        }

        return $reference;
    }

    public function loadMeta($meta, $postId) {
        if ($acfLayoutEditorContent = get_post_meta($postId, 'acf_layout_editor_content', true)) {
            $meta['acf_layout_editor_content'] = $acfLayoutEditorContent;
        }

        return $meta;
    }

    public function makeRowFields()
    {
        $acfLayoutComponentRepository = offbeat(AcfLayoutComponentRepository::class);

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
                'mc_acf_ft_true_false'=> 1,
                'wrapper' => array(
                    'width' => '',
                    'class' => 'components-container',
                    'id' => '',
                ),
                'layouts' => $acfLayoutComponentRepository->getLayouts(),
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

        return apply_filters('offbeat_acf_layout_rowsettings', $rowSettings);
    }

    public function make()
    {
        $post_types = apply_filters('offbeat_acf_layouteditor_posttypes', ['page']);
        $locations = [];

        if (!empty($post_types)) foreach($post_types as $post_type) {
            $locations[] = [[
                'param' => 'post_type',
                'operator' => '==',
                'value' => $post_type,
            ]];
        }

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
                        'id' => 'acf-layout-builder',
                    ),
                    'collapsed' => '',
                    'min' => 0,
                    'max' => 0,
                    'layout' => 'block',
                    'button_label' => __('Add row', 'offbeatwp'),
                    'sub_fields' => $this->makeRowFields(),
                ),
            ),
            'location' => $locations,
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
