<?php
defined('TYPO3_MODE') || die();

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPlugin(
	[
		'LLL:EXT:jar_columnrow/Resources/Private/Language/locallang_be.xlf:headline',
		'jarcolumnrow_columnrow',
		'EXT:jar_columnrow/Resources/Public/Icons/ColumnRow.svg',
	],
	'CType',
	'jar_columnrow'
);

$GLOBALS['TCA']['tt_content']['ctrl']['typeicon_classes']['jarcolumnrow_columnrow'] = 'jar-column-row-content-icon';

$GLOBALS['TCA']['tt_content']['types']['jarcolumnrow_columnrow']['previewRenderer'] = \B13\Container\Backend\Preview\ContainerPreviewRenderer::class;

$contentTableColumns = [
	'feditorce_feditor_columnrow_content_width' => [
		'exclude' => false,
		'label' => 'contentWidth',
		'l10n_mode' => 'exclude',
		'config' => [
			'type' => 'select',
			'renderType' => 'selectSingle',
			'items' => [
				0 => [
					0 => 'content_width',
					1 => 'content',
				],
				1 => [
					0 => 'fill_width',
					1 => 'fullwidth',
				],
			],
			'size' => 1,
			'maxitems' => 1,
			'eval' => '',
		],
	],
	'feditorce_feditor_columnrow_select_background' => [
		'exclude' => false,
		'label' => 'selectBackground',
		'l10n_mode' => 'exclude',
		'config' => [
			'type' => 'select',
			'renderType' => 'selectSingle',
			'items' => [
				0 => [
					0 => 'choose',
					1 => 0,
				],
				1 => [
					0 => 'background_color',
					1 => 1,
				],
				2 => [
					0 => 'background_graph',
					1 => 2,
				],
			],
			'size' => 1,
			'maxitems' => 1,
			'eval' => '',
		],
		'onChange' => 'reload',
	],
	'feditorce_feditor_columnrow_row_background' => [
		'exclude' => false,
		'label' => 'rowBackground',
		'l10n_mode' => 'exclude',
		'config' => [
			'type' => 'select',
			'renderType' => 'selectSingle',
			'items' => [
				0 => [
					0 => 'user_customized',
					1 => 'user',
				],
			],
			'size' => 1,
			'maxitems' => 1,
			'eval' => '',
		],
		'displayCond' => 'FIELD:feditorce_feditor_columnrow_select_background:=:1',
		'onChange' => 'reload',
	],
	'feditorce_feditor_columnrow_row_user_background' => [
		'exclude' => false,
		'label' => 'custom_background',
		'l10n_mode' => 'exclude',
		'config' => [
			'type' => 'input',
			'size' => 30,
			'eval' => 'trim',
		],
		'displayCond' => 'FIELD:feditorce_feditor_columnrow_row_background:=:user',
	],
	'feditorce_feditor_columnrow_row_background_image' => [
		'exclude' => false,
		'label' => 'background_graph',
		'l10n_mode' => 'exclude',
		'config' =>  [
			'type' => 'inline',
			'foreign_table' => 'sys_file_reference',
			'foreign_field' => 'uid_foreign',
			'foreign_sortby' => 'sorting_foreign',
			'foreign_table_field' => 'tablenames',
			'foreign_match_fields' => [
				'fieldname' => 'feditorce_feditor_columnrow_row_background_image',
			],
			'foreign_label' => 'uid_local',
			'foreign_selector' => 'uid_local',
			'overrideChildTca' => [
				'columns' => [
					'uid_local' => [
						'config' => [
							'appearance' => [
								'elementBrowserType' => 'file',
								'elementBrowserAllowed' => 'gif,jpg,jpeg,tif,tiff,bmp,pcx,tga,png,pdf,ai,svg',
							],
						],
					],
				],
				'types' => [
					2 => [
						'showitem' => '--palette--;LLL:EXT:lang/Resources/Private/Language/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,--palette--;;filePalette',
					],
				],
			],
			'filter' => [
				0 => [
					'userFunc' => 'TYPO3\\CMS\\Core\\Resource\\Filter\\FileExtensionFilter->filterInlineChildren',
					'parameters' => [
						'allowedFileExtensions' => 'gif,jpg,jpeg,tif,tiff,bmp,pcx,tga,png,pdf,ai,svg',
						'disallowedFileExtensions' => '',
					],
				],
			],
			'appearance' => [
				'useSortable' => true,
				'headerThumbnail' => [
					'field' => 'uid_local',
					'height' => '45m',
				],
				'enabledControls' => [
					'info' => true,
					'new' => false,
					'dragdrop' => true,
					'sort' => false,
					'hide' => true,
					'delete' => true,
				],
				'createNewRelationLinkTitle' => 'LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:images.addFileReference',
			],
			'maxitems' => 1,
			'minitems' => 0,
		],
		'displayCond' => 'FIELD:feditorce_feditor_columnrow_select_background:=:2',
	],
	'feditorce_feditor_columnrow_additional_row_class' => [
		'exclude' => false,
		'label' => 'add_row_class',
		'l10n_mode' => 'exclude',
		'config' => [
			'type' => 'input',
			'size' => 30,
			'eval' => 'trim',
		],
	],
	'feditorce_feditor_columnrow_columns' => [
		'exclude' => false,
		'label' => 'columns',
		'l10n_mode' => 'exclude',
		'config' => [
			'type' => 'inline',
			'foreign_table' => 'tx_jarcolumnrow_columns',
			'foreign_field' => 'parent_column_row',
			'foreign_sortby' => 'sorting',
			'minitems' => '1',
			'maxitems' => 99,
			'appearance' => [
				'collapseAll' => 1,
				'levelLinksPosition' => 'bottom',
				'showSynchronizationLink' => true,
				'showPossibleLocalizationRecords' => true,
				'showRemovedLocalizationRecords' => false,
				'showAllLocalizationLink' => true,
				'useSortable' => 1,
				'enabledControls' => [
					'info' => false,
				],
			],
			'behaviour' => [
				'enableCascadingDelete' => true,
				'allowLanguageSynchronization' => true,
			],
			'overrideChildTca' => [
				'columns' => [
					'sys_language_uid' => [
						'config' => [
							'type' => 'passthrough',
							'renderType' => NULL,
							'special' => NULL,
						],
					],
				],
			],
		],
	]
];

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('tt_content', $contentTableColumns);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes(
	'tt_content',
	'feditorce_feditor_columnrow_content_width,
	feditorce_feditor_columnrow_select_background,
	feditorce_feditor_columnrow_row_background,
	feditorce_feditor_columnrow_row_user_background,
	feditorce_feditor_columnrow_row_background_image,
	feditorce_feditor_columnrow_additional_row_class,
	feditorce_feditor_columnrow_columns',
	'jarcolumnrow_columnrow',
	'after:header'
);
// add $contentColuns to content ctpye 'feditor_columnrow'


