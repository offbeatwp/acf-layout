<?php
namespace OffbeatWP\AcfLayout\Layout;

use OffbeatWP\AcfCore\ComponentFields;
use OffbeatWP\AcfCore\FieldsMapper;
use OffbeatWP\AcfLayout\Fields\LayoutField;
use OffbeatWP\AcfLayout\Repositories\AcfLayoutComponentRepository;
use OffbeatWP\Form\Form;

class LayoutEditor {

    public function __construct()
    {

        add_action('acf/init', function () {
            $this->make();
        }, 9999);

        add_filter('acf/pre_update_value', [$this, 'preUpdateValue'], 10, 4);
        add_filter('acf/pre_load_value', [$this, 'preLoadValue'], 10, 3);
        add_filter('acf/pre_load_reference', [$this, 'preLoadReference'], 10, 3);
        add_filter('acf/load_meta', [$this, 'loadMeta'], 10, 2);

        // add_filter('acfe/flexible/thumbnail/name=component', [$this, 'layoutThumbnail'], 10, 3);
    }

    public function make() {
        $form = new Form();
        $form->add(LayoutField::make('page_layout', 'Layout'));
        
        $acfFieldMapper = new FieldsMapper($form);

        $post_types = apply_filters('offbeat_acf_layouteditor_posttypes', ['page']);
        $locations = [];

        if (!empty($post_types)) foreach($post_types as $post_type) {
            $locations[] = [[
                'param' => 'post_type',
                'operator' => '==',
                'value' => $post_type,
            ]];
        }

        $fields = [[
            'key' => 'page_layout_editor_enabled',
            'label' => __('Use Layout editor', 'offbeatwp'),
            'name' => 'page_layout_editor_enabled',
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
        ]];

        $layoutFields = $acfFieldMapper->map();
        $layoutFields[0]['conditional_logic'] = [
            [
                [
                    'field' => 'page_layout_editor_enabled',
                    'operator' => '==',
                    'value' => '1',
                ],
            ]
        ];

        $fields = array_merge($fields, $layoutFields);

        acf_add_local_field_group(array(
            'key' => 'group_page_layout',
            'title' => 'Layout',
            'fields' => $fields,
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

    public function preUpdateValue($check, $value, $postId, $field )
    {
        global $post;

        if ($field['name'] === 'page_layout') {
            $value = $this->normalizeAcfInputField($value, true);

            acf_update_metadata($postId, 'acf_layout_builder', $value);

            $check = false;
        }

        return $check;
    }

    public function normalizeAcfInputField($values, $toIndexedArray = false) {
        if (is_array($values)) {
            $valueKeys = array_keys($values);

            if ($this->isIndexedArray($valueKeys)) {
                $values = array_values($values);
            }

            if (!empty($values)) foreach ($values as $valueKey => $value) {
                $values[$valueKey] = $this->normalizeAcfInputField($value);
            }
        }

        return $values;
    }
    
    public function isIndexedArray($keys) {
        if (!empty($keys)) foreach ($keys as $key) {
            if (!is_numeric($key)) {
                return false;
            }
        }

        return true;
    }

    public function preLoadValue($value, $postId, $field)
    {
        if ($field['name'] === 'page_layout') {
            $layoutEditorContent = get_post_meta($postId, 'acf_layout_builder', true);

            if (!empty($layoutEditorContent)) {
                return $layoutEditorContent;
            }
        }

        return $value;
    }

    public function preLoadReference($reference, $fieldName, $postId)
    {
        if ($fieldName === 'page_layout') {
            return 'field_page_layout';
        }

        return $reference;
    }

    public function loadMeta($meta, $postId) {
        if ($acfLayoutEditorContent = get_post_meta($postId, 'acf_layout_builder', true)) {
            $meta['acf_layout_editor_content'] = $acfLayoutEditorContent;
        }

        return $meta;
    }
}
