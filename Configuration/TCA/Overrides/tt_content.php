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
	'columnrow_content_width' => [
		'exclude' => false,
		'label' => 'LLL:EXT:jar_columnrow/Resources/Private/Language/locallang_be.xlf:width_of_content',
		'l10n_mode' => 'exclude',
		'config' => [
			'type' => 'select',
			'renderType' => 'selectSingle',
			'items' => [
				0 => [
					0 => 'LLL:EXT:jar_columnrow/Resources/Private/Language/locallang_be.xlf:content_width',
					1 => 'content',
				],
				1 => [
					0 => 'LLL:EXT:jar_columnrow/Resources/Private/Language/locallang_be.xlf:full_width',
					1 => 'fullwidth',
				],
			],
			'size' => 1,
			'maxitems' => 1,
			'eval' => '',
		],
	],
	'columnrow_select_background' => [
		'exclude' => false,
		'label' => 'LLL:EXT:jar_columnrow/Resources/Private/Language/locallang_be.xlf:background',
		'l10n_mode' => 'exclude',
		'config' => [
			'type' => 'select',
			'renderType' => 'selectSingle',
			'items' => [
				0 => [
					0 => 'LLL:EXT:jar_columnrow/Resources/Private/Language/locallang_be.xlf:default',
					1 => 0,
				],
				1 => [
					0 => 'LLL:EXT:jar_columnrow/Resources/Private/Language/locallang_be.xlf:background_color',
					1 => 1,
				],
				2 => [
					0 => 'LLL:EXT:jar_columnrow/Resources/Private/Language/locallang_be.xlf:background_graph',
					1 => 2,
				],
			],
			'size' => 1,
			'maxitems' => 1,
			'eval' => '',
		],
		'onChange' => 'reload',
	],
	'columnrow_row_background' => [
		'exclude' => false,
		'label' => 'LLL:EXT:jar_columnrow/Resources/Private/Language/locallang_be.xlf:background_color',
		'l10n_mode' => 'exclude',
		'config' => [
			'type' => 'select',
			'renderType' => 'selectSingle',
			'items' => [
				0 => [
					0 => 'LLL:EXT:jar_columnrow/Resources/Private/Language/locallang_be.xlf:user_customized',
					1 => 'user',
				],
			],
			'size' => 1,
			'maxitems' => 1,
			'eval' => '',
		],
		'displayCond' => 'FIELD:columnrow_select_background:=:1',
		'onChange' => 'reload',
	],
	'columnrow_row_user_background' => [
		'exclude' => false,
		'label' => 'LLL:EXT:jar_columnrow/Resources/Private/Language/locallang_be.xlf:custom_background',
		'l10n_mode' => 'exclude',
		'config' => [
			'type' => 'input',
			'renderType' => 'colorpicker',
			'size' => 10,
			'eval' => 'trim',
		],
		'displayCond' =>
		[
			'AND' => [
				'FIELD:columnrow_row_background:=:user',
				'FIELD:columnrow_select_background:=:1',
			],
		], 
	],
	'columnrow_row_background_image' => [
		'exclude' => false,
		'label' => 'LLL:EXT:jar_columnrow/Resources/Private/Language/locallang_be.xlf:background_graph',
		'l10n_mode' => 'exclude',
		'config' =>  [
			'type' => 'inline',
			'foreign_table' => 'sys_file_reference',
			'foreign_field' => 'uid_foreign',
			'foreign_sortby' => 'sorting_foreign',
			'foreign_table_field' => 'tablenames',
			'foreign_match_fields' => [
				'fieldname' => 'columnrow_row_background_image',
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
		'displayCond' => 'FIELD:columnrow_select_background:=:2',
	],
	'columnrow_additional_row_class' => [
		'exclude' => false,
		'label' => 'LLL:EXT:jar_columnrow/Resources/Private/Language/locallang_be.xlf:css_class',
		'l10n_mode' => 'exclude',
		'config' => [
			'type' => 'input',
			'size' => 30,
			'eval' => 'trim',
		],
	],
	'columnrow_columns' => [
		'exclude' => false,
		'label' => 'LLL:EXT:jar_columnrow/Resources/Private/Language/locallang_be.xlf:columns',
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
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addFieldsToPalette(
	'tt_content',
	'columnrow_rowappearance',
	'		
		columnrow_select_background,
		--linebreak--,				
		columnrow_row_background,
		columnrow_row_user_background,
		columnrow_row_background_image,
		--linebreak--,		
		columnrow_content_width,
		columnrow_additional_row_class,
	',
	'after:header'
);

$GLOBALS['TCA']['tt_content']['palettes']['columnrow_rowappearance']['label'] = 'LLL:EXT:jar_columnrow/Resources/Private/Language/locallang_be.xlf:rowappearance';
	
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes(
	'tt_content',
	'columnrow_columns,--palette--;;columnrow_rowappearance',
	'jarcolumnrow_columnrow',
	'after:header'
);


$GLOBALS['TCA']['tx_jarcolumnrow_columns'] = [
	'ctrl' => [
		'title' => 'LLL:EXT:jar_columnrow/Resources/Private/Language/locallang_be.xlf:headline',
		'label' => 'col_lg',
		'tstamp' => 'tstamp',
		'type' => 'extended',
		'crdate' => 'crdate',
		'cruser_id' => 'cruser_id',
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
		'hideTable' => true
	],
	'types' => [
		0 => [
			'showitem' => '--palette--;;baseview',
		],
		1 => [
			'showitem' => 'sys_language_uid,--palette--;;desktop,--palette--;;medium,--palette--;;small,--palette--;;mobile,additional_col_class,extended, --div--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:tabs.access, starttime, endtime, hidden',
		],
	],
	'palettes' => [
		'baseview' => [
			'showitem' => 'col_lg, extended',
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
			'label' => 'LLL:EXT:jar_columnrow/Resources/Private/Language/locallang_be.xlf:show_extended',
			'config' => [
				'type' => 'check',
				'default' => 0,
			],
			'onChange' => 'reload',
		],		
		'col_lg' => [
			'exclude' => false,
			'label' => 'LLL:EXT:jar_columnrow/Resources/Private/Language/locallang_be.xlf:column_width',
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
			'label' => 'LLL:EXT:jar_columnrow/Resources/Private/Language/locallang_be.xlf:column_width',
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
			]
		],
		'col_sm' => [
			'exclude' => false,
			'label' => 'LLL:EXT:jar_columnrow/Resources/Private/Language/locallang_be.xlf:column_width',
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
			]
		],
		'col_xs' => [
			'exclude' => false,
			'label' => 'LLL:EXT:jar_columnrow/Resources/Private/Language/locallang_be.xlf:column_width',
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
			]
		],
		'order_lg' => [
			'exclude' => false,
			'label' => 'LLL:EXT:jar_columnrow/Resources/Private/Language/locallang_be.xlf:order',
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
			]
		],
		'order_md' => [
			'exclude' => false,
			'label' => 'LLL:EXT:jar_columnrow/Resources/Private/Language/locallang_be.xlf:order',
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
			]
		],
		'order_sm' => [
			'exclude' => false,
			'label' => 'LLL:EXT:jar_columnrow/Resources/Private/Language/locallang_be.xlf:order',
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
			]
		],
		'order_xs' => [
			'exclude' => false,
			'label' => 'LLL:EXT:jar_columnrow/Resources/Private/Language/locallang_be.xlf:order',
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
			]
		],
		'offset_lg' => [
			'exclude' => false,
			'label' => 'LLL:EXT:jar_columnrow/Resources/Private/Language/locallang_be.xlf:offset',
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
			]
		],
		'offset_md' => [
			'exclude' => false,
			'label' => 'LLL:EXT:jar_columnrow/Resources/Private/Language/locallang_be.xlf:offset',
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
			]
		],
		'offset_sm' => [
			'exclude' => false,
			'label' => 'LLL:EXT:jar_columnrow/Resources/Private/Language/locallang_be.xlf:offset',
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
			]
		],
		'offset_xs' => [
			'exclude' => false,
			'label' => 'LLL:EXT:jar_columnrow/Resources/Private/Language/locallang_be.xlf:offset',
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
			]
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