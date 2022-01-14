<?php

	call_user_func(function () {
	// Flux - Engine init
	\FluidTYPO3\Flux\Core::registerProviderExtensionKey('JarColumnrow', 'Content');

	$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/install']['update']['feditor_columnRowCtypeUpdateWizard'] = \Jar\Columnrow\Update\ColumnRowCtypeUpdateWizard::class;
});