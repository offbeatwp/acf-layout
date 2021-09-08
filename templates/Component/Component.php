<?php
namespace Components\Component;

use OffbeatWP\Components\AbstractComponent;

class Component extends AbstractComponent
{
    public static function settings(): array
    {
        return [];
    }

    public function render($settings)
    {
        $componentContent = $settings->componentContent;
        unset($settings->componentContent);

        $marginTop    = offbeat('design')->getMarginClasses($settings->margin_top, 'component', 'mt');
        $marginBottom = offbeat('design')->getMarginClasses($settings->margin_bottom, 'component', 'mb');

        $componentId       = isset($settings->id) || empty($settings->id) ? $settings->id : null;
        $additionalClasses = $settings->css_classes ?? null;

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
