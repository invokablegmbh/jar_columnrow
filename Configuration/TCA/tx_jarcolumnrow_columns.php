<?php

use Jar\Columnrow\ItemsProcFuncs\ColumnItemsProcFunc;

defined('TYPO3') || die();


$columnWidthConfig = [
    'type' => 'select',
    'renderType' => 'selectSingle',
    'items' => [],
    'itemsProcFunc' => ColumnItemsProcFunc::class . '->modifyWidthItems',
    'size' => 1,
    'maxitems' => 1,
    'eval' => '',
];

$columnOrderConfig = [
    'type' => 'select',
    'renderType' => 'selectSingle',
    'items' => [],
    'itemsProcFunc' => ColumnItemsProcFunc::class . '->modifyOrderItems',
    'size' => 1,
    'maxitems' => 1,
    'eval' => '',
];

$columnOffsetConfig = [
    'type' => 'select',
    'renderType' => 'selectSingle',
    'items' => [],
    'itemsProcFunc' => ColumnItemsProcFunc::class . '->modifyOffsetItems',
    'size' => 1,
    'maxitems' => 1,
    'eval' => '',
];
$columnPaddingConfig = [
    'type' => 'select',
    'renderType' => 'selectSingle',
    'items' => [],
    'itemsProcFunc' => ColumnItemsProcFunc::class . '->modifyPaddingItems',
    'size' => 1,
    'maxitems' => 1,
    'eval' => '',
];
$columnAlignmentConfig = [
    'type' => 'select',
    'renderType' => 'selectSingle',
    'items' => [],
    'itemsProcFunc' => ColumnItemsProcFunc::class . '->modifyAlignmentItems',
    'size' => 1,
    'maxitems' => 1,
    'eval' => '',
];

