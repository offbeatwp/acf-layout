<?php
namespace Components\Component;

use \Raow\Components\AbstractComponent;

class Component extends AbstractComponent
{
    public function render($settings)
    {
        $componentContent = $settings->componentContent;
        unset($settings->componentContent);

        $marginTop    = raowApp('design')->getMarginClasses($settings->margin_top, 'component', 'mt');
        $marginBottom = raowApp('design')->getMarginClasses($settings->margin_bottom, 'component', 'mb');

        $componentId       = isset($settings->id) || empty($settings->id) ? $settings->id : null;
        $additionalClasses = isset($settings->css_classes) ? $settings->css_classes : null;

        $componentClasses = implode(' ', compact('marginTop', 'marginBottom'));

        if (!empty($additionalClasses)) {
            $componentClasses .= " {$additionalClasses}";
        }

        return $this->view('component', [
            'componentContent' => $componentContent,
            'componentClasses' => $componentClasses,
            'componentId'      => $componentId,
            'settings'         => $settings,
        ]);
    }

}
