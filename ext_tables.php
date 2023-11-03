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
    GateService->getReflectedRow Zeile 127, keine Labels ladbar da diese über itemProcFuncs geladen werden
    Variante a) TCA Utility erweitern und die Labels laden
    Variante b) Labels berechnern lassen und undefinierte custom Felder entsprechend benennen
    Variante c) Ich schreibe einfach gar keine Labels mehr ins Backend (gemacht - ist doof, verliert man schnell den Überblick)
*/