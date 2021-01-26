<?php
namespace OffbeatWP\AcfLayout\Hooks;

use OffbeatWP\Hooks\AbstractFilter;

class SaveWysiwygContentAsUnformattedFilter extends AbstractFilter {
    public function filter($value, $postId, $field) {
        if (empty($GLOBALS['acf_layout_editor_content'])) return $value;

        if (
            $field['type'] == 'repeater' ||
            $field['type'] == 'group' ||
            $field['type'] == 'offbeat_components'
        ) {
            // bail early if no value
            if( empty($value) ) return $value;
            
            
            // bail early if not array
            if( !is_array($value) ) return $value;

            
            // bail early if no sub fields
            if(
                (
                    $field['type'] == 'repeater' ||
                    $field['type'] == 'group'
                ) &&
                empty($field['sub_fields'])
            ) return $value;


            // loop over rows
            foreach( array_keys($value) as $i ) {
                if ($field['type'] == 'offbeat_components') {
                    $componentName = $value[ $i ]['acf_component'];
        
                    if( !offbeat('components')->exists($componentName)) continue;
        
                    $component = offbeat('components')->get($componentName);
        
                    $componentClassName = explode('\\', $component);
                    $componentClassName = array_pop($componentClassName);
        
                    $fieldsMapper = new \OffbeatWP\AcfCore\FieldsMapper($component::getForm(), lcfirst($componentClassName));
                    
                    $sub_fields = $fieldsMapper->map();
                } else {
                    $sub_fields = $field['sub_fields'];
                }

                foreach( array_keys($sub_fields) as $j ) {
                    $sub_field = $sub_fields[ $j ];

                    if ($sub_field['type'] != 'wysiwyg') continue;

                    if ($field['type'] == 'group') {
                        if( is_array($value) && array_key_exists($sub_field['key'], $value) ) {
                            $value[ $sub_field['_name'] . '_raw' ] = $value[ $sub_field['key'] ];
                        }
                    } else {
                        if( is_array($value[ $i ]) && array_key_exists($sub_field['key'], $value[ $i ]) ) {
                            $value[ $i ][ $sub_field['_name'] . '_raw' ] = $value[ $i ][ $sub_field['key'] ];
                        }
                    }

                }
            }

        }

        return $value;
    }
}