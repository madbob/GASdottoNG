<?php

return [
    /*
        All the values in "commons" and the different arrays into "elements" may be defined everywhere.
        Those specific for an "elements" tag type will take precedence on those defined in "commons".
    */

    'commons' => [
        'label_width' => ['xs' => 12, 'sm' => 4],
        'input_width' => ['xs' => 12, 'sm' => 8],
    ],

    'translations' => [
        'enabled' => true,
        'params' => ['label', 'help', 'pophelp'],
        'function' => '_i',
    ],

    'elements' => [
        'navbar' => [
            'color' => 'dark',
        ],

        'field' => [
            'label_class' => 'glabel',
        ],

        'form' => [
            'novalidate' => false,
            'view' => 'horizontal',
            'buttons' => [['color' => 'primary', 'label' => 'Salva', 'attributes' => ['type' => 'submit']]],
        ],

        'textarea' => [
            'attributes' => ['rows' => 5],
        ],

        'modal' => [
            'scrollable' => true,
            'buttons' => [['color' => 'secondary', 'label' => 'Chiudi', 'attributes' => ['data-bs-dismiss' => 'modal']]],
            'size' => ['modal-xl', 'modal-fullscreen-md-down'],
        ],

        'check' => [
            'switch' => true,
        ],

        'radios' => [
            'color' => 'outline-info',
            'reviewCallback' => 'formatForDuskTesting',
        ],

        'checks' => [
            'color' => 'outline-info',
            'reviewCallback' => 'formatForDuskTesting',
        ],

        'tabs' => [
            'view' => 'tabs',
        ],
    ],

    'customs' => [
        'datepicker' => [
            'extends' => 'text',
            'params' => [
                'classes' => ['date'],
                'placeholder' => 'Mai',
                'textappend' => '<i class="bi-calendar"></i>',
                'reviewCallback' => 'formatDateToComponent',
                'attributes' => [
                    'autocomplete' => 'off',
                ],
            ],
        ],
        'address' => [
            'extends' => 'text',
            'params' => [
                'classes' => ['address'],
                'textappend' => '<i class="bi-signpost-split"></i>',
            ],
        ],
        'price' => [
            'extends' => 'text',
            'params' => [
                'classes' => ['number', 'trim-2-ddigits'],
                'reviewCallback' => 'formatPriceToComponent',
            ]
        ],
        'decimal' => [
            'extends' => 'text',
            'params' => [
                'decimals' => 2,
                'classes' => ['number'],
                'reviewCallback' => 'formatDecimalToComponent',
            ]
        ],
        'selectobj' => [
            'extends' => 'select',
            'params' => [
                'reviewCallback' => 'formatObjectsToComponent',
            ],
        ],
        'periodic' => [
            'extends' => 'text',
            'params' => [
                'classes' => ['periodic'],
                'placeholder' => 'Mai',
                'textappend' => '<i class="bi-calendar4-week"></i>',
                'reviewCallback' => 'formatPeriodicToComponent',
            ],
        ],
        'mbutton' => [
            'extends' => 'button',
            'params' => [
                'color' => 'light',
                'postlabel' => '<i class="bi-window"></i>',
            ],
        ],
        'ambutton' => [
            'extends' => 'button',
            'params' => [
                'classes' => ['async-modal'],
                'color' => 'light',
                'postlabel' => '<i class="bi-window"></i>',
            ],
        ],
        'mform' => [
            'extends' => 'form',
            'params' => [
                'classes' => ['main-form'],
                'reviewCallback' => 'formatMainFormButtons',
            ],
        ],
        'iform' => [
            'extends' => 'form',
            'params' => [
                'classes' => ['inner-form'],
            ],
        ],
        'scheck' => [
            'extends' => 'check',
            'params' => [
                'switch' => true,
                'label' => '',
                'squeeze' => true,
            ]
        ],
        'structchecks' => [
            'extends' => 'checks',
            'params' => [
                'classes' => ['wrapped-flex'],
                'reviewCallback' => 'formatChecksComponentValues'
            ]
        ],
        'remoteaccordion' => [
            'extends' => 'accordionitem',
            'params' => [
                'classes' => ['async-accordion'],
            ]
        ],
        'remotetabpane' => [
            'extends' => 'tabpane',
            'params' => [
                'button_classes' => ['async-tab'],
            ]
        ]
    ],
];
