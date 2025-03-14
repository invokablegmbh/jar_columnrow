<?php

use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

$versionInformation = GeneralUtility::makeInstance(Typo3Version::class);
// Only include page.tsconfig if TYPO3 version is below 12 so that it is not imported twice.
if ($versionInformation->getMajorVersion() < 12) {
    $GLOBALS['TBE_STYLES']['skins']['jar_columnrow'] = [
        'name' => 'jar_columnrow',
        'stylesheetDirectories' => [
            'css' => 'EXT:jar_columnrow/Resources/Public/Css/Backend/'
        ]
    ];

    ExtensionManagementUtility::allowTableOnStandardPages('tx_jarcolumnrow_columns');
} else {
    $GLOBALS['TYPO3_CONF_VARS']['BE']['stylesheets']['jar_columnrow'] = 'EXT:jar_columnrow/Resources/Public/Css/Backend12/style.css';
}






/*
    TYPO3 12
    - Akkordeon Migration
    Doku:    
        Color Pflege im TSConfig (2x direkt, allgemein über JAR) / TCA 
        Hinzufügen von neuen Farben
        Hinzugügen von neuen Feldern (Reflection jarcolumnrow)
        Migration der Farben von V1 zu V2 (ggf. automatisch?)
        Wie hinterlege ich meine eigenen Preview Farben?
*/
