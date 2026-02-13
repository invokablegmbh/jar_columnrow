<?php
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

defined('TYPO3') || die();

call_user_func(function () {
    ExtensionManagementUtility::registerPageTSConfigFile(
        'jar_columnrow',
        'Configuration/TsConfig/colorExample.tsconfig',
        'Example color configuration for column row'
    );
});
