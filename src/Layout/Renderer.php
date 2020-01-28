<?php
namespace OffbeatWP\AcfLayout\Layout;

class Renderer
{
    protected $postId;

    public function renderLayout($postId)
    {
        $this->postId = $postId;

        $enabled = get_field('layout_enabled', $postId);
        $inLoop  = in_the_loop();
        $content = null;

        if ($enabled && $inLoop) {
            $content = $this->renderRows(get_field('layout_row', $postId));
        }

        return $content;
    }

    public function renderRows($layoutFields)
    {
        $content           = '';
        $layoutFieldsIndex = 0;

        if (have_rows('layout_row', $this->postId)) {
            while (have_rows('layout_row', $this->postId)) {
                the_row();
                $layoutFields = array_values($layoutFields);
                $rowSettings = $this->getFields($layoutFields[$layoutFieldsIndex], ['component']);

                $content .= $this->renderRow($rowSettings);

                $layoutFieldsIndex++;
            }
        }

        return $content;
    }

    public function renderRow($rowSettings)
    {
        $rowComponents        = [];
        $componentFieldGroups = get_sub_field('component');
        $componentIndex       = 0;

        $rowSettings = json_encode($rowSettings);
        $rowSettings = json_decode($rowSettings);

        if (have_rows('component')) {
            while (have_rows('component')) {
                the_row();

                $componentFields = $this->getFields($componentFieldGroups[$componentIndex], ['acf_fc_layout']);

                $rowComponents[] = $this->renderComponent($componentFields);

                $componentIndex++;
            }
        }

        if (!is_object($rowSettings)) {
            $rowSettings = (object)$rowSettings;
        }
        $rowSettings->rowComponents = $rowComponents;

        $rowComponent = offbeat('acf_page_builder')->getActiveRowComponent();

        return offbeat('components')->render($rowComponent, $rowSettings);
    }

    public function renderComponent($componentSettings)
    {
        $componentName = get_row_layout();

        $componentSettings = json_encode($componentSettings);
        $componentSettings = json_decode($componentSettings);

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
