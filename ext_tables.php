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
    Doku:    
        Color Pflege im TSConfig (2x direkt, allgemein über JAR) / TCA 
        Hinzufügen von neuen Farben
        Hinzugügen von neuen Feldern (Reflection jarcolumnrow)
        Migration der Farben von V1 zu V2 (ggf. automatisch?)
        Wie hinterlege ich meine eigenen Preview Farben?
*/