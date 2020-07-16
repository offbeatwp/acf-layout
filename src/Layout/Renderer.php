<?php

namespace OffbeatWP\AcfLayout\Layout;


class Renderer
{
    protected $postId;

    public function renderLayout($postId = null)
    {
        $this->postId = $postId ?: get_the_ID();

        $rows = self::getLayoutFields($postId);

        return $this->renderRows($rows);
    }

    public static function getLayoutFields($postId, $force = false)
    {
        if ($force || !($fields = get_post_meta($postId, 'acf_layout_builder_formatted', true))) {
            $fields = get_field('page_layout', $postId);
            $fields = json_encode($fields);
            $fields = json_decode($fields);            

            update_post_meta($postId, 'acf_layout_builder_formatted', $fields);
        }

        return $fields;
    }

    public function renderRows($rows)
    {
        $content = '';

        if (!empty($rows)) foreach ($rows as $row) {
            $content .= $this->renderRow($row);
        }

        return $content;
    }

    public function getComponentName()
    {
        $row = get_row();

        return $row['acf_component'];
    }

    public function renderComponent($component)
    {
        $componentName = $component->acf_component;

        if (offbeat('components')->exists($componentName)) {
            $component->context = 'row';
            $component->componentContent = offbeat('components')->render($componentName, $component);
        } else {
            $component->componentContent = __('Component does not exist (' . $componentName . ')', 'offbeatwp');
        }

        $componentComponent = offbeat('acf_page_builder')->getActiveComponentComponent();

        return offbeat('components')->render($componentComponent, $component);
    }

    public function getAllComponentFields()
    {
        $component = offbeat('components')->get($this->getComponentName());

        $fieldsMapper = new \OffbeatWP\AcfCore\FieldsMapper($component::getForm());

        $keys = wp_list_pluck($fieldsMapper->map(), 'name');
        $keys = array_filter($keys);

        return $keys;
    }

    public function renderRow($rowSettings)
    {
        $components = $rowSettings->components;
        $rowComponents = [];

        if (!empty($components)) foreach ($components as $component) {
            $rowComponents[] = $this->renderComponent($component);
        }

        $rowSettings->rowComponents = $rowComponents;

        $rowComponent = offbeat('acf_page_builder')->getActiveRowComponent();

        return offbeat('components')->render($rowComponent, $rowSettings);
    }

    public function getFields($data, $ignoreKeys = [])
    {
        $fields = [];

        if (!empty($data)) {
            foreach ($data as $key => $value) {
                if (in_array($key, $ignoreKeys)) {
                    continue;
                }

                $subFields = get_sub_field($key);
                $subFieldsIndex = 0;
                $fieldObject = get_sub_field_object($key);

                if ($key == 'layout_row') {
                    $fields['layout'] = $this->renderRows($subFields);
                } elseif ($fieldObject['type'] == 'repeater') {

                    $repeaterFields = [];
                    if (is_array($subFields)) {
                        $subFields = array_values($subFields);
                    }

                    while (have_rows($key, $this->postId)) {
                        the_row();

                        $repeaterFields[] = $this->getFields($subFields[$subFieldsIndex]);

                        $subFieldsIndex++;
                    }

                    $fields[$key] = $repeaterFields;
                } elseif ($fieldObject['type'] == 'group') {
                    while (have_rows($key, $this->postId)) {
                        the_row();
                        $fields[$key] = $this->getFields($subFields, $ignoreKeys);
                    }
                } else {
                    $fieldValue = get_sub_field($key);

                    if (is_array($fieldValue)) $fieldValue = $fieldValue;

                    $fields[$key] = $fieldValue;
                }
            }
        }

        return $fields;
    }
}
