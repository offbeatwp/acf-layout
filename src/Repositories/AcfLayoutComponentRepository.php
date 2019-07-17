<?php
namespace OffbeatWP\AcfLayout\Repositories;

use OffbeatWP\AcfCore\ComponentFields;

class AcfLayoutComponentRepository {

    public $layouts = null;

    public function getLayouts($excludeNested = false) {
        if (!is_null($this->layouts)) {
            return $this->layouts;
        }

        $acfLayoutService = offbeat()->getService(\OffbeatWP\AcfLayout\Service::class);
        $componentComponent = offbeat('components')->get($acfLayoutService->getActiveComponentComponent());

        $components = [];

        if(!empty($acfLayoutService->components)) foreach ($acfLayoutService->components as $name => $component) {
            if ($excludeNested && $component == \Components\Column\Column::class) {
                continue;
            }

            $componentSettings = $component::settings();

            $fields = ComponentFields::get($name, 'acfeditor');

            if (!empty($componentComponentForm = $componentComponent::getForm())) {
                $fieldsMapper = new FieldsMapper($componentComponentForm, $componentSettings['slug']);
                $mappedFields = $fieldsMapper->map();

                if (!empty($mappedFields)) {
                    $fields = array_merge($fields, $mappedFields);
                }
            }

            $componentKey = 'component_' . $name;
            $categoryName = isset($componentSettings['category']) && $componentSettings['category']
                ? " ({$componentSettings['category']})"
                : null;

            $components[$componentKey] = [
                'key' => $componentKey,
                'name' => $name,
                'label' => $componentSettings['name'] . $categoryName,
                'display' => 'block',
                'sub_fields' => $fields,
                'min' => '',
                'max' => '',
            ];
        }
        
        uasort($components, function($a, $b) {
            if ($a['label'] == $b['label']) {
                return 0;
            }
            return ($a['label'] < $b['label']) ? -1 : 1;
        });

        $this->layouts = $components;

        return $this->layouts;
    }
}