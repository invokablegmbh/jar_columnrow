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
use Jar\Columnrow\Hooks\Datahandler\ColumnDatabase as ColumnDatabase;
use Jar\Columnrow\Utilities\ColumnRowUtility;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
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
        private readonly ContainerService $containerService,
        private readonly ConnectionPool $connectionPool,
    ) {
    }

    public function processDatamap_afterAllOperations(DataHandler &$dataHandler): void
    {
        $this->fixColPosAfterCopyPage($dataHandler);
        $this->fixLanguageFieldsAfterTranslation($dataHandler);
        $this->fixMissingContainerParent($dataHandler);
    }

    public function processCmdmap_afterFinish(DataHandler $dataHandler): void
    {
        $this->fixColPosAfterCopyPageCmdmap($dataHandler);
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
                    if($translatedTargetColumn !== null && $translatedTargetColumn !== []) {
                        $incomingFieldArray['colPos'] = ColumnRowUtility::decodeColPos($translatedTargetColumn, $translatedContainerRecord);
                    }                    
                }
            }
        } catch (Exception) {
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
            
            // under some circumstances the sys_language_uid is set to 0 by free translated elements (when their siblings are previous created via the translation wizard)
            // we have to set them to the right language
            if($columnRow && (isset($columnRow['sys_language_uid']) && $columnRow['sys_language_uid'] > 0 && !ColumnRowUtility::rowIsTranslatedInConnectionMode($columnRow))) {                   
                $incomingFieldArray['sys_language_uid'] = $columnRow['sys_language_uid'];
            } 
        }       
    }

    // when drag and drop elements between or in columns, container removes tx_container_parent
    protected function fixMissingContainerParent(DataHandler &$dataHandler) {
        $dataMap = $dataHandler->datamap;
        if (isset($dataMap['tt_content'])) {
            foreach ($dataMap['tt_content'] as $uid => $record) {
                if (
                    str_starts_with((string) $uid, 'NEW') ||
                    !isset($record['colPos']) ||
                    !isset($record['tx_container_parent']) ||
                    $record['tx_container_parent'] != 0 ||
                    !str_starts_with((string) $record['colPos'], ColumnRowUtility::$colPosPrefix)
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
                    !str_starts_with((string) $newUid, 'NEW') ||
                    !array_key_exists($newUid, $dataHandler->substNEWwithIDs)
                ) {
                    continue;
                }

                $insertedUid = (int)$dataHandler->substNEWwithIDs[$newUid];

                $sourceUid = $this->resolveCopiedSourceUid($dataHandler, $insertedUid);

                // when inserting the container, fix all previous inserted elements
                if (
                    ColumnRowUtility::isOurContainerCType($record['CType']) &&
                    $sourceUid !== null &&
                    array_key_exists('pid', $record) &&
                    array_key_exists('l18n_parent', $record) &&
                    array_key_exists('sys_language_uid', $record)
                ) {
                    $mappingLanguage = (int)$record['sys_language_uid'];

                    // if the container is translated, we have to use the record uid in the default language
                    if (ColumnRowUtility::rowIsTranslatedInConnectionMode($record)) {                        
                        $recordDefaultLanguage = $this->database->fetchOneRecord($sourceUid);
                        if(isset($recordDefaultLanguage['l10n_source'])) {
                            $sourceUid = (int)$recordDefaultLanguage['l10n_source'];
                            $insertedUid = (int)$record['l18n_parent'];
                            $mappingLanguage = 0;
                        }
                    }

                    $childContentElements = $this->database->fetchRecordsByParentAndLanguage($sourceUid, (int)$record['sys_language_uid']);

                    // just use elements from the same page
                    $childContentElements = array_filter($childContentElements, fn($element) => $element['pid'] === $record['pid']);

                    if($childContentElements !== []) {
                        
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
                        $originalColumnRow = $this->database->fetchOneRecord((int)$originalColumn['parent_column_row']);

                        if(
                            $originalColumnRow &&
                            isset($originalColumnRow['CType']) &&
                            ColumnRowUtility::isOurContainerCType($originalColumnRow['CType'])
                        ) { 
                            $newColumnRowUid = (int)$record['tx_container_parent'];
                            $newColumnRow = $this->database->fetchOneRecord($newColumnRowUid);
                            
                            if($newColumnRow && isset($newColumnRow['CType']) && ColumnRowUtility::isOurContainerCType($newColumnRow['CType'])) {

                                $colPosMap = $this->createColPosRemappingBasedOnOrder((int)$originalColumnRow['uid'], $newColumnRowUid, (int)$originalColumnRow['sys_language_uid']);

                                if(
                                    $colPosMap !== [] &&
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

    protected function resolveCopiedSourceUid(DataHandler $dataHandler, int $targetUid): ?int
    {
        $sourceUid = array_search($targetUid, $this->getContentCopyMapping($dataHandler), true);

        return $sourceUid === false ? null : (int)$sourceUid;
    }

    protected function getContentCopyMapping(DataHandler $dataHandler): array
    {
        $mapping = ($dataHandler->copyMappingArray_merged['tt_content'] ?? []) + ($dataHandler->copyMappingArray['tt_content'] ?? []);
        $contentCopyMapping = [];

        foreach ($mapping as $sourceUid => $targetUid) {
            $contentCopyMapping[(int)$sourceUid] = (int)$targetUid;
        }

        return $contentCopyMapping;
    }

    protected function fixColPosAfterCopyPageCmdmap(DataHandler $dataHandler): void
    {
        $targetPageIds = array_map('intval', array_values($dataHandler->copyMappingArray_merged['pages'] ?? []));

        if ($targetPageIds === []) {
            return;
        }

        $connection = $this->connectionPool->getConnectionForTable('tt_content');
        $rows = $connection->executeQuery(
            'SELECT child.uid AS child_uid, new_col.uid AS new_column_uid
            FROM tt_content child
            INNER JOIN tx_jarcolumnrow_columns old_col
                ON old_col.uid = CAST(SUBSTRING(child.colPos, 5) AS UNSIGNED)
            INNER JOIN tx_jarcolumnrow_columns new_col
                ON new_col.parent_column_row = child.tx_container_parent
                AND new_col.sorting = old_col.sorting
                AND new_col.sys_language_uid = old_col.sys_language_uid
                AND new_col.deleted = 0
                AND new_col.t3ver_oid = 0
            WHERE child.pid IN (:targetPageIds)
                AND child.deleted = 0
                AND child.t3ver_oid = 0
                AND child.tx_container_parent > 0
                AND child.colPos LIKE :columnRowColPosPrefix
                AND old_col.parent_column_row <> child.tx_container_parent',
            [
                'targetPageIds' => $targetPageIds,
                'columnRowColPosPrefix' => ColumnRowUtility::$colPosPrefix . '%',
            ],
            [
                'targetPageIds' => Connection::PARAM_INT_ARRAY,
                'columnRowColPosPrefix' => Connection::PARAM_STR,
            ]
        )->fetchAllAssociative();

        foreach ($rows as $row) {
            $connection->update(
                'tt_content',
                ['colPos' => ColumnRowUtility::decodeColPos(['uid' => (int)$row['new_column_uid']])],
                ['uid' => (int)$row['child_uid']],
            );
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

        if ($originalColumns === []) {
            return $colPosMap;
        }

        $columnMap = [];
        foreach ($originalColumns as $column) {
            $columnMap[$column['sorting']] = [
                'old_uid' => $column['uid']
            ];
        }

        $newColumns = $this->columnDatabase->fetchRecordsByParentAndLanguage($targetUid, $sys_language_uid);

        if ($newColumns === []) {
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
            $newTranslatedColumns = array_filter($dataMap['tx_jarcolumnrow_columns'], fn($element) => isset($element['l10n_parent']) &&
            str_starts_with((string) $element['l10n_parent'], 'NEW') &&
            isset($element['sys_language_uid']) &&
            $element['sys_language_uid'] > 0);

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
