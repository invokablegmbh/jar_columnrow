<?php

declare(strict_types=1);
namespace Jar\Columnrow\Update;

use TYPO3\CMS\Install\Updates\UpgradeWizardInterface;
use Doctrine\DBAL\DBALException;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;


/** @package Jar\Feditor\Updates */
class ColumnRowCtypeUpdateWizard implements UpgradeWizardInterface
{
    protected $mapping = [
        'j77template_columnrow' => 'jarcolumnrow_columnrow',
        'j77template_multicontainer' => 'jarcolumnrow_multicontainer'
    ];

    /**
     * Return the identifier for this wizard
     * This should be the same string as used in the ext_localconf class registration
     *
     * @return string
     */
    public function getIdentifier(): string
    {
        return 'feditor_columnRowCtypeUpdateWizard';
    }

    /**
     * Return the speaking name of this wizard
     *
     * @return string
     */
    public function getTitle(): string
    {
        return 'Jar: Update old tt_content column row CTypes';
    }

    /**
     * Return the description for this wizard
     *
     * @return string
     */
    public function getDescription(): string
    {
        return 'Updates the old CTypes (j77template_) to new extension ctype (jarcolumnrow_.)';
    }

    /**
     * Execute the update
     *
     * Called when a wizard reports that an update is necessary
     *
     * @return bool
     */
    public function executeUpdate(): bool
    {
        try {
            $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('tt_content');
            $queryBuilder->getRestrictions()->removeAll();
            foreach($this->mapping as $old => $new) {
                // Old Ctype => new cType
                $queryBuilder
                    ->update('tt_content')
                    ->where(
                        $queryBuilder->expr()->eq('CType', $queryBuilder->createNamedParameter($old))
                    )
                    ->set('CType', $new)
                    ->execute();
            }
            
        } catch (\Exception $exception) {
            return false;
        }
        return true;
    }

    /**
     * Is an update necessary?
     *
     * Is used to determine whether a wizard needs to be run.
     * Check if data for migration exists.
     *
     * @return bool
     */
    public function updateNecessary(): bool
    {   
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('tt_content');
        $queryBuilder->getRestrictions()->removeAll();
        $oldCytpe = !!reset(reset($queryBuilder
            ->count('CType')
            ->from('tt_content')
            ->where(
                $queryBuilder->expr()->in('CType', $queryBuilder->createNamedParameter(array_keys($this->mapping), Connection::PARAM_STR_ARRAY))
            )
            ->execute()
            ->fetchAll()));

        return $oldCytpe;
    }

    /**
     * Returns an array of class names of prerequisite classes
     *
     * This way a wizard can define dependencies like "database up-to-date" or
     * "reference index updated"
     *
     * @return string[]
     */
    public function getPrerequisites(): array
    {
        return [];
    }
    
}
