<?php
namespace OffbeatWP\AcfLayout;

use OffbeatWP\Services\AbstractServicePageBuilder;

class Service extends AbstractServicePageBuilder {

    public $components = [];

    public $bindings = [
        'acf_page_builder' => Repositories\AcfPageBuilderRepository::class
    ];

    public function afterRegister()
    {
        add_action('acf/init', function () {
            include_once(dirname(__FILE__) . '/Integrations/AcfFieldOffbeatComponents.php');
        });
        
        add_action('admin_enqueue_scripts', [$this, 'adminEnqueueScripts'], 50);

        if (is_admin()) {
            new Layout\Admin($this);
        }

        new Layout\LayoutEditor();

        // PostModel::macro('hasLayout', function () {
        //     return get_field('layout_enabled', $this->getId());
        // });
        
        // $service = $this;
        // PostModel::macro('layout', function () use ($service) {
        //     $renderer = new Layout\Renderer();
        //     return $renderer->renderLayout();
        // });

        // offbeat('components')->register('acflayout.row', Components\Row\Row::class);
        // offbeat('components')->register('acflayout.component', Components\Component\Component::class);

        // if(offbeat('console')->isConsole()) {
        //     offbeat('console')->register(Console\Install::class);
        // }
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