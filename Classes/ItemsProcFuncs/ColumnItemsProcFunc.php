<?php

declare(strict_types=1);

namespace Jar\Columnrow\ItemsProcFuncs;

use Jar\Columnrow\Utilities\ColumnRowUtility;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;

/*
 * This file is part of TYPO3 CMS-based extension "jar_columnrow" by invokable.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

class ColumnItemsProcFunc implements SingletonInterface
{
    protected ?array $defaultWidthItems = null;
    protected ?array $defaultOrderItems = null;

    /** @return array  */
    protected function getDefaultWidthItems():array {
        if($this->defaultWidthItems !== null) {
            return $this->defaultWidthItems;
        }

        $base = ColumnRowUtility::getGridBase();

        $defaultCols = [];
        for ($i = 0; $i < $base; $i++) {
            $colSize = $i + 1;
            $percent = number_format($colSize  / $base * 100, 2, '.', '') . '%';
            // pad the colsize to the digits of the base
            $paddedColSize = str_pad((string) $colSize, strlen((string) $base), '0', STR_PAD_LEFT);
            $defaultCols[] = [
                '[' . $paddedColSize . '] ' . $percent,
                $colSize
            ];
        }

        return $this->defaultWidthItems = $defaultCols;
    }

    /**
     * Modify the items of a select field
     *
     * @param array $params (passed by reference)
     */
    public function modifyWidthItems(array &$params): void
    {
        $params['items'] = array_merge($this->getDefaultWidthItems(), $params['items']);

        // sort by column size
        usort($params['items'], function($a, $b) {
            $a = (float) $a[1];
            $b = (float) $b[1];
            if ($a == $b) {
                return 0;
            }
            return ($a < $b) ? -1 : 1;
        });

        $params['items'] = array_reverse($params['items']);

        // add default mode to all widths, except for the main width field
        if($params['field'] !== 'col_lg') {
            $params['items']  = array_merge([
                [ 'LLL:EXT:jar_columnrow/Resources/Private/Language/locallang_be.xlf:item_automatic', 0 ]
            ], $params['items']);
        }

        // add hide column option to all widths (in extended mode)
        $extendedView = (bool) ($params['row']['extended'] ?? false);

        if($extendedView) {
            $params['items'][] = ['LLL:EXT:jar_columnrow/Resources/Private/Language/locallang_be.xlf:item_do_now_show', -1 ];
        }
    }

    /** @return array  */
    protected function getDefaultOrderItems(): array
    {
        if ($this->defaultOrderItems !== null) {
            return $this->defaultOrderItems;
        }

        $base = ColumnRowUtility::getGridBase();

        $defaultCols = [];
        for ($i = 0; $i < $base; $i++) {
            $colSize = $i + 1;            
            $defaultCols[] = [
                $colSize,
                $colSize
            ];
        }

        return $this->defaultOrderItems = $defaultCols;
    }


    /**
     * Modify the items of a select field
     *
     * @param array $params (passed by reference)
     */
    public function modifyOrderItems(array &$params): void {
        $params['items'] = array_merge($this->getDefaultOrderItems(), $params['items']);

        // sort by size
        usort($params['items'], function ($a, $b) {
            $a = (float) $a[1];
            $b = (float) $b[1];
            if ($a == $b) {
                return 0;
            }
            return ($a < $b) ? -1 : 1;
        });

        $params['items']  = array_merge([
            ['LLL:EXT:jar_columnrow/Resources/Private/Language/locallang_be.xlf:default', 0]
        ], $params['items']);
    }


    /**
     * Modify the items of a select field
     *
     * @param array $params (passed by reference)
     */
    public function modifyOffsetItems(array &$params): void
    {
        $params['items'] = array_merge($this->getDefaultWidthItems(), $params['items']);

        // sort by size
        usort($params['items'], function ($a, $b) {
            $a = (float) $a[1];
            $b = (float) $b[1];
            if ($a == $b) {
                return 0;
            }
            return ($a < $b) ? -1 : 1;
        });

        $params['items']  = array_merge([
            ['LLL:EXT:jar_columnrow/Resources/Private/Language/locallang_be.xlf:no_offset', 0]
        ], $params['items']);
    }
}
