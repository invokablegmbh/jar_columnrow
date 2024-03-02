<?php

declare(strict_types=1);
namespace Jar\Columnrow\Services;

use Doctrine\DBAL\Schema\Column;
use Jar\Columnrow\Utilities\ColumnRowUtility;
use Jar\Utilities\Services\ReflectionService;
use Jar\Utilities\Utilities\IteratorUtility;
use Jar\Utilities\Utilities\LocalizationUtility;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;

/*
 * This file is part of TYPO3 CMS-based extension "jar_columnrow" by invokable.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

class GateService implements SingletonInterface
{
    protected ?array $lastUsedRow = null;
    protected ?array $originalTranslationOfLastUsedRow = null; // useful, when we have a connected default / translated record situation
    protected array $reflectedRows = [];
    protected array $generatedGrids = [];

    /**
     * @param ReflectionService $reflectionService 
     * @return void 
     */
    public function __construct(
        private readonly ReflectionService $reflectionService
    ) {
        $reflectionService
        ->setTableColumnWhitelist([
            'tt_content' => ['columnrow_*']
        ])
        ->setTableColumnRemoveablePrefixes([
            'tt_content' => ['columnrow_']
        ]);
    }   
    
    /**
     * @param null|array $row 
     * @return array 
     */
    public function getContainerGridFromRow(?array $row): array
    {       
        $reflectedRow = $this->getReflectedRow($row);

        if($reflectedRow === null) {
            return [];
        }

        if(!isset($reflectedRow['uid']) || !isset($reflectedRow['columns']) || !count($reflectedRow['columns'])) {
            return [];
        }

        $cacheId = $this->originalTranslationOfLastUsedRow === null ? $row['uid'] : $this->originalTranslationOfLastUsedRow['uid'];

        if(isset($this->generatedGrids[$cacheId])) {            
            return $this->generatedGrids[$cacheId];
        }

        // Create a full-width column to display the child columns at the correct width
        // we have to use the name "unused" to make this column unuseable (@see TYPO3\CMS\Backend\View\BackendLayout\Grid\GridRow)
        
        $gridbase = ColumnRowUtility::getGridBase();

        $grid = [
            [
                [
                    'name' => 'unused',
                    'colPos' => -1,
                    'colspan' => $gridbase
                ]
            ],
            [
                /* place for first columns */
            ]
        ];

        // translation: overwrite title-fields of columns with the translated values (connected mode), to show right labels in backend
        if($row === $this->lastUsedRow && $this->originalTranslationOfLastUsedRow !== null) {
            $orignalLanguageReflectedRow = $this->getReflectedRow($this->originalTranslationOfLastUsedRow);
            foreach($reflectedRow['columns'] as $key => $column) {
                if(isset($orignalLanguageReflectedRow['columns'][$key]['title'])) {
                    $reflectedRow['columns'][$key]['title'] = $orignalLanguageReflectedRow['columns'][$key]['title'];
                }
            }
        }

        foreach($reflectedRow['columns'] as $column) {
            // fallback to gridbase if no column width is set (like the accordion items do)
            $currentColumntWidth = $column['col_lg'] ?? $gridbase;

            // Special Column Marker (-1,0, custom values)
            $isSpecialColumn = $currentColumntWidth < 0 || $currentColumntWidth > $gridbase;            
            if($isSpecialColumn) {
                $currentColumntWidth = $gridbase;
            }

            // Check if we need to start a new row with the column width
            $existingColumnWidth = IteratorUtility::pluck($grid[count($grid) - 1], 'colspan');
            // create the sum of existing columns
            $newColumnWidth = (int) array_reduce($existingColumnWidth, function($carry, $item) {
                return $carry + $item;
            }, $currentColumntWidth);

            if($newColumnWidth > $gridbase) {
                $grid[] = [];
            }
            $columnLabel = '';
            if(isset($column['title']) && !empty($column['title'])) {
                $columnLabel = $column['title'];
            } else {
                if($isSpecialColumn) {                
                    $columnLabel = LocalizationUtility::localize('LLL:EXT:jar_columnrow/Resources/Private/Language/locallang_be.xlf:custom') . ' (' . $column['col_lg'] . ')';
                } else {
                    $columnLabel = number_format($currentColumntWidth  / $gridbase * 100, 2, '.', '') . '%';
                }
            }
            
            $grid[count($grid) - 1][] = [
                'name' => $columnLabel,
                'colPos' => ColumnRowUtility::decodeColPos($column, $row),
                'colspan' => $currentColumntWidth,
            ];
        }

        $this->generatedGrids[$cacheId] = $grid;

        return $grid;
    }

    /** @return array  */
    public function getContainerGridFromLastUsedRow(): array
    {
        return $this->getContainerGridFromRow($this->lastUsedRow);
    }

    /**
     * @param null|array $row 
     * @return null|array 
     */
    public function getReflectedRow(?array $row): ?array
    {
        if($row === null) {
            return null;
        }   
        
        if(isset($this->reflectedRows[$row['uid']])) {            
            return $this->reflectedRows[$row['uid']];
        }
        $reflectedRow = $this->reflectionService->buildArrayByRow($row, 'tt_content');

        $this->reflectedRows[$row['uid']] = $reflectedRow;
        return $reflectedRow;
    }

    /**
     * @param null|array $row 
     * @return GateService 
     */
    public function setLastUsedRow(?array $row): GateService
    {
        // check if we have a connected default / translated record situation
        // and store the original translation of the last used row
        // for using individual column titles in the grid
        if ($this->lastUsedRow !== null &&
            $this->lastUsedRow['sys_language_uid'] !== $row['sys_language_uid'] &&
            $this->lastUsedRow['sys_language_uid'] > 0 &&
            $this->lastUsedRow['l18n_parent'] === $row['uid']
        ) {
            $this->originalTranslationOfLastUsedRow = $this->lastUsedRow;
        } else {
            $this->originalTranslationOfLastUsedRow = null;
        }

        $this->lastUsedRow = $row;
        return $this;
    }

    /**
     * @return null|array 
     */
    public function getLastUsedRow(): ?array
    {
        return $this->lastUsedRow;
    }
}
