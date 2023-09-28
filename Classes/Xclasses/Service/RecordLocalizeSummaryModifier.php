<?php

declare(strict_types=1);

namespace Jar\Columnrow\Xclasses\Service;

/*
 * This file is part of TYPO3 CMS-based extension "jar_columnrow" by invokable.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */


use Jar\Columnrow\Utilities\ColumnRowUtility;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;

class RecordLocalizeSummaryModifier extends \B13\Container\Service\RecordLocalizeSummaryModifier
{

    /**
     * @param array $payload
     * @return array
     */
    public function rebuildPayload(array $payload): array
    {
        $colPosPrefix = ColumnRowUtility::$colPosPrefix;
        $payload = parent::rebuildPayload($payload);

        // check for content elements with our colPos prefix and add them to the column list
        if(isset($payload['records'])) {            
            foreach (array_keys($payload['records']) as $colPos) {
                if(ColumnRowUtility::isColumnRowColPos((int) $colPos)) {
                    $payload['columns']['columns'][$colPos] = 'Column (' . $colPos . ')';
                    $payload['columns']['columnList'][] = $colPos;
                }
            }
        }
        return $payload;
    }
}
