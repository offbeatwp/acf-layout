<?php
namespace OffbeatWP\AcfLayout\Repositories;

use OffbeatWP\AcfCore\FieldsMapper;
class AcfPageBuilderRepository {

    public $components;

    public function __construct()
    {
        $this->components = collect();
    }

    public function registerComponent($name, $componentClass)
    {
        $this->components->put($name, $componentClass);
    }

    public function getComponentsList() {
        return $this->components;
    }

    public function getActiveRowComponent()
    {   
        $rowComponent = 'acflayout.row';
        if(offbeat('components')->exists('row')) $rowComponent = 'row';

        if(offbeat('components')->exists($rowComponent)) return $rowComponent;

        return false;
    }
    public function getActiveComponentComponent()
    {
        $componentComponent = 'acflayout.component';
        if(offbeat('components')->exists('component')) $componentComponent = 'component';

        return $componentComponent;
    } 

    public function getEnabledPostTypes()
    {
        return apply_filters('offbeat_acf_layouteditor_posttypes', ['page']);
    }

    public function getComponentFields($fromCache = true)
    {
        if ($fromCache) {
            $fields = get_transient('acf_layout_builder_component_fields');

            if (!empty($fields)) {
                error_log('form cache');
                return $fields;
            }
        }

        $components = offbeat('components')->get();
        $fields = [];

        if (!empty($components)) foreach ($components as $component) {
            $componentClassName = explode('\\', $component);
            $componentClassName = array_pop($componentClassName);
    
            if ($component::supports('pagebuilder')) {
                $fieldsMapper = new FieldsMapper($component::getForm(), lcfirst($componentClassName));

                $componentFields = $fieldsMapper->map();

                if (!empty($componentFields)) {
                    $fields = array_merge($fields, $componentFields);
                }
            }
        }

        set_transient('acf_layout_builder_component_fields', $fields);

        return $fields;
    }
}