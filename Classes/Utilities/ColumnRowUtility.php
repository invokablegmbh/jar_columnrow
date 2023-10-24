<?php

declare(strict_types=1);

namespace Jar\Columnrow\Utilities;

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


    
}
