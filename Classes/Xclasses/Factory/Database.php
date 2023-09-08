<?php

declare(strict_types=1);

namespace Jar\Columnrow\Xclasses\Factory;

/*
 * This file is part of TYPO3 CMS-based extension "jar_columnrow" by invokable.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */


use TYPO3\CMS\Extbase\Utility\DebuggerUtility;

class Database extends \B13\Container\Domain\Factory\Database
{
    public function fetchOneRecord(int $uid): ?array
    {        
        $result = parent::fetchOneRecord($uid);
        $GLOBALS['feierabendanfang'] = $result;        
        return $result;
    }
}
