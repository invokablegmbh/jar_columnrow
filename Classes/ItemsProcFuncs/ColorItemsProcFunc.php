<?php

declare(strict_types=1);

namespace Jar\Columnrow\ItemsProcFuncs;

use Jar\Columnrow\Utilities\ColumnRowUtility;
use Jar\Utilities\Utilities\BackendUtility;
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

class ColorItemsProcFunc implements SingletonInterface
{ 
    /**
     * Modify the items of a select field
     *
     * @param array $params (passed by reference)
     */
    public function modifyItems(array &$params): void
    {
        $pageTs = BackendUtility::getCurrentPageTS();

        if (array_key_exists('jar_columnrow', $pageTs) && array_key_exists('colors', $pageTs['jar_columnrow'])) {
            $colors = $pageTs['jar_columnrow']['colors'];
            foreach($colors as $label => $color) {
                $params['items'][] = [$label, $color];
            }
        }

        // resolve labels to sort items
        foreach ($params['items'] as &$item) {
            $item[0] = LocalizationUtility::localize($item[0]);
        }

        // sort items by label and "user" to bottom
        usort($params['items'], function ($a, $b) {
            if ($a[1] === 'user') {
                return 1;
            }
            if ($b[1] === 'user') {
                return -1;
            }
            return strcmp($a[0], $b[0]);
        });
    }
}
