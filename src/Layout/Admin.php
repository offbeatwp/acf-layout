<?php
namespace OffbeatWP\AcfLayout\Layout;

class Admin {
    public function __construct($service)
    {
        add_filter('use_block_editor_for_post', [$this, 'useBlockEditorForPost'], 20, 2);
        add_action('admin_init',    [$this, 'disableEditorWhenLayoutIsActive'], 99);
        add_action('acf/input/admin_head', [$this, 'rdsn_acf_repeater_collapse']);

        add_filter('acf/field_wrapper_attributes', [$this, 'setDataInputName'], 10, 2);

        add_action('acf/input/admin_footer', [$this,'acfDragNDropFlexibleLayoutsBetweenRepeaters']);

        add_action('admin_enqueue_scripts', [$this, 'enqueueScripts'], 999);
    }

    public  function disableEditorWhenLayoutIsActive()
    {
        global $pagenow, $post;

        if (
            $pagenow == 'post.php' &&
            isset($_GET['post']) &&
            is_numeric($_GET['post']) &&
            get_field('page_layout_editor_enabled', $_GET['post']) === true
        ) {
            remove_post_type_support(get_post_type($_GET['post']), 'editor');
        }
    }

    public  function useBlockEditorForPost($useBlockEditor, $post)
    {
        $postModel = offbeat('post')->get($post);

        if ($postModel->hasLayout()) {
            return false;
        }

        return $useBlockEditor;
    }

    public function setDataInputName($wrapper, $field)
    {
        $inputKey = preg_replace('#.*\[([^\]]+)\]$#misU', '$1', $field['name']);

        $wrapper['data-input-key'] = $inputKey;
        
        // $wrapper['class'] = str_replace('acf-field-offbeat-components', 'acf-field-offbeat-components acf-field-flexible-content', $wrapper['class']);
        
        return $wrapper;
    }

    public function enqueueScripts()
    {
        $min = defined('WP_DEBUG') && WP_DEBUG ? '' : 'min.';
        wp_enqueue_script('offbeat-acf-layout', get_template_directory_uri() . "/vendor/offbeatwp/acf-layout/src/assets/js/main.{$min}js", ['jquery'], 1);
        wp_enqueue_style( 'offbeat-acf-layout', get_template_directory_uri() . "/vendor/offbeatwp/acf-layout/src/assets/css/main.css", [], 1);
    }

    public function rdsn_acf_repeater_collapse() {
    ?>
    <script type="text/javascript">
        jQuery(function($) {
            $('.acf-flexible-content .layout, .acf-layout-clones .layout').addClass('-collapsed');

            $('[data-name="component"]').find('.acf-row:not(.acf-clone)').has('.-collapsed-target').addClass('-collapsed');

            $('[data-name="component"]').find('.acf-row:not(.acf-clone) .-collapsed-target').click(function () {
                $(this).closest('.acf-row').removeClass('-collapsed');
            });
        });
    </script>
    <?php
    }

    function acfDragNDropFlexibleLayoutsBetweenRepeaters() {
        ?>
        <script type="text/javascript">
            
            (function($) {
                acf.add_action('ready', function( item ) {
                    var wrapper = $('.page-layout-editor').first();
                    acfResetFieldNames(wrapper); 
                    
                    fixInputs(wrapper);
                });

                acf.add_action('sortstop', function( item, placeholder ) {
                    acfResetFieldNames($(item).closest('.page-layout-editor').first());              
                });

                acf.add_action('append', function( item ) {
                    var wrapper = $('.page-layout-editor').first();
                    acfResetFieldNames($(item).closest('.page-layout-editor').first());              

                    fixInputs(wrapper);
                });
                

                function acfResetFieldNames(wrapper) {
                    $(wrapper).find('[name^="acf["]').each(function() {
                        var field_name = getFieldName(this);
                        $(this).attr('name', field_name);
                    });
                }

                function fixInputs(wrapper) {
                    $(wrapper).find('input').each(function(){
                        
                        $(this).attr('value', this.value);
                        
                    });

                    $(wrapper).find('textarea').each(function(){
                        
                        $(this).html(this.value);
                        
                    });

                    $(wrapper).find('input:radio,input:checkbox').each(function() {
                        
                        if(this.checked)
                            $(this).attr('checked', 'checked');
                        
                        else
                            $(this).attr('checked', false);
                        
                    });

                    $(wrapper).find('option').each(function(){
                        
                        if(this.selected)
                            $(this).attr('selected', 'selected');
                            
                        else
                            $(this).attr('selected', false);
                        
                    });
                }

                function getFieldName(fieldElement, doAltId) {
                    var currentElement = fieldElement;
                    var nameValue = '';
                    var isFirstId = true;

                    while($(currentElement).parents('[data-input-key], [data-id]').length > 0) {
                        var currentElement = $(currentElement).parents('[data-input-key], [data-id]').first();

                        if (typeof currentElement.data('input-key') !== 'undefined') {
                            nameValue = '[' + currentElement.data('input-key') + ']' + nameValue;
                        } else if (typeof currentElement.data('id') !== 'undefined') {
                            var id = currentElement.data('id');

                            if (typeof currentElement.data('altid') !== 'undefined') {
                                id = currentElement.data('altid');
                            } else {
                                id = acf.uniqueId();
                                currentElement.data('altid', id);
                            }

                            nameValue = '[' + id + ']' + nameValue;
                        }
                    }

                    var current_field_name = $(fieldElement).attr('name');
                    var field_name_additional_key = current_field_name.substring(current_field_name.lastIndexOf('['));

                    var field_name_additional_key_regex = field_name_additional_key.replace('[', '\\\[');
                    field_name_additional_key_regex = field_name_additional_key_regex.replace(']', '\\\]');
                    
                    if (!nameValue.match(new RegExp(field_name_additional_key_regex + '$', 'g'))) {
                        nameValue += field_name_additional_key;
                    }


                    return 'acf' + nameValue;
                }

                function setupAcfLayoutSortable(el) {
                    $(el).find(".values").sortable({
                        connectWith: ".page-layout-editor .values",
                        tolerance: 'pointer',
                        start: function(event, ui) {
                            acf.do_action('sortstart', ui.item, ui.placeholder);
                        },
                        stop: function(event, ui) {
                            acf.do_action('sortstop', ui.item, ui.placeholder);
                            $(this).find('.mce-tinymce').each(function() {
                                tinyMCE.execCommand('mceRemoveControl', true, $(this).attr('id'));
                                tinyMCE.execCommand('mceAddControl', true, $(this).attr('id'));
                            });
                        }
                    });
                }

                acf.add_action('ready', function($el){
                    setupAcfLayoutSortable('.page-layout-editor');
                });

                acf.add_action('append_field', function (el) {
                    if ($(el).find('.values').length) {
                        setupAcfLayoutSortable(el);
                    }
                });
            })(jQuery); 

        </script>
        <?php
    }
}
