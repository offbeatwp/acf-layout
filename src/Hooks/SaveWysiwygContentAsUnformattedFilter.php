<?php
namespace OffbeatWP\AcfLayout\Hooks;

use OffbeatWP\Hooks\AbstractFilter;

class SaveWysiwygContentAsUnformattedFilter extends AbstractFilter {
    public function filter($value, $postId, $field) {
        if (empty($GLOBALS['acf_layout_editor_content'])) return $value;
        if (
            $field['type'] == 'repeater' ||
            $field['type'] == 'offbeat_components' 
        ) {
            // bail early if no value
            if( empty($value) ) return $value;
            
            
            // bail early if not array
            if( !is_array($value) ) return $value;

            
            // bail early if no sub fields
            if( $field['type'] == 'repeater' & empty($field['sub_fields']) ) return $value;


            // loop over rows
            foreach( array_keys($value) as $i ) {
                if ($field['type'] == 'offbeat_components') {
                    $componentName = $value[ $i ]['acf_component'];
                    $formattedValue[$i] = [];
                    $formattedValue[$i]['acf_component'] = $componentName;
        
                    if( !offbeat('components')->exists($componentName)) continue;
        
                    $component = offbeat('components')->get($componentName);
        
                    $componentClassName = explode('\\', $component);
                    $componentClassName = array_pop($componentClassName);
        
                    $fieldsMapper = new \OffbeatWP\AcfCore\FieldsMapper($component::getForm(), lcfirst($componentClassName));
                    
                    $sub_fields = $fieldsMapper->map();
                } else {
                    $sub_fields = $field['sub_fields'];
                }
                // loop through sub fields
                foreach( array_keys($sub_fields) as $j ) {
                    $sub_field = $sub_fields[ $j ];

                    if ($sub_field['type'] != 'wysiwyg') continue;

                    $sub_value = acf_extract_var( $value[ $i ], $sub_field['key'] );

                    $value[ $i ][ $sub_field['_name'] . '_raw' ] = $sub_value;
                }
                
            }

        }

        error_log(var_export($value, true));

        return $value;
    }
}