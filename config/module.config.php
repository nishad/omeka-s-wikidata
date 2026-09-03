<?php
return [
    'controllers' => [
        'factories' => [
            'Wikidata\Controller\Index' => \Wikidata\Service\IndexControllerFactory::class,
        ],
    ],
    'translator' => [
        'translation_file_patterns' => [
            [
                'type' => 'gettext',
                'base_dir' => OMEKA_PATH . '/modules/Wikidata/language',
                'pattern' => '%s.mo',
                'text_domain' => null,
            ],
        ],
    ],
    'view_manager' => [
        'template_path_stack' => [
            OMEKA_PATH . '/modules/Wikidata/view',
        ],
    ],
    'data_types' => [
        'factories' => [
            /* MediaWiki API */
            'wikidata:mediawiki:all' => 'Wikidata\Service\MediaWikiDataTypeFactory',

            /* Wikidata OpenRefine Reconciliation API */
            'wikidata:reconciliation:all' => 'Wikidata\Service\ReconciliationDataTypeFactory',
            'wikidata:reconciliation:persons' => 'Wikidata\Service\ReconciliationDataTypeFactory',
            'wikidata:reconciliation:properties' => 'Wikidata\Service\ReconciliationDataTypeFactory',
            'wikidata:reconciliation:locations' => 'Wikidata\Service\ReconciliationDataTypeFactory',
            'wikidata:reconciliation:languages' => 'Wikidata\Service\ReconciliationDataTypeFactory',
        ],
        /*
         * Data types offered when annotating a value. A type must appear here
         * as well as implement ValueAnnotatingInterface; Omeka intersects this
         * list with an instanceof check.
         */
        'value_annotating' => [
            'wikidata:mediawiki:all',
            'wikidata:reconciliation:all',
            'wikidata:reconciliation:persons',
            'wikidata:reconciliation:properties',
            'wikidata:reconciliation:locations',
            'wikidata:reconciliation:languages',
        ],
    ],
    'router' => [
        'routes' => [
            'admin' => [
                'child_routes' => [
                    'wikidata' => [
                        'type' => 'Literal',
                        'options' => [
                            'route' => '/wikidata-suggest',
                            'defaults' => [
                                '__NAMESPACE__' => 'Wikidata\Controller',
                                'controller' => 'Index',
                            ],
                        ],
                        'may_terminate' => false,
                        'child_routes' => [
                            'proxy' => [
                                'type' => 'Literal',
                                'options' => [
                                    'route' => '/proxy',
                                    'defaults' => [
                                        'action' => 'proxy',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
];
