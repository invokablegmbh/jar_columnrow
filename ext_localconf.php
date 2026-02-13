<?php


declare(strict_types=1);

use TYPO3\CMS\Core\Imaging\IconRegistry;
use TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider;
use Jar\Columnrow\Update\ColumnRowCtypeUpdateWizard;
use Jar\Columnrow\Update\MigrateFluxToContainer;
use B13\Container\Domain\Factory\Database;
use B13\Container\Tca\Registry;
use B13\Container\Service\RecordLocalizeSummaryModifier;
use Jar\Columnrow\Hooks\Datahandler\DatamapPreProcessFieldArrayHook;
use B13\Container\Backend\Grid\ContainerGridColumn;
use B13\Container\Hooks\Datahandler\CommandMapBeforeStartHook;
use B13\Container\Domain\Service\ContainerService;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

defined('TYPO3') || die();

// Include page.tsconfig
$versionInformation = GeneralUtility::makeInstance(Typo3Version::class);
// Only include page.tsconfig if TYPO3 version is below 12 so that it is not imported twice.
if ($versionInformation->getMajorVersion() < 12) {
}

// add icon the classic way if needed 
if (version_compare(ExtensionManagementUtility::getExtensionVersion('core'), '7.0', '>=') && version_compare(ExtensionManagementUtility::getExtensionVersion('core'), '11.4', '<')) {
	$iconRegistry = GeneralUtility::makeInstance(
		IconRegistry::class
	);
	$iconRegistry->registerIcon(
		'jar-column-row-content-icon',
		SvgIconProvider::class,
		['source' => 'EXT:jar_columnrow/Resources/Public/Icons/ColumnRow.svg']
	);
	$iconRegistry->registerIcon(
		'jar-accordion-content-icon',
		SvgIconProvider::class,
		['source' => 'EXT:jar_columnrow/Resources/Public/Icons/Accordion.svg']
	);
}

// Update Wizards
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/install']['update']['columnrow_CtypeUpdateWizard'] = ColumnRowCtypeUpdateWizard::class;
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/install']['update']['columnrow_migrateFluxToContainer'] = MigrateFluxToContainer::class;

// extend Container Classes

// grid creation: saving the last loaded record for using it when the grid is fetched just via ctype
$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][Database::class] = [
	'className' => \Jar\Columnrow\Xclasses\Factory\Database::class,
];

// grid creation: adding the dynamic grid configuration for our columnrow container
$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][Registry::class] = [
	'className' => \Jar\Columnrow\Xclasses\Tca\Registry::class,
];

// translation: adding our columns to the localication summary
$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][RecordLocalizeSummaryModifier::class] = [
	'className' => \Jar\Columnrow\Xclasses\Service\RecordLocalizeSummaryModifier::class,
];

// translation: adding field array hook to change the colpos of an translated element to the cooresponding column
// validate column row values in certain translation cases
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processCmdmapClass']['tx_columnrow-pre-process-cmdmap'] = DatamapPreProcessFieldArrayHook::class;
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass']['tx_columnrow-pre-process-field-array'] = DatamapPreProcessFieldArrayHook::class;

// backend preview: add getContainer to grid column to access image and color informations in the preview
$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][ContainerGridColumn::class] = [
	'className' => \Jar\Columnrow\Xclasses\Backend\ContainerGridColumn::class,
];

// repair wrong children colPos when copying columnrow elements
$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][CommandMapBeforeStartHook::class] = [
	'className' => \Jar\Columnrow\Xclasses\Hooks\Datahandler\CommandMapBeforeStartHook::class,
];

// disable default containter paste after behavior for columnrow elements, the default behavior suits better in our case
$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][ContainerService::class] = [
	'className' => \Jar\Columnrow\Xclasses\Service\ContainerService::class,
];
