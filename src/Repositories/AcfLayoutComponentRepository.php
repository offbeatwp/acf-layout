<?php
namespace OffbeatWP\AcfLayout\Repositories;

use OffbeatWP\AcfCore\ComponentFields;
use OffbeatWP\AcfCore\FieldsMapper;
use OffbeatWP\Components\NestedComponent;

class AcfLayoutComponentRepository {

    public $layouts = null;

    public function getLayouts($excludeNested = false) {
        if (!is_null($this->layouts)) {
            return $this->layouts;
        }

        $acfLayoutService = offbeat()->getService(\OffbeatWP\AcfLayout\Service::class);
        $componentComponent = offbeat('components')->get($this->getActiveComponentComponent());

        $components = [];

        if(!empty($acfLayoutService->components)) foreach ($acfLayoutService->components as $name => $component) {
            $fields = [];
            
            $componentClassReflection = new \ReflectionClass($component);
            if ($excludeNested && $componentClassReflection->implementsInterface(NestedComponent::class)) {
                continue;
            }

            $componentSettings = $component::settings();

            $formFields = $component::getForm();

            if (empty($formFields)) $formFields = [];
    
            if ($formFields->isNotEmpty()) {
                $fieldsMapper = new FieldsMapper($formFields, $component::getSlug());
                $fieldsMapper->setContext('acfeditor');
                $mappedFields = $fieldsMapper->map();
    
                if (!empty($mappedFields)) {
                    $fields = $mappedFields;
                }
            }

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
                'acfe_flexible_category' => isset($componentSettings['category']) && $componentSettings['category'] ? $componentSettings['category'] : 'Basic',
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

    public function getActiveRowComponent()
    {   
        $rowComponent = 'acflayout.row';
        if(offbeat('components')->exists('row')) $rowComponent = 'row';

        return $rowComponent;
    }
    public function getActiveComponentComponent()
    {
        $componentComponent = 'acflayout.component';
        if(offbeat('components')->exists('component')) $componentComponent = 'component';

        return $componentComponent;
    }    
}