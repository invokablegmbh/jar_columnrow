<?php

declare(strict_types=1);

namespace Jar\Columnrow\Xclasses\View;

use Jar\Columnrow\Utilities\ColumnRowUtility;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;

/*
 * This file is part of TYPO3 CMS-based extension "jar_columnrow" by invokable.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */


class GridColumnItem extends \TYPO3\CMS\Backend\View\BackendLayout\Grid\GridColumnItem
{
    public function getRecord(): array
    {
        return $this->record;
    }
}