$GLOBALS['TCA']['tx_jarcolumnrow_columns'] = [
    'ctrl' => [
        'title' => 'LLL:EXT:jar_columnrow/Resources/Private/Language/locallang_be.xlf:headline',
        'label' => 'title',
        'label_alt' => 'col_lg',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'versioningWS' => true,
        'languageField' => 'sys_language_uid',
        'transOrigPointerField' => 'l10n_parent',
        'translationSource' => 'l10n_source',
        'delete' => 'deleted',
        'enablecolumns' => [
            'disabled' => 'hidden',
            'starttime' => 'starttime',
            'endtime' => 'endtime',
        ],
        'searchFields' => null,
        'iconfile' => 'EXT:jar_columnrow/Resources/Public/Icons/ColumnRow.svg',
        'typeicon_classes' => [
            'default' => 'jar-column-row-content-icon',
        ],
        'hideTable' => true,
        // your place to set fields that should be displayed in each language individually
        // also when the connected mode is active
        'columnRowSettings' => [
            'individualFieldsPerLanguage' => ['title'],
        ],
        'security' => [
            'ignorePageTypeRestriction' => true,
        ],
    ],
    'types' => [
        0 => [
            'showitem' => '
                title,
                --palette--;;desktop,
                l10n_parent,
                --div--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:tabs.appearance,
                --palette--;;medium,
                --palette--;;small,
                --palette--;;mobile,
                --palette--;;additinal_options,
                additional_col_class,
                --div--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:tabs.access,
                starttime,
                endtime,
                hidden,
                l10n_parent,
            ',
        ]
    ],
    'palettes' => [
        'baseview' => [
            'showitem' => 'col_lg',
        ],
        'desktop' => [
            'showitem' => 'col_lg, order_lg, offset_lg',
            'label' => 'LLL:EXT:jar_columnrow/Resources/Private/Language/locallang_be.xlf:large_desktop',
        ],
        'medium' => [
            'showitem' => 'col_md, order_md, offset_md',
            'label' => 'LLL:EXT:jar_columnrow/Resources/Private/Language/locallang_be.xlf:small_desktop',
        ],
        'small' => [
            'showitem' => 'col_sm, order_sm, offset_sm',
            'label' => 'LLL:EXT:jar_columnrow/Resources/Private/Language/locallang_be.xlf:tablet',
        ],
        'mobile' => [
            'showitem' => 'col_xs, order_xs, offset_xs',
            'label' => 'LLL:EXT:jar_columnrow/Resources/Private/Language/locallang_be.xlf:smartphone',
        ],
        'additinal_options' => [
            'showitem' => 'padding_class, alignment_class, bg_color_class',
            'label' => 'Weitere Optionen',
        ],
    ],
    'columns' => [
        'sys_language_uid' => [
            'exclude' => true,
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.language',
            'config' => [
                'type' => 'language',
            ],
        ],
        'l10n_parent' => [
            'config' => [
                'type' => 'passthrough',
                'default' => 0,
            ],
        ],
        'l10n_diffsource' => [
            'config' => [
                'type' => 'passthrough',
            ],
        ],
        't3ver_label' => [
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.versionLabel',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'max' => 255,
            ],
        ],
        'hidden' => [
            'exclude' => true,
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.hidden',
            'config' => [
                'type' => 'check',
            ],
        ],
        'starttime' => [
            'exclude' => true,
            'behaviour' => [
                'allowLanguageSynchronization' => true,
            ],
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.starttime',
            'config' => [
                'type' => 'datetime',
                'size' => 13,
                'default' => 0,
            ],
        ],
        'endtime' => [
            'exclude' => true,
            'behaviour' => [
                'allowLanguageSynchronization' => true,
            ],
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.endtime',
            'config' => [
                'type' => 'datetime',
                'size' => 13,
                'default' => 0,
                'range' => [
                    'upper' => 2145913200,
                ],
            ],
        ],
        'title' => [
            'exclude' => false,
            'label' => 'LLL:EXT:jar_columnrow/Resources/Private/Language/locallang_be.xlf:title',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim',
            ],
        ],
        'col_lg' => [
            'exclude' => false,
            'label' => 'LLL:EXT:jar_columnrow/Resources/Private/Language/locallang_be.xlf:column_width',
            'config' => $columnWidthConfig
        ],
        'col_md' => [
            'exclude' => false,
            'label' => 'LLL:EXT:jar_columnrow/Resources/Private/Language/locallang_be.xlf:column_width',
            'config' => $columnWidthConfig
        ],
        'col_sm' => [
            'exclude' => false,
            'label' => 'LLL:EXT:jar_columnrow/Resources/Private/Language/locallang_be.xlf:column_width',
            'config' => $columnWidthConfig
        ],
        'col_xs' => [
            'exclude' => false,
            'label' => 'LLL:EXT:jar_columnrow/Resources/Private/Language/locallang_be.xlf:column_width',
            'config' => $columnWidthConfig
        ],
        'order_lg' => [
            'exclude' => false,
            'label' => 'LLL:EXT:jar_columnrow/Resources/Private/Language/locallang_be.xlf:order',
            'config' => $columnOrderConfig
        ],
        'order_md' => [
            'exclude' => false,
            'label' => 'LLL:EXT:jar_columnrow/Resources/Private/Language/locallang_be.xlf:order',
            'config' => $columnOrderConfig
        ],
        'order_sm' => [
            'exclude' => false,
            'label' => 'LLL:EXT:jar_columnrow/Resources/Private/Language/locallang_be.xlf:order',
            'config' => $columnOrderConfig
        ],
        'order_xs' => [
            'exclude' => false,
            'label' => 'LLL:EXT:jar_columnrow/Resources/Private/Language/locallang_be.xlf:order',
            'config' => $columnOrderConfig
        ],
        'offset_lg' => [
            'exclude' => false,
            'label' => 'LLL:EXT:jar_columnrow/Resources/Private/Language/locallang_be.xlf:offset',
            'config' => $columnOffsetConfig
        ],
        'offset_md' => [
            'exclude' => false,
            'label' => 'LLL:EXT:jar_columnrow/Resources/Private/Language/locallang_be.xlf:offset',
            'config' => $columnOffsetConfig
        ],
        'offset_sm' => [
            'exclude' => false,
            'label' => 'LLL:EXT:jar_columnrow/Resources/Private/Language/locallang_be.xlf:offset',
            'config' => $columnOffsetConfig
        ],
        'offset_xs' => [
            'exclude' => false,
            'label' => 'LLL:EXT:jar_columnrow/Resources/Private/Language/locallang_be.xlf:offset',
            'config' => $columnOffsetConfig
        ],
        'padding_class' => [
            'exclude' => false,
            'label' => 'LLL:EXT:jar_columnrow/Resources/Private/Language/locallang_be.xlf:padding_class',
            'config' => $columnPaddingConfig
        ],
        'alignment_class' => [
            'exclude' => false,
            'label' => 'LLL:EXT:jar_columnrow/Resources/Private/Language/locallang_be.xlf:alignment_class',
            'config' => $columnAlignmentConfig
        ],
        'bg_color_class' => [
            'exclude' => false,
            'label' => 'LLL:EXT:jar_columnrow/Resources/Private/Language/locallang_be.xlf:bg_color_class',
            'config' => [
                'type' => 'color',
                'size' => 10,
                'default' => 'transparent', // Optional: Standardfarbe
                'valuePicker' => [
                    'items' => [
                        ['Weiß', '#ffffff'],
                        ['Schwarz', '#000000'],
                        ['Rot', '#e30613'],
                        ['Grau', '#dadada'],
                    ],
                ],
            ],
        ],
        'additional_col_class' => [
            'exclude' => false,
            'label' => 'LLL:EXT:jar_columnrow/Resources/Private/Language/locallang_be.xlf:css_class',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim',
            ],
        ],
        'parent_column_row' => [
            'exclude' => false,
            'label' => 'parent_column_row',
            'config' => [
                'type' => 'passthrough',
            ],
        ],
    ],
];
