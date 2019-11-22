<?php
namespace OffbeatWP\AcfLayout\Layout;


class Renderer
{
    protected $postId;

    public function renderLayout()
    {
        $this->postId = get_the_ID();

        // $enabled = get_field('layout_enabled', $this->postId);
        $inLoop  = in_the_loop();


        $rows = get_field('page_layout');

        $rows = json_encode($rows);
        $rows = json_decode($rows);
        // if ($enabled && $inLoop) {
            $content = $this->renderRows($rows);
            // var_dump(get_field('page_layout'));
        // }

        return $content;
    }

    public function renderRows($rows)
    {
        $content           = '';

        if (!empty($rows)) foreach($rows as $row) {
            $content .= $this->renderRow($row);
        }

        return $content;
        
    }

    public function getComponentName() {
        $row = get_row();

        return $row['acf_component'];
    }

    public function renderComponent2($component)
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
        $rowComponents        = [];

        if (!empty($components)) foreach ($components as $component) {
            $rowComponents[] = $this->renderComponent2($component);
        }

        $rowSettings->rowComponents = $rowComponents;

        $rowComponent = offbeat('acf_page_builder')->getActiveRowComponent();

        return offbeat('components')->render($rowComponent, $rowSettings);
    }

    public function renderComponent($componentSettings)
    {
        $componentName = get_row_layout();

        if (!is_object($componentSettings)) {
            $componentSettings = (object) [];
        }

        if (offbeat('components')->exists($componentName)) {
            $componentSettings->context = 'row';
            $componentSettings->componentContent = offbeat('components')->render($componentName, $componentSettings);
        } else {
            $componentSettings->componentContent = __('Component does not exist', 'offbeatwp');
        }

        $componentComponent = offbeat('acf_page_builder')->getActiveComponentComponent();

        return offbeat('components')->render($componentComponent, $componentSettings);
    }

    public function getFields($data, $ignoreKeys = [])
    {
        $fields = [];

        if (!empty($data)) {
            foreach ($data as $key => $value) {
                if (in_array($key, $ignoreKeys)) {
                    continue;
                }

                $subFields      = get_sub_field($key);
                $subFieldsIndex = 0;
                $fieldObject    = get_sub_field_object($key);

                if ($key == 'layout_row') {
                    $fields['layout'] = $this->renderRows($subFields );
                } elseif ($fieldObject['type'] == 'repeater') {

                    $repeaterFields = [];
                    if (is_array($subFields)) {
                        $subFields = array_values($subFields);
                    }

                    while (have_rows($key)) {
                        the_row();

                        $repeaterFields[] = $this->getFields($subFields[$subFieldsIndex]);

                        $subFieldsIndex++;
                    }

                    $fields[$key] = $repeaterFields;
                } elseif ($fieldObject['type'] == 'group') {
                    while (have_rows($key)) {
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
