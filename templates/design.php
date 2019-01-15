<?php
return [
    'row_themes' => [
        'default' => [
            'label'     => __('Default', 'raow'),
            'classes'   => 'theme theme--default',
            'sub_themes'  => [

            ],
        ],

    ],
    'margins' => function ($context) {
        $default = '';

        if ($context === 'row') {
            $default = '{{prefix}}-5 {{prefix}}-lg-7';
        } elseif ($context === 'component') {
            $default = '{{prefix}}-5 {{prefix}}-lg-7';
        }

        return [
            'default' => [
                'label'  => __('Default', 'raow'),
                'classes' => $default,
            ],
            'none' => [
                'label' => __('None', 'raow'),
                'classes' => '{{prefix}}-0',
            ],
            'small' => [
                'label' => __('Small', 'raow'),
                'classes' => '{{prefix}}-2',
            ],
            'medium' => [
                'label' => __('Medium', 'raow'),
                'classes' => '{{prefix}}-5',
            ],
            'large' => [
                'label' => __('Large', 'raow'),
                'classes' => '{{prefix}}-5 {{prefix}}-lg-7',
            ],
        ];
    },
    'paddings' => function ($context) {
        return [
            'default' => [
                'label'  => __('Default', 'raow'),
                'classes' => '',
            ],
            'none' => [
                'label' => __('None', 'raow'),
                'classes' => '{{prefix}}-0',
            ],
            'small' => [
                'label' => __('Small', 'raow'),
                'classes' => '{{prefix}}-2',
            ],
            'medium' => [
                'label' => __('Medium', 'raow'),
                'classes' => '{{prefix}}-5',
            ],
            'large' => [
                'label' => __('Large', 'raow'),
                'classes' => '{{prefix}}-5 {{prefix}}-lg-7',
            ],
        ];
    },
];