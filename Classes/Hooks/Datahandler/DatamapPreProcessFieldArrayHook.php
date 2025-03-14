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
use Doctrine\DBAL\Schema\Column;
use Jar\Columnrow\Hooks\Datahandler\ColumnDatabase as ColumnDatabase;
use Jar\Columnrow\Utilities\ColumnRowUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;

class DatamapPreProcessFieldArrayHook
{
    public function __construct(
        private readonly ContainerFactory $containerFactory,
        private readonly Database $database,
        private readonly ColumnDatabase $columnDatabase,
        private readonly ContentDatabase $contentDatabase,
        private readonly Registry $tcaRegistry,
        private readonly ContainerService $containerService,
        private readonly ConnectionPool $connectionPool,
    ) {
    }

    public function processDatamap_afterAllOperations(DataHandler &$dataHandler)
    {
        $this->fixColPosAfterCopyPage($dataHandler);
        $this->fixLanguageFieldsAfterTranslation($dataHandler);
        $this->fixMissingContainerParent($dataHandler);
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

    // when drag and drop elements between or in columns, container removes tx_container_parent
    protected function fixMissingContainerParent(DataHandler &$dataHandler) {
        $dataMap = $dataHandler->datamap;
        if (isset($dataMap['tt_content'])) {
            foreach ($dataMap['tt_content'] as $uid => $record) {
                if (
                    strpos((string) $uid, 'NEW') === 0 ||
                    !isset($record['colPos']) ||
                    !isset($record['tx_container_parent']) ||
                    $record['tx_container_parent'] != 0 ||
                    strpos((string) $record['colPos'], ColumnRowUtility::$colPosPrefix) !== 0
                ) {
                    continue;
                }

                $parentColumnUid = ColumnRowUtility::encodeColPos((int) $record['colPos']);
                $parentColumnRow = $this->columnDatabase->fetchOneRecord($parentColumnUid);

                if(!$parentColumnRow || !isset($parentColumnRow['parent_column_row'])) {
                    continue;
                }

                $containerParentUid = $parentColumnRow['parent_column_row'];

                $this->connectionPool->getConnectionForTable('tt_content')
                    ->update(
                        'tt_content',
                        ['tx_container_parent' => $containerParentUid],
                        ['uid' => $uid],
                    );
            }
        }
    }

    // when copying a page, the colPos of the container elements are not correct
    protected function fixColPosAfterCopyPage(DataHandler &$dataHandler)
    {
        $dataMap = $dataHandler->datamap;
        if (isset($dataMap['tt_content'])) {
            foreach ($dataMap['tt_content'] as $newUid => $record) {
                if(
                    !isset($record['CType']) ||
                    strpos((string) $newUid, 'NEW') !== 0 ||
                    !array_key_exists($newUid, $dataHandler->substNEWwithIDs)
                ) {
                    continue;
                }

                $insertedUid = $dataHandler->substNEWwithIDs[$newUid];

                // when inserting the container, fix all previous inserted elements
                if (
                    ColumnRowUtility::isOurContainerCType($record['CType']) &&
                    array_key_exists('t3_origuid', $record) &&
                    array_key_exists('pid', $record) &&
                    array_key_exists('l18n_parent', $record) &&
                    array_key_exists('sys_language_uid', $record)
                ) {
                    $sourceUid = $record['t3_origuid'];
                    $mappingLanguage = $record['sys_language_uid'];

                    // if the container is translated, we have to use the record uid in the default language
                    if (ColumnRowUtility::rowIsTranslatedInConnectionMode($record)) {                        
                        $recordDefaultLanguage = $this->database->fetchOneRecord($record['t3_origuid']);
                        if(isset($recordDefaultLanguage['l10n_source'])) {
                            $sourceUid = $recordDefaultLanguage['l10n_source'];
                            $insertedUid = $record['l18n_parent'];
                            $mappingLanguage = 0;
                        }
                    }

                    $childContentElements = $this->database->fetchRecordsByParentAndLanguage($sourceUid, $record['sys_language_uid']);

                    // just use elements from the same page
                    $childContentElements = array_filter($childContentElements, function ($element) use ($record) {
                        return $element['pid'] === $record['pid'];
                    });

                    if(!empty($childContentElements)) {
                        
                        $colPosMap = $this->createColPosRemappingBasedOnOrder($sourceUid, $insertedUid, $mappingLanguage);

                        // update the colPos of the child elements
                        foreach ($childContentElements as $childContentElement) {
                            if (isset($childContentElement['colPos']) && isset($colPosMap[$childContentElement['colPos']])) {

                                $this->connectionPool->getConnectionForTable('tt_content')
                                ->update(
                                    'tt_content',
                                    ['colPos' => $colPosMap[$childContentElement['colPos']]],
                                    ['uid' => $childContentElement['uid']], 
                                );
                            }
                        }
                    }
                }



                
                // correct colpos of each container child element, when columnrow is allready inserted                    

                if (
                    isset($record['tx_container_parent']) &&
                    !empty($record['tx_container_parent']) &&
                    isset($record['colPos']) &&
                    isset($record['pid']) &&
                    !empty($record['colPos'])
                ) { 
                    $originalColumnUid = ColumnRowUtility::encodeColPos((int) $record['colPos']);
                    $originalColumn = $this->columnDatabase->fetchOneRecord($originalColumnUid);

                    if(
                        $originalColumn &&
                        isset($originalColumn['parent_column_row']) &&
                        !empty($originalColumn['parent_column_row']) &&
                        isset($originalColumn['sorting'])
                    ) { 
                        $originalColumnRow = $this->database->fetchOneRecord($originalColumn['parent_column_row']);

                        if(
                            $originalColumnRow &&
                            isset($originalColumnRow['CType']) &&
                            ColumnRowUtility::isOurContainerCType($originalColumnRow['CType'])
                        ) { 
                            $newColumnRow = $this->contentDatabase->fetchOneRecordByOrigUidAndPid((int) $originalColumnRow['uid'], (int) $record['pid']);
                            
                            if($newColumnRow && isset($newColumnRow['uid'])) {

                                $colPosMap = $this->createColPosRemappingBasedOnOrder($originalColumnRow['uid'], $newColumnRow['uid'], $originalColumnRow['sys_language_uid']);

                                if(
                                    !empty($colPosMap) &&
                                    isset($colPosMap[$record['colPos']])
                                ) {
                                    $this->connectionPool->getConnectionForTable('tt_content')
                                        ->update(
                                            'tt_content',
                                            ['colPos' => $colPosMap[$record['colPos']]],
                                            ['uid' => $insertedUid],
                                        );
                                }
                            }
                        }
                    }
                }
            }
        }
    }



    /**
     * Helper Method for create a mapping colPos values in a columnrow (f.e when copying a columnrow)
     * 
     * @param int $sourceUid 
     * @param int $targetUid 
     * @param int $sys_language_uid 
     * @return array
     */
    protected function createColPosRemappingBasedOnOrder(int $sourceUid, int $targetUid, int $sys_language_uid): array
    {
        $colPosMap = [];
        
        $originalColumns = $this->columnDatabase->fetchRecordsByParentAndLanguage($sourceUid, $sys_language_uid);

        if (empty($originalColumns)) {
            return $colPosMap;
        }

        $columnMap = [];
        foreach ($originalColumns as $column) {
            $columnMap[$column['sorting']] = [
                'old_uid' => $column['uid']
            ];
        }

        $newColumns = $this->columnDatabase->fetchRecordsByParentAndLanguage($targetUid, $sys_language_uid);

        if (empty($newColumns)) {
            return $colPosMap;
        }

        foreach ($newColumns as $newColumn) {
            if (isset($columnMap[$newColumn['sorting']])) {
                $columnMap[$newColumn['sorting']]['new_uid'] = $newColumn['uid'];
            }
        }

        
        foreach ($columnMap as $mapElement) {
            if (isset($mapElement['new_uid']) && isset($mapElement['old_uid'])) {
                $colPosMap[ColumnRowUtility::decodeColPos(['uid' => $mapElement['old_uid']])] = ColumnRowUtility::decodeColPos(['uid' => $mapElement['new_uid']]);
            }
        }

        return $colPosMap;
    }
    
    
    // when editing a element in default language, translated connected elements are created with sorting as 'l10n_source' and 'l10n_parent'
    // you can solve that by re-saving the element in default language, but it is very anoying
    // so we have to repair them afterwards
    protected function fixLanguageFieldsAfterTranslation(DataHandler &$dataHandler) {
        
        
        $dataMap = $dataHandler->datamap;

        if (isset($dataMap['tx_jarcolumnrow_columns'])) {
            $newTranslatedColumns = array_filter($dataMap['tx_jarcolumnrow_columns'], function ($element) {
                return
                    isset($element['l10n_parent']) &&
                    strpos((string) $element['l10n_parent'], 'NEW') === 0 &&
                    isset($element['sys_language_uid']) &&
                    $element['sys_language_uid'] > 0;
            });

            foreach ($newTranslatedColumns as $key => $newTranslatedColumn) {
                if (
                    !array_key_exists($key, $dataHandler->substNEWwithIDs) ||
                    !array_key_exists($newTranslatedColumn['l10n_parent'], $dataHandler->substNEWwithIDs)
                ) {
                    continue;
                }
                $insertedUid = $dataHandler->substNEWwithIDs[$key];
                $realParentUid = $dataHandler->substNEWwithIDs[$newTranslatedColumn['l10n_parent']];

                // check if the problem exists (sorting == l10n_parent == l10n_source) and set the right l10n_parent and l10n_source
                $row = $this->columnDatabase->fetchOneRecord($insertedUid);
                if ($row && $row['l10n_parent'] === $row['sorting'] && $row['l10n_source'] === $row['sorting']) {
                    $this->columnDatabase->updateRecord($insertedUid, [
                        'l10n_parent' => $realParentUid,
                        'l10n_source' => $realParentUid
                    ]);
                }
            }
        }  
    }
}
