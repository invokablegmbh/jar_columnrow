<?php

declare(strict_types=1);

namespace Jar\Columnrow\TitleFuncs;

use TYPO3\CMS\Backend\Utility\BackendUtility as UtilityBackendUtility;

/*
 * This file is part of TYPO3 CMS-based extension "jar_columnrow" by invokable.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

class ColumnTitleFunc
{ 
    /**
     * Modify Title of a column
     *
     * @param array $params (passed by reference)
     */
    public function columnTitle(array &$parameters): void
    {
        $record = UtilityBackendUtility::getRecord($parameters['table'], $parameters['row']['uid']);
        if(!empty($record['title'])) {
            $parameters['title'] = $record['title'];
        }
    }
}
