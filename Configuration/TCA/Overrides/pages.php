<?php
defined('TYPO3_MODE') || die();

call_user_func(function () {
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::registerPageTSConfigFile(
        'jar_columnrow',
        'Configuration/TsConfig/colorExample.tsconfig',
        'Example color configuration for column row'
    );
});
