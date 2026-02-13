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

use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Database\Query\Restriction\EndTimeRestriction;
use TYPO3\CMS\Core\Database\Query\Restriction\HiddenRestriction;
use TYPO3\CMS\Core\Database\Query\Restriction\StartTimeRestriction;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class ContentDatabase implements SingletonInterface
{
    public function __construct(private readonly ConnectionPool $connectionPool)
    {
    }
    protected function getQueryBuilder(): QueryBuilder
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable('tt_content');
        $queryBuilder->getRestrictions()
            ->removeByType(HiddenRestriction::class)
            ->removeByType(StartTimeRestriction::class)
            ->removeByType(EndTimeRestriction::class);
        return $queryBuilder;
    }

    public function fetchOneRecordByOrigUidAndPid(int $origUid, int $pid): ?array
    {
        $queryBuilder = $this->getQueryBuilder();
        $stm = $queryBuilder->select('*')
            ->from('tt_content')->where($queryBuilder->expr()->eq(
            't3_origuid',
            $queryBuilder->createNamedParameter($origUid, Connection::PARAM_INT)
        ), $queryBuilder->expr()->eq(
            'pid',
            $queryBuilder->createNamedParameter($pid, Connection::PARAM_INT)
        ))->executeQuery();

        $record = $stm->fetchAssociative();
        
        if ($record === false) {
            return null;
        }

        return $record;
    }
}
