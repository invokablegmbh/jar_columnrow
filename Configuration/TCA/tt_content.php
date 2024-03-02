<?php

use Jar\Columnrow\ItemsProcFuncs\ColorItemsProcFunc;
use Jar\Columnrow\ItemsProcFuncs\ColumnItemsProcFunc;

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

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPlugin(
	[
		'LLL:EXT:jar_columnrow/Resources/Private/Language/locallang_be.xlf:accordion_headline',
		'jarcolumnrow_accordion',
		'EXT:jar_columnrow/Resources/Public/Icons/Accordion.svg',
	],
	'CType',
	'jar_columnrow'
);


$GLOBALS['TCA']['tt_content']['ctrl']['typeicon_classes']['jarcolumnrow_accordion'] = 'jar-accordion-content-icon';
$GLOBALS['TCA']['tt_content']['types']['jarcolumnrow_accordion']['previewRenderer'] = \B13\Container\Backend\Preview\ContainerPreviewRenderer::class;


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
					1 => 'container',
				],
				1 => [
					0 => 'LLL:EXT:jar_columnrow/Resources/Private/Language/locallang_be.xlf:full_width',
					1 => 'container-fluid',
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
			'itemsProcFunc' => ColorItemsProcFunc::class . '->modifyItems',
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
				'levelLinksPosition' => 'bottom',
				'showSynchronizationLink' => false,
				'showPossibleLocalizationRecords' => false,
				'showRemovedLocalizationRecords' => false,
				'showAllLocalizationLink' => false,
				'useSortable' => 1,
				'enabledControls' => [
					'info' => false,
				],
			],
			'behaviour' => [
				'enableCascadingDelete' => true,
				'allowLanguageSynchronization' => false,
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
					'title' => [
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

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes(
	'tt_content',
	'columnrow_columns',
	'jarcolumnrow_accordion',
	'after:header'
);

// enable language overlay for tx_jarcolumnrow_columns when used as accordion (to rename accordion item titles in connected mode)
$GLOBALS['TCA']['tt_content']['types']['jarcolumnrow_accordion']['columnsOverrides']['columnrow_columns'] = [
	'label' => 'LLL:EXT:jar_columnrow/Resources/Private/Language/locallang_be.xlf:items',
	'l10n_mode' => 'prefixLangTitle',
	'l10n_display' => 'defaultAsReadonly',
];


$GLOBALS['TCA']['tt_content']['types']['jarcolumnrow_accordion']['columnsOverrides']['columnrow_columns']['config']['overrideChildTca']['columns']['title'] = [
	'config' => [
		'type' => 'input',
		'size' => 30,
		'eval' => 'trim',
	],
];

// Important: We need l10n_parent in the list, to be able to use it in the reflected service to get the default of connected translations
$GLOBALS['TCA']['tt_content']['types']['jarcolumnrow_accordion']['columnsOverrides']['columnrow_columns']['config']['overrideChildTca']['types'] = [
	0 => [
		'showitem' => 'title,l10n_parent',
	]
];


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

$GLOBALS['TCA']['tx_jarcolumnrow_columns'] = [
	'ctrl' => [
		'title' => 'LLL:EXT:jar_columnrow/Resources/Private/Language/locallang_be.xlf:headline',
		'label' => 'title',
		'label_alt' => 'col_lg', 		
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
			'showitem' => 'title,--palette--;;baseview,l10n_parent',
		],
		1 => [
			'showitem' => 'sys_language_uid,title,--palette--;;desktop,--palette--;;medium,--palette--;;small,--palette--;;mobile,additional_col_class,extended, --div--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:tabs.access, starttime, endtime, hidden,l10n_parent',
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
			'config' =>	$columnWidthConfig			
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