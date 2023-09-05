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