<?php
namespace OffbeatWP\AcfLayout\Layout;

use OffbeatWP\AcfLayout\Repositories\AcfLayoutComponentRepository;

class Renderer
{
    protected $postId;

    public function renderLayout()
    {
        $this->postId = get_the_ID();

        $enabled = get_field('layout_enabled', $this->postId);
        $inLoop  = in_the_loop();

        if ($enabled && $inLoop) {
            $content = $this->renderRows(get_field('layout_row'));
        }

        return $content;
    }

    public function renderRows($layoutFields)
    {
        $content           = '';
        $layoutFieldsIndex = 0;

        if (have_rows('layout_row')) {
            while (have_rows('layout_row')) {
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

        if (have_rows('component')) {
            while (have_rows('component')) {
                the_row();

                $componentFields = $this->getFields($componentFieldGroups[$componentIndex], ['acf_fc_layout']);

                $rowComponents[] = $this->renderComponent($componentFields);

                $componentIndex++;
            }
        }


        $rowSettings['rowComponents'] = $rowComponents;

        $rowComponent = offbeat(AcfLayoutComponentRepository::class)->getActiveRowComponent();

        return offbeat('components')->render($rowComponent, $rowSettings);
    }

    public function renderComponent($componentSettings)
    {
        $componentName = get_row_layout();

        $componentSettings = json_encode($componentSettings);
        $componentSettings = json_decode($componentSettings);

        if (offbeat('components')->exists($componentName)) {
            $componentSettings->context = 'row';
            $componentSettings->componentContent = offbeat('components')->render($componentName, $componentSettings);
        } else {
            $componentSettings->componentContent = __('Component does not exist', 'offbeatwp');
        }

        $componentComponent = offbeat(AcfLayoutComponentRepository::class)->getActiveComponentComponent();

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
