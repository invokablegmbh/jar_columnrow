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

class RecordLocalizeSummaryModifier extends \B13\Container\Service\RecordLocalizeSummaryModifier
{

    private array $matchedColPos = [];

    /**
     * @param array $payload
     * @return array
     */
    #[\Override]
    public function rebuildPayload(array $payload): array
    {        
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

    // todo: make that prettier, ugly fix because this is now handled via 2 separate methods in "RecordSummaryForLocalization"

    #[\Override]
    public function filterRecords(array $recordsPerColPos): array
    {
        $filtered = parent::filterRecords($recordsPerColPos);

        $this->matchedColPos = [];
        foreach (array_keys($filtered) as $colPos) {
            if (ColumnRowUtility::isColumnRowColPos((int) $colPos)) {
                $this->matchedColPos[] = $colPos;
            }
        }        
        return $filtered;
    }

    #[\Override]
    public function rebuildColumns(array $columns): array
    {
        $columns = parent::rebuildColumns($columns);       
        if($this->matchedColPos !== []) {
            foreach ($this->matchedColPos as $colPos) {
                $columns['columns'][$colPos] = 'Column (' . $colPos . ')';
                $columns['columnList'][] = $colPos;
            }           
        }
        return $columns;
    }
}
