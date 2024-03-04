<?php

declare(strict_types=1);
namespace Jar\Columnrow\Hooks\Datahandler;

/*
 * This file is part of TYPO3 CMS-based extension "jar_columnrow" by invokable.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

use B13\Container\Domain\Factory\ContainerFactory;
use B13\Container\Domain\Service\ContainerService;
use B13\Container\Domain\Factory\Exception;
use B13\Container\Hooks\Datahandler\Database;
use B13\Container\Tca\Registry;
use Jar\Columnrow\Hooks\Datahandler\Database as ColumnDatabase;
use Jar\Columnrow\Utilities\ColumnRowUtility;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;

class DatamapPreProcessFieldArrayHook
{
    public function __construct(
        private readonly ContainerFactory $containerFactory,
        private readonly Database $database,
        private readonly ColumnDatabase $columnDatabase,
        private readonly Registry $tcaRegistry,
        private readonly ContainerService $containerService
    ) {
    }

    protected function copyToLanguageElementInContainer(array $incomingFieldArray): array
    { 
        if (!isset($incomingFieldArray['tx_container_parent']) || (int)$incomingFieldArray['tx_container_parent'] === 0) {
            return $incomingFieldArray;
        }
        if (!isset($incomingFieldArray['l10n_source']) || (int)$incomingFieldArray['l10n_source'] === 0) {
            return $incomingFieldArray;
        }
        if (!isset($incomingFieldArray['l18n_parent']) || (int)$incomingFieldArray['l18n_parent'] > 0) {
            return $incomingFieldArray;
        }
        if (!isset($incomingFieldArray['sys_language_uid']) || (int)$incomingFieldArray['sys_language_uid'] === 0) {
            return $incomingFieldArray;
        }        
        $translatedContainerRecord = $this->database->fetchOneRecord((int)$incomingFieldArray['tx_container_parent']);  
       
        if ($translatedContainerRecord === null) {
            return $incomingFieldArray;
        }
        try {            
            $container = $this->containerFactory->buildContainer((int)$translatedContainerRecord['uid']);
            if (!$container->isConnectedMode()) {                
                $sourceColumnUid = ColumnRowUtility::encodeColPos((int)$incomingFieldArray['colPos']);
                if($sourceColumnUid !== (int)$incomingFieldArray['colPos']) {
                    $translatedTargetColumn = $this->columnDatabase->fetchOneTranslatedRecordByl10nSource($sourceColumnUid, $incomingFieldArray['sys_language_uid']);
                    if(!empty($translatedTargetColumn)) {
                        $incomingFieldArray['colPos'] = ColumnRowUtility::decodeColPos($translatedTargetColumn, $translatedContainerRecord);
                    }                    
                }
            }
        } catch (Exception $e) {
            // not a container
        }
        return $incomingFieldArray;
    }

    public function processDatamap_preProcessFieldArray(array &$incomingFieldArray, string $table, $id, DataHandler $dataHandler): void
    {
        $this->validateColumns($incomingFieldArray, $table, $id, $dataHandler); 

        if ($table !== 'tt_content') {
            return;
        }
        if (MathUtility::canBeInterpretedAsInteger($id)) {
            return;
        }
        if (!isset($incomingFieldArray['pid']) || (int)$incomingFieldArray['pid'] >= 0) {
            return;
        }      
        $incomingFieldArray = $this->copyToLanguageElementInContainer($incomingFieldArray);
    }


    protected function validateColumns(array &$incomingFieldArray, string $table, $id, DataHandler $dataHandler): void{
        $dataMap = $dataHandler->datamap;

        if ($table === 'tx_jarcolumnrow_columns') {
            // look for the parent column row (first in dataset, then in database)              
            $columnRow = false;
            if(isset($dataMap['tt_content']) && is_array($dataMap['tt_content'])) {
                $matchingColumnRows = array_filter($dataMap['tt_content'], function($element) use ($id) {
                    if(isset($element['columnrow_columns']) && !empty($element['columnrow_columns'])) {
                        $columndUids = GeneralUtility::trimExplode(',', $element['columnrow_columns']);
                        return in_array($id, $columndUids);
                    }
                    return false;
                });
                $columnRow = reset($matchingColumnRows);
            }
            if(!$columnRow) {
                $column = $this->columnDatabase->fetchOneRecord((int)$id);
                if ($column) {
                    $columnRow = $this->database->fetchOneRecord((int)$column['parent_column_row']);
                }
            }
            
            if($columnRow) {                   
                // under some circumstances the sys_language_uid is set to 0 by free translated elements (when their siblings are previous created via the translation wizard)
                // we have to set them to the right language
                if(
                    isset($columnRow['sys_language_uid']) &&
                    $columnRow['sys_language_uid'] > 0 && 
                    !ColumnRowUtility::rowIsTranslatedInConnectionMode($columnRow)
                ) {
                    $incomingFieldArray['sys_language_uid'] = $columnRow['sys_language_uid'];
                }
            } 
        }       
    }

    public function processDatamap_afterAllOperations(DataHandler &$dataHandler)
    {
        $dataMap = $dataHandler->datamap;

        // when editing a element in default language, translated connected elements are created with sorting as 'l10n_source' and 'l10n_parent'
        // you can solve that by re-saving the element in default language, but it is very anoying
        // so we have to repair them afterwards
        if(isset($dataMap['tx_jarcolumnrow_columns'])) {
            $newTranslatedColumns = array_filter($dataMap['tx_jarcolumnrow_columns'], function ($element) {               
                return
                    isset($element['l10n_parent']) &&
                    strpos((string) $element['l10n_parent'], 'NEW') === 0 && 
                    isset($element['sys_language_uid']) &&
                    $element['sys_language_uid'] > 0;
            });

            foreach($newTranslatedColumns as $key => $newTranslatedColumn) {                
                if(
                    !array_key_exists($key, $dataHandler->substNEWwithIDs) ||
                    !array_key_exists($newTranslatedColumn['l10n_parent'], $dataHandler->substNEWwithIDs)
                ) {
                    continue;
                }
                $insertedUid = $dataHandler->substNEWwithIDs[$key];
                $realParentUid = $dataHandler->substNEWwithIDs[$newTranslatedColumn['l10n_parent']];

                // check if the problem exists (sorting == l10n_parent == l10n_source) and set the right l10n_parent and l10n_source
                $row = $this->columnDatabase->fetchOneRecord($insertedUid);
                if($row && $row['l10n_parent'] === $row['sorting'] && $row['l10n_source'] === $row['sorting']) {
                    $this->columnDatabase->updateRecord($insertedUid, [
                        'l10n_parent' => $realParentUid,
                        'l10n_source' => $realParentUid
                    ]);
                }
            }
        }  
    }
}
