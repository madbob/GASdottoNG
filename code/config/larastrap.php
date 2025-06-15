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

    'translate' => function($identifier) {
        return __('texts.' . $identifier);
    },

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

        'text' => [
            'attributes' => [
                'maxlength' => 190,
            ],
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
        ],

        'radios-model' => [
            'color' => 'outline-info',
        ],

        'checks' => [
            'color' => 'outline-info',
        ],

        'tabs' => [
            'view' => 'tabs',
        ],

        'tabpane' => [
            'reviewCallback' => 'formatTabLabel',
        ],

        'collapse' => [
            'classes' => ['mb-2'],
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
        'username' => [
            'extends' => 'text',
            'params' => [
                'attributes' => [
                    'autocomplete' => 'off',
                    'autocorrect' => 'off',
                    'autocapitalize' => 'none',
                    'spellcheck' => 'false',
                ],
            ],
        ],
        'price' => [
            'extends' => 'text',
            'params' => [
                'classes' => ['number', 'trim-ddigits'],
                'asplaintext' => false,
                'attributes' => [
                    'data-trim-digits' => 2,
                ],
                'reviewCallback' => 'formatPriceToComponent',
            ],
        ],
        'decimal' => [
            'extends' => 'text',
            'params' => [
                'attributes' => [
                    'decimals' => 2,
                ],
                'classes' => ['number', 'trim-ddigits'],
                'reviewCallback' => 'formatDecimalToComponent',
            ],
        ],
        'integer' => [
            'extends' => 'text',
            'params' => [
                'classes' => ['number'],
                'attributes' => [
                    'data-enforce-integer' => '1',
                ],
            ],
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
                'color' => 'dark',
                'postlabel' => '<i class="bi-window"></i>',
            ],
        ],
        'ambutton' => [
            'extends' => 'button',
            'params' => [
                'classes' => ['async-modal'],
                'color' => 'dark',
                'postlabel' => '<i class="bi-window"></i>',
            ],
        ],
        'downloading' => [
            'extends' => 'link',
            'params' => [
                'classes' => ['btn'],
                'color' => 'light',
                'postlabel' => '<i class="bi-download"></i>',
            ],
        ],
        'iconbutton' => [
            'extends' => 'button',
            'params' => [
                'classes' => ['btn-icon'],
            ],
        ],
        'suggestion' => [
            'extends' => 't',
            'params' => [
                'node' => 'div',
                'classes' => ['alert', 'alert-info', 'mb-2', 'small', 'p-2'],
                'attributes' => [
                    'role' => 'alert',
                ],
            ],
        ],

        /*
            mform viene usato per le entità rappresentate dentro una
            loadablelist: al salvataggio, lo slot in cui si trova il form stesso
            viene chiuso ed il relativo header viene aggiornato
        */
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
                'reviewCallback' => 'formatInnerLastUpdater',
            ],
        ],
        'wizardform' => [
            'extends' => 'form',
            'params' => [
                'method' => 'POST',
                'buttons' => [
                    [
                        'color' => 'success',
                        'label' => 'Avanti',
                        'attributes' => ['type' => 'submit'],
                    ],
                ],
            ],
        ],
        'updater' => [
            'extends' => 't',
            'params' => [
                'node' => 'small',
                'name' => 'printable_updater',
                'classes' => ['me-3', 'text-body-secondary', 'float-start', 'text-start', 'pt-2'],
            ],
        ],
        'scheck' => [
            'extends' => 'check',
            'params' => [
                'switch' => true,
                'label' => '',
                'squeeze' => true,
            ],
        ],
        'structchecks' => [
            'extends' => 'checks',
            'params' => [
                'classes' => ['wrapped-flex'],
                'reviewCallback' => 'formatChecksComponentValues',
            ],
        ],
        'remoteaccordion' => [
            'extends' => 'accordionitem',
            'params' => [
                'classes' => ['async-accordion'],
            ],
        ],
        'remotetabpane' => [
            'extends' => 'tabpane',
            'params' => [
                'button_classes' => ['async-tab'],
            ],
        ],
    ],
];
