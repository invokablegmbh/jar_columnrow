<?php

declare(strict_types=1);

namespace Jar\Columnrow\Utilities;

use Jar\Utilities\Utilities\BackendUtility;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;

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
        if (substr($cType, 0, strlen(self::$containerCTypePrefix)) === self::$containerCTypePrefix) {
            return true;
        }
        return false;
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
        return substr((string) $colPos, 0, strlen(self::$colPosPrefix)) === self::$colPosPrefix;
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
            'content_width' => 'content',
            'class' => '',
            'style' => '',
        ];

        if(
            !array_key_exists('select_background', $row) ||
            !array_key_exists('row_background', $row) ||
            !array_key_exists('row_user_background', $row) ||
            !array_key_exists('row_background_image', $row) ||
            !array_key_exists('content_width', $row) ||
            !array_key_exists('additional_row_class', $row)
        ) {
            return $result;
        }

        // content width
        $result['content_width'] = $row['content_width'];

        // Background Image Mode
        if($row['select_background'] == 2 && is_array($row['row_background_image']) && count($row['row_background_image'])) {
            $result['style'] .= 'background-image:url(' . $row['row_background_image'][0]['url'] . ');';
        }
        // Selected color mode - custom color
        else if ($row['select_background'] == 1 ) {
            
            // custom color
            if ($row['row_background'] === 'user') {
                $result['style'] .= 'background-color:' . $row['row_user_background'] . ';';
            }
            // predefined color
            // predefined class
            
            //$result['style'] .= 'background-image:url(' . $row['row_background_image'][0]['url'] . ');';
        }

        
        DebuggerUtility::var_dump($row);
        return $result;
    }
}
