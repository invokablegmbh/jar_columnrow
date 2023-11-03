<?php

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

$GLOBALS['TBE_STYLES']['skins']['jar_columnrow'] = [
    'name' => 'jar_columnrow',
    'stylesheetDirectories' => [
        'css' => 'EXT:jar_columnrow/Resources/Public/Css/Backend/'
    ]
];

ExtensionManagementUtility::allowTableOnStandardPages('tx_jarcolumnrow_columns');

/*

*/