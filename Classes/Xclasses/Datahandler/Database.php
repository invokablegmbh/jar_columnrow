<?php

declare(strict_types=1);

namespace Jar\Columnrow\Xclasses\Datahandler;

/*
 * This file is part of TYPO3 CMS-based extension "jar_columnrow" by invokable.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */


use TYPO3\CMS\Core\SingletonInterface;
use B13\Container\Tca\ContainerConfiguration;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;

class Database extends \B13\Container\Hooks\Datahandler\Database implements SingletonInterface
{
    public function fetchOneRecord(int $uid): ?array
    {        
        return parent::fetchOneRecord($uid);
    }

    public function fetchOverlayRecords(array $record): array
    {
        return parent::fetchOverlayRecords($record);
    }

    public function fetchOneTranslatedRecordByl10nSource(int $uid, int $language): ?array
    {
        return parent::fetchOneTranslatedRecordByl10nSource($uid, $language);
    }

    public function fetchOneTranslatedRecordByLocalizationParent(int $uid, int $language): ?array
    {
        return parent::fetchOneTranslatedRecordByLocalizationParent($uid, $language);
    }

    public function fetchRecordsByParentAndLanguage(int $parent, int $language): array
    {
        return parent::fetchRecordsByParentAndLanguage($parent, $language);
    }

    public function fetchContainerRecordLocalizedFreeMode(int $defaultUid, int $language): ?array
    {
        return parent::fetchContainerRecordLocalizedFreeMode($defaultUid, $language);
    }
}
