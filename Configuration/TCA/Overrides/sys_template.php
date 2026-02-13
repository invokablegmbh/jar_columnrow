<?php
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

defined('TYPO3') || die();

call_user_func(function () {
    ExtensionManagementUtility::addStaticFile('jar_columnrow', 'Configuration/TypoScript', 'Jar Column Row');
});
