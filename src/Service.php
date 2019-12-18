<?php
namespace OffbeatWP\AcfLayout;

use OffbeatWP\Services\AbstractServicePageBuilder;
use OffbeatWP\Content\Post\PostModel;

class Service extends AbstractServicePageBuilder {

    public $components = [];

    public $bindings = [
        Repositories\AcfLayoutComponentRepository::class => Repositories\AcfLayoutComponentRepository::class
    ];

    public function afterRegister()
    {
        if (is_admin()) {
            new Layout\Admin($this);
        }

        new Layout\LayoutEditor();

        PostModel::macro('hasLayout', function () {
            return get_field('layout_enabled', $this->getId());
        });
        
        $service = $this;
        PostModel::macro('layout', function () use ($service) {
            $renderer = new Layout\Renderer();
            return $renderer->renderLayout($this->getId());
        });

        offbeat('components')->register('acflayout.row', Components\Row\Row::class);
        offbeat('components')->register('acflayout.component', Components\Component\Component::class);

        if(offbeat('console')->isConsole()) {
            offbeat('console')->register(Console\Install::class);
        }
    }

    public function onRegisterComponent($name, $componentClass)
    {
        $this->components[$name] = $componentClass;
    }
}