<?php
namespace OffbeatWP\AcfLayout\Repositories;

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

        return $rowComponent;
    }
    public function getActiveComponentComponent()
    {
        $componentComponent = 'acflayout.component';
        if(offbeat('components')->exists('component')) $componentComponent = 'component';

        return $componentComponent;
    } 
}