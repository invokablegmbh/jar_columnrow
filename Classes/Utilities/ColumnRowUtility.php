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
    protected static string $containerCTypePrefix = 'jarcolumnrow_';

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
     * @param array $columnRow 
     * @param array $parentRow 
     * @return int 
     */
    public static function calculateColPos(array $columnRow, array $parentRow = null): int
    {
        return (int) ('7731' . $columnRow['uid']);
    }

    
}
