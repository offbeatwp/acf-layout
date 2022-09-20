<?php
namespace OffbeatWP\AcfLayout\Components\Component;

use OffbeatWP\Components\AbstractComponent;

class Component extends AbstractComponent
{
    static function setttings() {
        return [];
    }

    public function render($settings)
    {
        $componentContent = $settings->componentContent;
        unset($settings->componentContent);

        $marginTop    = isset($settings->margin_top) ? offbeat('design')->getMarginClasses($settings->margin_top, 'component', 'mt') : '';
        $marginBottom = isset($settings->margin_bottom) ? offbeat('design')->getMarginClasses($settings->margin_bottom, 'component', 'mb') : '';

        $componentId       = isset($settings->id) && !empty($settings->id) ? $settings->id : null;
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

    // public static function form()
    // {
    //     return [
    //         \OffbeatWP\AcfLayout\Fields\DisplaySettings::get()
    //     ];
    // }
}
