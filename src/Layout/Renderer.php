<?php
namespace OffbeatWP\AcfLayout\Layout;

class Renderer
{
    protected $postId;
    protected $service;

    public function __construct ($service) {
        $this->service = $service;
    }

    public function renderLayout()
    {
        $this->postId = get_the_ID();

        $enabled = get_field('layout_enabled', $this->postId);
        $inLoop  = in_the_loop();

        if ($enabled && $inLoop) {
            $content = $this->renderRows();
        }

        return $content;
    }

    public function renderRows()
    {
        $content           = '';
        $layoutFieldsIndex = 0;
        $layoutFields      = get_field('layout_row');

        if (have_rows('layout_row')) {
            while (have_rows('layout_row')) {
                the_row();

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

        if (have_rows('component')) {
            while (have_rows('component')) {
                the_row();
                $componentFields = $this->getFields($componentFieldGroups[$componentIndex], ['acf_fc_layout']);
                $rowComponents[] = $this->renderComponent($componentFields);

                $componentIndex++;
            }
        }

        $rowSettings['rowComponents'] = $rowComponents;

        $rowComponent = $this->service->getActiveRowComponent();

        return offbeat('components')->render($rowComponent, $rowSettings);
    }

    public function renderComponent($componentSettings)
    {
        $componentName = get_row_layout();

        if (offbeat('components')->exists($componentName)) {
            $componentSettings['context'] = 'row';
            $componentSettings['componentContent'] = offbeat('components')->render($componentName, $componentSettings);
        } else {
            $componentSettings['componentContent'] = __('Component does not exist', 'offbeatwp');
        }

        $componentComponent = $this->service->getActiveComponentComponent();

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

                if ($fieldObject['type'] == 'repeater') {
                    $repeaterFields = [];
                    while (have_rows($key)) {
                        the_row();

                        $repeaterFields[] = (object) $this->getFields($subFields[$subFieldsIndex]);

                        $subFieldsIndex++;
                    }

                    $fields[$key] = $repeaterFields;
                } elseif ($fieldObject['type'] == 'group') {
                    while (have_rows($key)) {
                        the_row();

                        $fields = array_merge($fields, $this->getFields($subFields, $ignoreKeys));
                    }
                } else {
                    $fieldValue = get_sub_field($key);

                    if (is_array($fieldValue)) $fieldValue = (object) $fieldValue;

                    $fields[$key] = $fieldValue;
                }
            }
        }

        return $fields;
    }
}
