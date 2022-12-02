<?php

	call_user_func(function () {
	// Flux - Engine init
	\FluidTYPO3\Flux\Core::registerProviderExtensionKey('JarColumnrow', 'Content');

	$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/install']['update']['feditor_columnRowCtypeUpdateWizard'] = \Jar\Columnrow\Update\ColumnRowCtypeUpdateWizard::class;

	// add content the classic way
	if (version_compare(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::getExtensionVersion('core'), 7.0, '>=') && version_compare(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::getExtensionVersion('core'), 11.4, '<')) {
		$iconRegistry = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
			\TYPO3\CMS\Core\Imaging\IconRegistry::class
		);
		$iconRegistry->registerIcon(
			'jar-column-row-content-icon',
			\TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
			['source' => 'EXT:jar_columnrow/Resources/Public/Icons/ColumnRow.svg']
		);
	}
});