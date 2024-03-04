<?php


declare(strict_types=1);

use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

defined('TYPO3') or die();

// Include page.tsconfig
$versionInformation = GeneralUtility::makeInstance(Typo3Version::class);
// Only include page.tsconfig if TYPO3 version is below 12 so that it is not imported twice.
if ($versionInformation->getMajorVersion() < 12) {
	ExtensionManagementUtility::addPageTSConfig(
		'@import "EXT:jar_columnrow/Configuration/page.tsconfig"'
	);
}

// add icon the classic way if needed 
if (version_compare(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::getExtensionVersion('core'), '7.0', '>=') && version_compare(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::getExtensionVersion('core'), '11.4', '<')) {
	$iconRegistry = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
		\TYPO3\CMS\Core\Imaging\IconRegistry::class
	);
	$iconRegistry->registerIcon(
		'jar-column-row-content-icon',
		\TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
		['source' => 'EXT:jar_columnrow/Resources/Public/Icons/ColumnRow.svg']
	);
}

// Update Wizards
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/install']['update']['columnrow_CtypeUpdateWizard'] = \Jar\Columnrow\Update\ColumnRowCtypeUpdateWizard::class;
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/install']['update']['columnrow_migrateFluxToContainer'] = \Jar\Columnrow\Update\MigrateFluxToContainer::class;

// extend Container Classes

// grid creation: saving the last loaded record for using it when the grid is fetched just via ctype
$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][\B13\Container\Domain\Factory\Database::class] = [
	'className' => \Jar\Columnrow\Xclasses\Factory\Database::class,
];

// grid creation: adding the dynamic grid configuration for our columnrow container
$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][\B13\Container\Tca\Registry::class] = [
	'className' => \Jar\Columnrow\Xclasses\Tca\Registry::class,
];

// translation: adding our columns to the localication summary
$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][\B13\Container\Service\RecordLocalizeSummaryModifier::class] = [
	'className' => \Jar\Columnrow\Xclasses\Service\RecordLocalizeSummaryModifier::class,
];

// translation: adding field array hook to change the colpos of an translated element to the cooresponding column
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processCmdmapClass']['tx_columnrow-pre-process-cmdmap'] = \Jar\Columnrow\Hooks\Datahandler\DatamapPreProcessFieldArrayHook::class;
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass']['tx_columnrow-pre-process-field-array'] = \Jar\Columnrow\Hooks\Datahandler\DatamapPreProcessFieldArrayHook::class;

// backend preview: add getContainer to grid column to access image and color informations in the preview
$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][\B13\Container\Backend\Grid\ContainerGridColumn::class] = [
	'className' => \Jar\Columnrow\Xclasses\Backend\ContainerGridColumn::class,
];

// repair wrong children colPos when copying columnrow elements
$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][\B13\Container\Hooks\Datahandler\CommandMapBeforeStartHook::class] = [
	'className' => \Jar\Columnrow\Xclasses\Hooks\Datahandler\CommandMapBeforeStartHook::class,
];
