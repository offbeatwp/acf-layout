<?php
namespace OffbeatWP\AcfLayout\Helpers;

use OffbeatWP\AcfLayout\Layout\Renderer;

class AcfLayoutHelper {
    public function renderLayout($rows)
    {
        $renderer = new Renderer();
        return $renderer->renderRows($rows);
    }
}