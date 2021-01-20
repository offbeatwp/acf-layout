<?php
namespace OffbeatWP\AcfLayout;

use OffbeatWP\AcfLayout\Hooks\SaveWysiwygContentAsUnformattedFilter;
use OffbeatWP\Services\AbstractServicePageBuilder;
use OffbeatWP\Content\Post\PostModel;
use OffbeatWP\Contracts\View;

class Service extends AbstractServicePageBuilder {

    public $components = [];

    public $bindings = [
        'acf_page_builder' => Repositories\AcfPageBuilderRepository::class
    ];

    public function afterRegister(View $view)
    {
        add_action('acf/init', function () {
            include_once(dirname(__FILE__) . '/Integrations/AcfFieldOffbeatComponents.php');
        });
        
        add_action('admin_enqueue_scripts', [$this, 'adminEnqueueScripts'], 50);

        if (is_admin()) {
            new Layout\Admin($this);
        }

        new Layout\LayoutEditor();

        PostModel::macro('hasLayout', function () {
            if (!function_exists('get_field')) return null;

            return get_field('page_layout_editor_enabled', $this->getId());
        });
        
        PostModel::macro('layout', function () {
            $renderer = new Layout\Renderer();
            return apply_filters('acf_layout_editor/content', $renderer->renderLayout($this->getId()));
        });

        $view->registerGlobal('acflayout', new Helpers\AcfLayoutHelper());

        // offbeat('components')->register(\'acflayout.row', Components\Row\Row::class);
        // offbeat('components')->register('acflayout.component', Components\Component\Component::class);

        if(offbeat('console')->isConsole()) {
            offbeat('console')->register(Console\Install::class);
            offbeat('console')->register(Console\CacheFields::class);
            offbeat('console')->register(Console\Preload::class);
        }

        if (offbeat('ajax')->isAjaxRequest() && isset($_POST['action']) && preg_match('#^acf/fields/#', $_POST['action'])) {
            add_action('acf/init', function () {
                $componentFields = get_option('acf_layout_builder_component_fields');
                if (!$componentFields) {
                    return;
                }

                acf_add_local_fields( $componentFields );
            });
        }

        offbeat('hooks')->addFilter('acf/format_value', SaveWysiwygContentAsUnformattedFilter::class, 5 ,3);
    }

    public function onRegisterComponent($name, $componentClass)
    {
        offbeat('acf_page_builder')->registerComponent($name, $componentClass);
    }

    public function adminEnqueueScripts()
    {
        wp_enqueue_script('offbeat-acf-page-builder', get_template_directory_uri() . "/vendor/offbeatwp/acf-layout/src/assets/js/acf_page_builder_field.js", ['jquery'], 1);
    }
}