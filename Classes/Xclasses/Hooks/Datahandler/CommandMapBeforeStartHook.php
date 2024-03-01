<?php

declare(strict_types=1);

namespace Jar\Columnrow\Xclasses\Hooks\Datahandler;

use Jar\Columnrow\Hooks\Datahandler\Database as ColumnDatabase;
use Jar\Columnrow\Services\GateService;
use Jar\Columnrow\Utilities\ColumnRowUtility;
use Jar\Utilities\Utilities\IteratorUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;

/*
 * This file is part of TYPO3 CMS-based extension "jar_columnrow" by invokable.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */


class CommandMapBeforeStartHook extends \B13\Container\Hooks\Datahandler\CommandMapBeforeStartHook
{
    protected function dataFromContainerIdColPos(array $data): array
    {
        if(
            isset($data['tx_container_parent']) &&
            $data['tx_container_parent'] > 0 &&
            isset($data['colPos']) &&
            $data['colPos'] > 0
        ) {
            $newParentColPos = $this->getColposFromColumnRowContainer((int) $data['tx_container_parent']);            
            // check if colPos is still based on old copied container
            if(!in_array($data['colPos'], $newParentColPos)) {
                // find old parent, based on old colPos
                $oldParentColumnUid = ColumnRowUtility::encodeColPos($data['colPos']);                
                $oldParentColumn = GeneralUtility::makeInstance(ColumnDatabase::class)->fetchOneRecord($oldParentColumnUid);
                if(isset($oldParentColumn['parent_column_row'])) {                    
                    $oldParentColPos = $this->getColposFromColumnRowContainer((int) $oldParentColumn['parent_column_row']);
                    // get index of old colPos in old parent and take new colPos from new parent
                    $oldColPosIndex = array_search($data['colPos'], $oldParentColPos);
                    $data['colPos'] = $newParentColPos[$oldColPosIndex];    
                }
            }            
        }        
        return parent::dataFromContainerIdColPos($data);
    }

    protected function getColposFromColumnRowContainer(int $uid): array
    {
        $result = [];
        $row = $this->database->fetchOneRecord($uid);
        if (isset($row['CType']) && ColumnRowUtility::isOurContainerCType($row['CType'])) {
            $gateService = GeneralUtility::makeInstance(GateService::class);
            $reflectedRow = $gateService->getReflectedRow($row);           
            foreach($reflectedRow['columns'] as $column) {                
                $result[] = ColumnRowUtility::decodeColPos($column);
            }
        }         
        return $result;
    }
}
