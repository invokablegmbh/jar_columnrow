<?php

declare(strict_types=1);
namespace Jar\Columnrow\Services;

use Doctrine\DBAL\Schema\Column;
use Jar\Columnrow\Utilities\ColumnRowUtility;
use Jar\Utilities\Services\ReflectionService;
use Jar\Utilities\Utilities\IteratorUtility;
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

        if(isset($this->generatedGrids[$row['uid']])) {            
            return $this->generatedGrids[$row['uid']];
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

        foreach($reflectedRow['columns'] as $column) {
            // Check if we need to start a new row with the column width
            $currentColumntWidth = $column['col_lg'];
            if($currentColumntWidth < 1) {
                $currentColumntWidth = $gridbase;
            }
            $existingColumnWidth = IteratorUtility::pluck($grid[count($grid) - 1], 'colspan');
            // create the sum of existing columns
            $newColumnWidth = (int) array_reduce($existingColumnWidth, function($carry, $item) {
                return $carry + $item;
            }, $currentColumntWidth);

            if($newColumnWidth > $gridbase) {
                $grid[] = [];
            }
            $grid[count($grid) - 1][] = [
                'name' => '',//$currentColumntWidth,
                'colPos' => ColumnRowUtility::decodeColPos($column, $row),
                'colspan' => $currentColumntWidth,
            ];
        }

        $this->generatedGrids[$row['uid']] = $grid;

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
    protected function getReflectedRow(?array $row): ?array
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
