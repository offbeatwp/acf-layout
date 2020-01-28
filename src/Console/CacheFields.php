<?php
namespace OffbeatWP\AcfLayout\Console;

use OffbeatWP\Console\AbstractCommand;
use OffbeatWP\AcfCore\FieldsMapper;

class CacheFields extends AbstractCommand
{
    const COMMAND = 'acf-layout:cache-fields';

    public function execute($args, $argsNamed)
    {
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

        update_option('acf_layout_builder_component_fields', $fields);

        $this->success('Components fields cached');
    }
}
