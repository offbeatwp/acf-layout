<?php
namespace OffbeatWP\AcfLayout\Layout;

class Admin {
    public function __construct($service)
    {
        add_action('admin_init',    [$this, 'disableEditorWhenLayoutIsActive'], 99);
        add_action('acf/input/admin_head', [$this, 'rdsn_acf_repeater_collapse']);

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
            get_field('layout_enabled', $_GET['post']) === true
        ) {
            remove_post_type_support(get_post_type($_GET['post']), 'editor');
        }
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
                $(document).on('change', '.acf-field-flexible-content', function () {
                    acfResetFieldNames(this);
                });

                function acfResetFieldNames(wrapper) {
                    $(wrapper).parents('.acf-row').find('[name^="acf[field_"]').each(function() {

                        var field_name = getFieldName(this);

                        $(this).attr('name', field_name);
                    });
                }

                function getFieldName(fieldElement, doAltId) {
                    var currentElement = fieldElement;
                    var nameValue = '';
                    var isFirstId = true;

                    while($(currentElement).parents('[data-key], [data-id]').length > 0) {
                        var currentElement = $(currentElement).parents('[data-key], [data-id]').first();

                        if (currentElement.data('key')) {
                            nameValue = '[' + currentElement.data('key') + ']' + nameValue;
                        } else if (currentElement.data('id')) {
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
                        connectWith: "#acf-layout-builder .values",
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
                    setupAcfLayoutSortable('#acf-layout-builder');
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