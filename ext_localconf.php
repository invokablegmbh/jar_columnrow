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
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/install']['update']['feditor_columnRowCtypeUpdateWizard'] = \Jar\Columnrow\Update\ColumnRowCtypeUpdateWizard::class;

// extend Container Classes

// saving the last loaded record for using it when the grid is fetched just via ctype
$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][\B13\Container\Domain\Factory\Database::class] = [
	'className' => \Jar\Columnrow\Xclasses\Factory\Database::class,
];

// adding the dynamic grid configuration for our columnrow container
$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][\B13\Container\Tca\Registry::class] = [
	'className' => \Jar\Columnrow\Xclasses\Tca\Registry::class,
];

// adding the uid to the ctype when fetching the grid
$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][\TYPO3\CMS\Backend\View\BackendLayout\Grid\GridColumnItem::class] = [
	'className' => \Jar\Columnrow\Xclasses\View\GridColumnItem::class,
];

// adding our columns to the localication summary
$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][\B13\Container\Service\RecordLocalizeSummaryModifier::class] = [
	'className' => \Jar\Columnrow\Xclasses\Service\RecordLocalizeSummaryModifier::class,
];

// adding field array hook to change the colpos of an translated element to the cooresponding column
 $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass']['tx_columnrow-pre-process-field-array'] = \Jar\Columnrow\Hooks\Datahandler\DatamapPreProcessFieldArrayHook::class;