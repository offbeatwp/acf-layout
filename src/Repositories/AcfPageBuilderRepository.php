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
}