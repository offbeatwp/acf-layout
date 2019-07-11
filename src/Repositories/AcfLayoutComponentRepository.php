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
        $componentComponent = offbeat('components')->get($acfLayoutService->getActiveComponentComponent());

        $components = [];

        if(!empty($acfLayoutService->components)) foreach ($acfLayoutService->components as $name => $component) {
            $componentClassReflection = new \ReflectionClass($component);
            if ($excludeNested && $componentClassReflection->implementsInterface(NestedComponent::class)) {
                continue;
            }

            $componentSettings = $component::settings();

            $formFields = $component::getForm();

            if (empty($formFields)) $formFields = [];
    
            if (!empty($formFields)) {
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

            $components[$componentKey] = [
                'key' => $componentKey,
                'name' => $name,
                'label' => $componentSettings['name'],
                'display' => 'block',
                'sub_fields' => $fields,
                'min' => '',
                'max' => '',
            ];
        }

        $this->layouts = $components;

        return $this->layouts;
    }
}