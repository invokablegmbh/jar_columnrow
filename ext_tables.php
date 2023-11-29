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
    Backend Darstellung schön machen
    Son Quatsch mal stylen
    
element.style {
    background: url(/fileadmin/user_upload/giphy__1_.gif);
    width: 100%;
    display: block;
    top: 60px;
    position: absolute;
    background-size: cover;
    opacity: 0.25;
    bottom: 29px;
}
    Kapseln der Daten mit Ausgabe im FE im Hinterkopf behalten


    Color Pflege im TSConfig (2x direkt, allgemein über JAR) / TCA 
*/