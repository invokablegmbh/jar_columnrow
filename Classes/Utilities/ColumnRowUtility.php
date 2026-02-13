<?php

declare(strict_types=1);

namespace Jar\Columnrow\Utilities;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use Jar\Utilities\Utilities\BackendUtility;
use Jar\Utilities\Utilities\TcaUtility;

/*
 * This file is part of TYPO3 CMS-based extension "jar_columnrow" by invokable.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */



class ColumnRowUtility 
{
    public static string $containerCTypePrefix = 'jarcolumnrow_';
    public static string $colPosPrefix = '7731';

    /**
     * @param string $cType 
     * @return bool 
     */
    public static function isOurContainerCType(string $cType): bool
    {
        // check the beginning of ctype for our container prefix
        return str_starts_with($cType, self::$containerCTypePrefix);
    }

    /**     
     * @param array $column 
     * @param array $parentRow 
     * @return int 
     */
    public static function decodeColPos(array $column, array $parentRow = null): int
    {
        return (int) (self::$colPosPrefix . $column['uid']);
    }

 
    /**
     * @param int $colPos 
     * @return int 
     */
    public static function encodeColPos(int $colPos): int
    {
        if(!self::isColumnRowColPos($colPos)) {
            return $colPos;
        }
        return (int) substr((string) $colPos, strlen(self::$colPosPrefix));
    }

    /**
     * @param int $colPos 
     * @return bool 
     */
    public static function isColumnRowColPos(int $colPos): bool        
    {
        return str_starts_with((string) $colPos, self::$colPosPrefix);
    }

    /**     
     * @return int 
     */
    public static function getGridBase(): int
    {
        $pageTs = BackendUtility::getCurrentPageTS();

        if(array_key_exists('jar_columnrow', $pageTs) && array_key_exists('gridBase', $pageTs['jar_columnrow'])) {
            return (int) $pageTs['jar_columnrow']['gridBase'];
        }

        return 12;
    }
    
    /**
     * Returns frontend Classes, colors and background images based on backend configuration
     * @param array $row 
     * @return array 
     */
    public static function getFrontendAttributesByPopulatedRow(array $row): array {
        $result = [
            'content_width' => 'container',
            'class' => '',
            'style' => '',
        ];

        if(!isset($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['jar_columnrow']['getFrontendAttributesByPopulatedRow']) && (!array_key_exists('select_background', $row) || !array_key_exists('row_background', $row) || !array_key_exists('row_user_background', $row) || !array_key_exists('row_background_image', $row) || !array_key_exists('content_width', $row) || !array_key_exists('additional_row_class', $row))) {
            return $result;
        }

        

        // content width
        if(!empty($row['content_width'])) {
            $result['content_width'] = $row['content_width'];
        }


        // Background Image Mode        
        if (isset($row['select_background']) && $row['select_background'] == 2 && is_array($row['row_background_image']) && count($row['row_background_image'])) {
            $result['style'] .= 'background-image:url(' . $row['row_background_image'][0]['url'] . ');';
        } elseif (isset($row['select_background']) && $row['select_background'] == 1) {
            // custom color
            if ($row['row_background'] === 'user' && !empty($row['row_user_background'])) {
                $result['style'] .= 'background-color:' . $row['row_user_background'] . ';';
            } elseif (str_starts_with((string) $row['row_background'], '.')) {
                // if $row['row_background'] starts with a '.' it is a class otherwise a colorcode
                $result['class'] .= ' ' . substr(implode(' ', explode('.', (string) $row['row_background'])), 1);
            } else {
                $result['style'] .= 'background-color:' . $row['row_background'] . ';';
            }
        }        

        // Additional Row Class
        if (!empty($row['additional_row_class'])) {
            $result['class'] .= ' ' . implode(' ', explode('.', (string) $row['additional_row_class']));
        }

        // add hook to change or add attributes
        $hookResult = [];
        if (isset($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['jar_columnrow']['getFrontendAttributesByPopulatedRow'])) {
            foreach ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['jar_columnrow']['getFrontendAttributesByPopulatedRow'] as $className) {                
                $hookResult[] = GeneralUtility::makeInstance($className)
                    ->getFrontendAttributesByPopulatedRow($row, $result);
            }
        }
        if (is_array($hookResult) && count($hookResult)) {
            foreach ($hookResult as $hookRow) {
                if (is_array($hookRow)) {
                    $result = array_merge($result, $hookRow);
                }
            }
        }
        
        return $result;
    }

    /**
     * @param array $row 
     * @return bool 
     */
    public static function rowIsTranslatedInConnectionMode(array $row): bool
    {
        return $row['sys_language_uid'] > 0 && $row['l18n_parent'] > 0;
    }

    /**
     * Set individual fields per language, useful for f.e. title fields in connected mode
     * @param array $reflectedDefaultColumnRow 
     * @param array $reflectedTranslatedColumnRow 
     * @return array 
     */
    public static function addIndividualFieldsPerLanguage(array $reflectedDefaultColumnRow, array $reflectedTranslatedColumnRow): array
    {
        $individualFieldsPerLanguage = TcaUtility::getTca()['tx_jarcolumnrow_columns']['ctrl']['columnRowSettings']['individualFieldsPerLanguage'] ?? [];
       
        if(count($individualFieldsPerLanguage) && is_array($reflectedDefaultColumnRow['columns']) && is_array($reflectedTranslatedColumnRow['columns'])) {            
            foreach($reflectedDefaultColumnRow['columns'] as $key => $column) {
                foreach ($individualFieldsPerLanguage as $fieldname) {
                    if(isset($column[$fieldname]) && isset($reflectedTranslatedColumnRow['columns'][$key][$fieldname])) {
                        $reflectedDefaultColumnRow['columns'][$key][$fieldname] = $reflectedTranslatedColumnRow['columns'][$key][$fieldname];
                    }
                }
            }
        }
        
        return $reflectedDefaultColumnRow;
    }
}
