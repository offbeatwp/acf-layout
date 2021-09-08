<?php
namespace Components\Row;

use OffbeatWP\Components\AbstractComponent;

class Row extends AbstractComponent
{
    public static function settings(): array
    {
        return [];
    }

    public function render($settings)
    {
        $rowComponents = $settings->rowComponents;
        unset($settings->rowComponents);

        $variations = self::variations();
        $variation  = $settings->width ?? '';

        if (empty($variation) || !isset($variations[$variation])) {
            $variation = 'default';
        }

        $rowTheme      = isset($settings->row_theme) ? offbeat('design')->getRowThemeClasses($settings->row_theme) : '';
        $marginTop     = isset($settings->margin_top) ? offbeat('design')->getMarginClasses($settings->margin_top, 'row', 'mt') : '';
        $marginBottom  = isset($settings->margin_bottom) ? offbeat('design')->getMarginClasses($settings->margin_bottom, 'row', 'mb') : '';
        $paddingTop    = isset($settings->padding_top) ? offbeat('design')->getPaddingClasses($settings->padding_top, 'row', 'pt') : '';
        $paddingBottom = isset($settings->padding_bottom) ? offbeat('design')->getPaddingClasses($settings->padding_bottom, 'row', 'pb') : '';

        $rowId                = !empty($settings->id) ? $settings->id : null;
        $additionalRowClasses = $settings->css_classes ?? null;

        $rowClasses = implode(' ', compact('rowTheme', 'marginTop', 'marginBottom', 'paddingTop', 'paddingBottom'));

        if (!empty($additionalRowClasses)) {
            $rowClasses .= " {$additionalRowClasses}";
        }

        return $this->view($variation, [
            'rowComponents' => $rowComponents,
            'rowClasses' => $rowClasses,
            'rowId'      => $rowId,
            'settings'   => $settings,
        ]);
    }

    public static function variations(): array
    {
        return [
            'default'    => [
                'label' => __('Default', 'offbeatwp'),
            ],
            'full_width_content_default' => [
                'label' => __('Full width - Content default', 'offbeatwp'),
            ],
            'full_width' => [
                'label' => __('Full width', 'offbeatwp'),
            ],
            'narrow'     => [
                'label' => __('Narrow', 'offbeatwp'),
            ],
            'narrowest'  => [
                'label' => __('Narrowest', 'offbeatwp'),
            ],
        ];
    }
}
