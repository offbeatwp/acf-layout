<?php
namespace Components\Row;

class Row extends \Raow\Components\AbstractComponent
{
    public function render($settings)
    {
        $rowContent = $settings->rowContent;
        unset($settings->rowContent);

        $variations = self::variations();
        $variation  = isset($settings->width) ? $settings->width : '';

        if (empty($variation) || !isset($variations[$variation])) {
            $variation = 'default';
        }

        $rowTheme      = raowApp('design')->getRowThemeClasses($settings->row_theme);
        $marginTop     = raowApp('design')->getMarginClasses($settings->margin_top, 'row', 'mt');
        $marginBottom  = raowApp('design')->getMarginClasses($settings->margin_bottom, 'row', 'mb');
        $paddingTop    = raowApp('design')->getPaddingClasses($settings->padding_top, 'row', 'pt');
        $paddingBottom = raowApp('design')->getPaddingClasses($settings->padding_bottom, 'row', 'pb');

        $rowId                = isset($settings->id) || empty($settings->id) ? $settings->id : null;
        $additionalRowClasses = isset($settings->css_classes) ? $settings->css_classes : null;

        $rowClasses = implode(' ', compact('rowTheme', 'marginTop', 'marginBottom', 'paddingTop', 'paddingBottom'));

        if (!empty($additionalRowClasses)) {
            $rowClasses .= " {$additionalRowClasses}";
        }

        return $this->view($variation, [
            'rowContent' => $rowContent,
            'rowClasses' => $rowClasses,
            'rowId'      => $rowId,
            'settings'   => $settings,
        ]);
    }

    public static function variations()
    {
        return [
            'default'    => [
                'label' => __('Default', 'raow'),
            ],
            'full_width_content_default' => [
                'label' => __('Full width - Content default', 'raow'),
            ],
            'full_width' => [
                'label' => __('Full width', 'raow'),
            ],
            'narrow'     => [
                'label' => __('Narrow', 'raow'),
            ],
            'narrowest'  => [
                'label' => __('Narrowest', 'raow'),
            ],
        ];
    }
}
