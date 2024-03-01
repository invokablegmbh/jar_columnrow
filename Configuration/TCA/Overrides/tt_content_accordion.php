<?php

use Jar\Columnrow\ItemsProcFuncs\ColorItemsProcFunc;
use Jar\Columnrow\ItemsProcFuncs\ColumnItemsProcFunc;

defined('TYPO3_MODE') || die();

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPlugin(
	[
		'LLL:EXT:jar_columnrow/Resources/Private/Language/locallang_be.xlf:accordion_headline',
		'jarcolumnrow_accordion',
		'EXT:jar_columnrow/Resources/Public/Icons/Accordion.svg',
	],
	'CType',
	'jar_columnrow'
);


$GLOBALS['TCA']['tt_content']['ctrl']['typeicon_classes']['jarcolumnrow_columnrow'] = 'jar-accordion-content-icon';

$GLOBALS['TCA']['tt_content']['types']['jarcolumnrow_accordion']['previewRenderer'] = \B13\Container\Backend\Preview\ContainerPreviewRenderer::class;

$contentTableColumns = [	
	'columnrow_accordion_items' => [
		'exclude' => false,
		'label' => 'LLL:EXT:jar_columnrow/Resources/Private/Language/locallang_be.xlf:items',
		'l10n_mode' => 'exclude',
		'config' => [
			'type' => 'inline',
			'foreign_table' => 'tx_jarcolumnrow_accordion_items',
			'foreign_field' => 'parent_accordion',
			'foreign_sortby' => 'sorting',
			'minitems' => '1',
			'maxitems' => 99,
			'appearance' => [				
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
	'columnrow_accordion_items',
	'jarcolumnrow_accordion',
	'after:header'
);


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

$GLOBALS['TCA']['tx_jarcolumnrow_accordion_items'] = [
	'ctrl' => [
		'title' => 'LLL:EXT:jar_columnrow/Resources/Private/Language/locallang_be.xlf:item',
		'label' => 'title',
		'tstamp' => 'tstamp',
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
		'iconfile' => 'EXT:jar_columnrow/Resources/Public/Icons/Accordion.svg',
		'typeicon_classes' => [
			'default' => 'jar-accordion-content-icon',
		],
		'hideTable' => true
	],
	'types' => [
		0 => [
			'showitem' => 'title',
		]
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
		'title' => [
			'exclude' => false,			
			'label' => 'LLL:EXT:jar_columnrow/Resources/Private/Language/locallang_be.xlf:title',
			'config' => [
				'type' => 'input',
				'size' => 30,
				'eval' => 'trim',
			],
		],
		'parent_accordion' => [
			'exclude' => false,
			'label' => 'parent_accordion',
			'config' => [
				'type' => 'passthrough',
			],
		],
	],
];