$GLOBALS['TCA']['tx_jarcolumnrow_columns'] = [
	'ctrl' => [
		'title' => 'LLL:EXT:j77_template/dummylang.xlf:tx_j77template_fedttc_feditor_columnrow_columns',
		'label' => 'col_lg',
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'cruser_id' => 'cruser_id',
		'versioningWS' => true,
		'languageField' => 'sys_language_uid',
		'transOrigPointerField' => 'l10n_parent',
		'transOrigDiffSourceField' => 'l10n_diffsource',
		'delete' => 'deleted',
		'enablecolumns' => [
			'disabled' => 'hidden',
			'starttime' => 'starttime',
			'endtime' => 'endtime',
		],
		'searchFields' => null,
		'iconfile' => 'EXT:jar_feditor/Resources/Public/Icons/DefaultContentIcon.svg',
		'typeicon_classes' => [
			'default' => 'jar-column-row-content-icon',
		],
		'hideTable' => true
	],
	'types' => [
		1 => [
			'showitem' => 'sys_language_uid, l10n_parent, l10n_diffsource, extended,col_lg,col_md,col_sm,col_xs,order_lg,order_md,order_sm,order_xs,offset_lg,offset_md,offset_sm,offset_xs,additional_col_class,parent_column_row, --div--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:tabs.access, starttime, endtime, hidden',
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
			'displayCond' => 'FIELD:sys_language_uid:>:0',
			'exclude' => true,
			'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.l18n_parent',
			'config' => [
				'type' => 'select',
				'renderType' => 'selectSingle',
				'default' => 0,
				'items' => [
					0 => [
						0 => '',
						1 => 0,
					],
				],
				'foreign_table' => 'tx_j77template_fedttc_feditor_columnrow_columns',
				'foreign_table_where' => 'AND tx_j77template_fedttc_feditor_columnrow_columns.pid=###CURRENT_PID### AND tx_j77template_fedttc_feditor_columnrow_columns.sys_language_uid IN (-1,0)',
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
				'type' => 'input',
				'renderType' => 'inputDateTime',
				'size' => 13,
				'eval' => 'datetime',
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
				'type' => 'input',
				'renderType' => 'inputDateTime',
				'size' => 13,
				'eval' => 'datetime',
				'default' => 0,
				'range' => [
					'upper' => 2145913200,
				],
			],
		],
		'extended' => [
			'exclude' => false,
			'label' => 'column_extended',
			'config' => [
				'type' => 'check',
				'default' => 0,
			],
			'onChange' => 'reload',
		],		
		'col_lg' => [
			'exclude' => false,
			'label' => 'column_width_large_desktop',
			'config' => [
				'type' => 'select',
				'renderType' => 'selectSingle',
				'items' => [					
					1 => [
						0 => '[12] 100%',
						1 => 12,
					],
					2 => [
						0 => '[11] 91.66%',
						1 => 11,
					],
					3 => [
						0 => '[10] 83.33%',
						1 => 10,
					],
					4 => [
						0 => '[9] 75%',
						1 => 9,
					],
					5 => [
						0 => '[8] 66.66%',
						1 => 8,
					],
					6 => [
						0 => '[7] 58.33%',
						1 => 7,
					],
					7 => [
						0 => '[6] 50%',
						1 => 6,
					],
					8 => [
						0 => '[5] 41.66%',
						1 => 5,
					],
					9 => [
						0 => '[4] 33.33%',
						1 => 4,
					],
					10 => [
						0 => '[3] 25%',
						1 => 3,
					],
					11 => [
						0 => '[2] 16.66%',
						1 => 2,
					],
					12 => [
						0 => '[1] 8.33%',
						1 => 1,
					],
					/* TODO: ONLY MAKE SENSE WHEN EXTENDED IS TRUE */
					13 => [
						0 => 'do_not_show',
						1 => -1,
					],
				],
				'size' => 1,
				'maxitems' => 1,
				'eval' => '',
			]			
		],
		'col_md' => [
			'exclude' => false,
			'label' => 'column_width_small_desktop',
			'config' => [
				'type' => 'select',
				'renderType' => 'selectSingle',
				'items' => [
					0 => [
						0 => 'item_automatic',
						1 => 0,
					],
					1 => [
						0 => '[12] 100%',
						1 => 12,
					],
					2 => [
						0 => '[11] 91.66%',
						1 => 11,
					],
					3 => [
						0 => '[10] 83.33%',
						1 => 10,
					],
					4 => [
						0 => '[9] 75%',
						1 => 9,
					],
					5 => [
						0 => '[8] 66.66%',
						1 => 8,
					],
					6 => [
						0 => '[7] 58.33%',
						1 => 7,
					],
					7 => [
						0 => '[6] 50%',
						1 => 6,
					],
					8 => [
						0 => '[5] 41.66%',
						1 => 5,
					],
					9 => [
						0 => '[4] 33.33%',
						1 => 4,
					],
					10 => [
						0 => '[3] 25%',
						1 => 3,
					],
					11 => [
						0 => '[2] 16.66%',
						1 => 2,
					],
					12 => [
						0 => '[1] 8.33%',
						1 => 1,
					],
					13 => [
						0 => 'do_not_show',
						1 => -1,
					],
				],
				'size' => 1,
				'maxitems' => 1,
				'eval' => '',
			],
			'displayCond' => 'FIELD:extended:=:1',
		],
		'col_sm' => [
			'exclude' => false,
			'label' => 'column_width_tablet',
			'config' => [
				'type' => 'select',
				'renderType' => 'selectSingle',
				'items' => [
					0 => [
						0 => 'item_automatic',
						1 => 0,
					],
					1 => [
						0 => '[12] 100%',
						1 => 12,
					],
					2 => [
						0 => '[11] 91.66%',
						1 => 11,
					],
					3 => [
						0 => '[10] 83.33%',
						1 => 10,
					],
					4 => [
						0 => '[9] 75%',
						1 => 9,
					],
					5 => [
						0 => '[8] 66.66%',
						1 => 8,
					],
					6 => [
						0 => '[7] 58.33%',
						1 => 7,
					],
					7 => [
						0 => '[6] 50%',
						1 => 6,
					],
					8 => [
						0 => '[5] 41.66%',
						1 => 5,
					],
					9 => [
						0 => '[4] 33.33%',
						1 => 4,
					],
					10 => [
						0 => '[3] 25%',
						1 => 3,
					],
					11 => [
						0 => '[2] 16.66%',
						1 => 2,
					],
					12 => [
						0 => '[1] 8.33%',
						1 => 1,
					],
					13 => [
						0 => 'do_not_show',
						1 => -1,
					],
				],
				'size' => 1,
				'maxitems' => 1,
				'eval' => '',
			],
			'displayCond' => 'FIELD:extended:=:1',
		],
		'col_xs' => [
			'exclude' => false,
			'label' => 'column_width_mobile',
			'config' => [
				'type' => 'select',
				'renderType' => 'selectSingle',
				'items' => [
					0 => [
						0 => 'item_automatic',
						1 => 0,
					],
					1 => [
						0 => '[12] 100%',
						1 => 12,
					],
					2 => [
						0 => '[11] 91.66%',
						1 => 11,
					],
					3 => [
						0 => '[10] 83.33%',
						1 => 10,
					],
					4 => [
						0 => '[9] 75%',
						1 => 9,
					],
					5 => [
						0 => '[8] 66.66%',
						1 => 8,
					],
					6 => [
						0 => '[7] 58.33%',
						1 => 7,
					],
					7 => [
						0 => '[6] 50%',
						1 => 6,
					],
					8 => [
						0 => '[5] 41.66%',
						1 => 5,
					],
					9 => [
						0 => '[4] 33.33%',
						1 => 4,
					],
					10 => [
						0 => '[3] 25%',
						1 => 3,
					],
					11 => [
						0 => '[2] 16.66%',
						1 => 2,
					],
					12 => [
						0 => '[1] 8.33%',
						1 => 1,
					],
					13 => [
						0 => 'do_not_show',
						1 => -1,
					],
				],
				'size' => 1,
				'maxitems' => 1,
				'eval' => '',
			],
			'displayCond' => 'FIELD:extended:=:1',
		],
		'order_lg' => [
			'exclude' => false,
			'label' => 'order_large_desktop',
			'config' => [
				'type' => 'select',
				'renderType' => 'selectSingle',
				'items' => [
					0 => [
						0 => 'item_up_high',
						1 => 0,
					],
					1 => [
						0 => 'item_bottom',
						1 => -1,
					],
				],
				'size' => 1,
				'maxitems' => 1,
				'eval' => '',
			],
			'displayCond' => 'FIELD:extended:=:1',
		],
		'order_md' => [
			'exclude' => false,
			'label' => 'order_small_desktop',
			'config' => [
				'type' => 'select',
				'renderType' => 'selectSingle',
				'items' => [
					0 => [
						0 => 'item_up_high',
						1 => 0,
					],
					1 => [
						0 => 'item_bottom',
						1 => -1,
					],
				],
				'size' => 1,
				'maxitems' => 1,
				'eval' => '',
			],
			'displayCond' => 'FIELD:extended:=:1',
		],
		'order_sm' => [
			'exclude' => false,
			'label' => 'order_tablet',
			'config' => [
				'type' => 'select',
				'renderType' => 'selectSingle',
				'items' => [
					0 => [
						0 => 'item_up_high',
						1 => 0,
					],
					1 => [
						0 => 'item_bottom',
						1 => -1,
					],
				],
				'size' => 1,
				'maxitems' => 1,
				'eval' => '',
			],
			'displayCond' => 'FIELD:extended:=:1',
		],
		'order_xs' => [
			'exclude' => false,
			'label' => 'order_smartphone',
			'config' => [
				'type' => 'select',
				'renderType' => 'selectSingle',
				'items' => [
					0 => [
						0 => 'item_up_high',
						1 => 0,
					],
					1 => [
						0 => 'item_bottom',
						1 => -1,
					],
				],
				'size' => 1,
				'maxitems' => 1,
				'eval' => '',
			],
			'displayCond' => 'FIELD:extended:=:1',
		],
		'offset_lg' => [
			'exclude' => false,
			'label' => 'offset_large_desktop',
			'config' => [
				'type' => 'select',
				'renderType' => 'selectSingle',
				'items' => [
					0 => [
						0 => 'no_offset',
						1 => 0,
					],
				],
				'size' => 1,
				'maxitems' => 1,
				'eval' => '',
			],
			'displayCond' => 'FIELD:extended:=:1',
		],
		'offset_md' => [
			'exclude' => false,
			'label' => 'offset_small_desktop',
			'config' => [
				'type' => 'select',
				'renderType' => 'selectSingle',
				'items' => [
					0 => [
						0 => 'no_offset',
						1 => 0,
					],
				],
				'size' => 1,
				'maxitems' => 1,
				'eval' => '',
			],
			'displayCond' => 'FIELD:extended:=:1',
		],
		'offset_sm' => [
			'exclude' => false,
			'label' => 'offset_tablet',
			'config' => [
				'type' => 'select',
				'renderType' => 'selectSingle',
				'items' => [
					0 => [
						0 => 'no_offset',
						1 => 0,
					],
				],
				'size' => 1,
				'maxitems' => 1,
				'eval' => '',
			],
			'displayCond' => 'FIELD:extended:=:1',
		],
		'offset_xs' => [
			'exclude' => false,
			'label' => 'offset_smartphone',
			'config' => [
				'type' => 'select',
				'renderType' => 'selectSingle',
				'items' => [
					0 => [
						0 => 'no_offset',
						1 => 0,
					],
				],
				'size' => 1,
				'maxitems' => 1,
				'eval' => '',
			],
			'displayCond' => 'FIELD:extended:=:1',
		],
		'additional_col_class' => [
			'exclude' => false,
			'label' => 'add_column_class',
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

/*
$GLOBALS['TCA'] = [
  'tt_content' => [
		'types' => [
			'Jar\\Feditor\\Domain\\Model\\ContentElement\\Feditor\\Columnrow' => [
        'showitem' => '--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,
                     --palette--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:palette.general;general,
                     --palette--;;headers,
                     feditorce_feditor_columnrow_content_width,feditorce_feditor_columnrow_select_background,feditorce_feditor_columnrow_row_background,feditorce_feditor_columnrow_row_user_background,feditorce_feditor_columnrow_row_background_image,feditorce_feditor_columnrow_additional_row_class,feditorce_feditor_columnrow_columns,
                     --div--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:tabs.appearance,
                     --palette--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:palette.frames;frames,
                     --palette--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:palette.appearanceLinks;appearanceLinks,
                     --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:language,
                     --palette--;;language,--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access,
                     --palette--;;hidden,
                     --palette--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:palette.access;access,                     
                     --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:categories, categories,
                     --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:notes,rowDescription,
                     --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:extended,
                     --div--;LLL:EXT:flux/Resources/Private/Language/locallang.xlf:tt_content.tabs.relation,tx_flux_parent,tx_flux_column,tx_flux_children;LLL:EXT:flux/Resources/Private/Language/locallang.xlf:tt_content.tx_flux_children
            ',       
      ],   
  ]
];

*/