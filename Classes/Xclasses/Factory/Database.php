<?php

declare(strict_types=1);

namespace Jar\Columnrow\Xclasses\Factory;

use Jar\Columnrow\Services\GateService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;

/*
 * This file is part of TYPO3 CMS-based extension "jar_columnrow" by invokable.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */


class Database extends \B13\Container\Domain\Factory\Database
{
    public function fetchOneRecord(int $uid): ?array
    {        
        $result = parent::fetchOneRecord($uid);
        GeneralUtility::makeInstance(GateService::class)->setLastUsedRow($result);
        return $result;
    }

    public function fetchOneDefaultRecord(array $record): ?array
    {
        $result = parent::fetchOneDefaultRecord($record);
        if($result !== null) {
            // overload current connected row with default values        
            GeneralUtility::makeInstance(GateService::class)->setLastUsedRow($result);
        }
        return $result;
    }
}
