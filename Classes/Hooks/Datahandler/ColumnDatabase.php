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

class ColumnDatabase implements SingletonInterface
{
    private array $fetchedOneRecords = [];
    public function __construct(private readonly ConnectionPool $connectionPool)
    {
    }

    protected function getQueryBuilder(): QueryBuilder
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable('tx_jarcolumnrow_columns');
        $queryBuilder->getRestrictions()
            ->removeByType(HiddenRestriction::class)
            ->removeByType(StartTimeRestriction::class)
            ->removeByType(EndTimeRestriction::class);
        return $queryBuilder;
    }

    public function fetchOneRecord(int $uid): ?array
    {
        if(isset($this->fetchedOneRecords[$uid])) {
            return $this->fetchedOneRecords[$uid];
        }

        $queryBuilder = $this->getQueryBuilder();
        $stm = $queryBuilder->select('*')
            ->from('tx_jarcolumnrow_columns')->where($queryBuilder->expr()->eq(
            'uid',
            $queryBuilder->createNamedParameter($uid, Connection::PARAM_INT)
        ))->executeQuery();

        $record = $stm->fetchAssociative();
        
        if ($record === false) {
            return null;
        }

        $this->fetchedOneRecords[$uid] = $record;

        return $record;
    }

    public function fetchOverlayRecords(array $record): array
    {
        $queryBuilder = $this->getQueryBuilder();
        $stm = $queryBuilder->select('*')
            ->from('tx_jarcolumnrow_columns')->where($queryBuilder->expr()->eq(
            'l18n_parent',
            $queryBuilder->createNamedParameter((int)$record['uid'], Connection::PARAM_INT)
        ))->executeQuery();
        return (array)$stm->fetchAllAssociative();
    }

    public function fetchOneTranslatedRecordByl10nSource(int $uid, int $language): ?array
    {
        $queryBuilder = $this->getQueryBuilder();
        $stm = $queryBuilder->select('*')
            ->from('tx_jarcolumnrow_columns')->where($queryBuilder->expr()->eq(
            'l10n_source',
            $queryBuilder->createNamedParameter($uid, Connection::PARAM_INT)
        ), $queryBuilder->expr()->eq(
            'sys_language_uid',
            $queryBuilder->createNamedParameter($language, Connection::PARAM_INT)
        ))->executeQuery();

        $record = $stm->fetchAssociative();
        
        if ($record === false) {
            return null;
        }
        return $record;
    }

    public function fetchOneTranslatedRecordByLocalizationParent(int $uid, int $language): ?array
    {
        $queryBuilder = $this->getQueryBuilder();
        $stm = $queryBuilder->select('*')
            ->from('tx_jarcolumnrow_columns')->where($queryBuilder->expr()->eq(
            'l18n_parent',
            $queryBuilder->createNamedParameter($uid, Connection::PARAM_INT)
        ), $queryBuilder->expr()->eq(
            'sys_language_uid',
            $queryBuilder->createNamedParameter($language, Connection::PARAM_INT)
        ))->executeQuery();

        $record = $stm->fetchAssociative();

        if ($record === false) {
            return null;
        }
        return $record;
    }

    public function fetchRecordsByParentAndLanguage(int $parent, int $language): array
    {
        $queryBuilder = $this->getQueryBuilder();
        $stm = $queryBuilder->select('*')
            ->from('tx_jarcolumnrow_columns')
            ->where(
                $queryBuilder->expr()->eq(
                    'parent_column_row',
                    $queryBuilder->createNamedParameter($parent, Connection::PARAM_INT)
                ),
                $queryBuilder->expr()->eq(
                    'sys_language_uid',
                    $queryBuilder->createNamedParameter($language, Connection::PARAM_INT)
                ),
                $queryBuilder->expr()->eq(
                    't3ver_oid',
                    $queryBuilder->createNamedParameter(0, Connection::PARAM_INT)
                )
            )->orderBy('sorting', 'ASC')->executeQuery();

        return (array)$stm->fetchAllAssociative();
    }

    public function fetchContainerRecordLocalizedFreeMode(int $defaultUid, int $language): ?array
    {
        $queryBuilder = $this->getQueryBuilder();
        $stm = $queryBuilder->select('*')
            ->from('tx_jarcolumnrow_columns')->where($queryBuilder->expr()->eq(
            'l10n_source',
            $queryBuilder->createNamedParameter($defaultUid, Connection::PARAM_INT)
        ), $queryBuilder->expr()->eq(
            'l18n_parent',
            $queryBuilder->createNamedParameter(0, Connection::PARAM_INT)
        ), $queryBuilder->expr()->eq(
            'sys_language_uid',
            $queryBuilder->createNamedParameter($language, Connection::PARAM_INT)
        ), $queryBuilder->expr()->eq(
            't3ver_oid',
            $queryBuilder->createNamedParameter(0, Connection::PARAM_INT)
        ))->executeQuery();
            
        $record = $stm->fetchAssociative();
        
        if ($record === false) {
            return null;
        }
        return $record;
    }

    public function updateRecord(int $uid, array $fields): void
    {
        if(!count($fields)) {
            return;
        }

        $queryBuilder = $this->getQueryBuilder();
        $stm = $queryBuilder->update('tx_jarcolumnrow_columns')
            ->where(
                $queryBuilder->expr()->eq(
                    'uid',
                    $queryBuilder->createNamedParameter($uid, Connection::PARAM_INT)
                )                
        );

        foreach($fields as $name => $value) {
            $stm->set($name, $value);
        }


        $stm->executeStatement();
        
    }
}
