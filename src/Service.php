<?php
namespace OffbeatWP\AcfLayout;

use OffbeatWP\Services\AbstractServicePageBuilder;
use OffbeatWP\Content\Post\PostModel;

class Service extends AbstractServicePageBuilder {

    public $components = [];

    public function afterRegister()
    {
        if (is_admin()) {
            new Layout\Admin($this);     
        }

        new Layout\Fields($this);
        new Layout\Renderer($this);

        PostModel::macro('isLayoutEditorActive', function () {
            return get_field('layout_enabled', $this->id);
        });

        offbeat('components')->register('acflayout.row', Components\Row\Row::class);
        offbeat('components')->register('acflayout.component', Components\Component\Component::class);
    }

    public function onRegisterComponent($name, $componentClass)
    {
        $this->components[$name] = $componentClass;
    }

    public function getActiveRowComponent()
    {   
        $rowComponent = 'acflayout.row';
        if(offbeat('components')->exists('row')) $rowComponent = 'row';

        return $rowComponent;
    }
    public function getActiveComponentComponent()
    {
        $componentComponent = 'acflayout.component';
        if(offbeat('components')->exists('component')) $componentComponent = 'row';

        return $componentComponent;
    }
}