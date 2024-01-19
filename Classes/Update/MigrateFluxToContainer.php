<?php

declare(strict_types=1);
namespace Jar\Columnrow\Update;

use IteratorIterator;
use Jar\Columnrow\Utilities\ColumnRowUtility;
use Jar\Utilities\Utilities\IteratorUtility;
use TYPO3\CMS\Install\Updates\UpgradeWizardInterface;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Service\FlexFormService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;

/** @package Jar\Columnrow\Update */
class MigrateFluxToContainer implements UpgradeWizardInterface
{    
    /**
     * Return the identifier for this wizard
     * This should be the same string as used in the ext_localconf class registration
     *
     * @return string
     */
    public function getIdentifier(): string
    {
        return 'columnrow_migrateFluxToContainer';
    }

    /**
     * Return the speaking name of this wizard
     *
     * @return string
     */
    public function getTitle(): string
    {
        return 'ColumnRow: Convert Flux based Column Rows to Container.';
    }

    /**
     * Return the description for this wizard
     *
     * @return string
     */
    public function getDescription(): string
    {
        return 'Migrate flexform based column definitions to container based definitions.';
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
        $contentElements = $this->getQueryForFluxBasedColumnRowElements()
            ->select('*')
            ->execute()
            ->fetchAll();

        foreach ($contentElements as $contentElement) {
            $flexForm = GeneralUtility::makeInstance(FlexFormService::class)->convertFlexFormContentToArray($contentElement['pi_flexform']);
            DebuggerUtility::var_dump($flexForm, (string) $contentElement['uid']);

            $contentRow = [
                'columnrow_content_width' => isset($flexForm['contentWidth']) ? $flexForm['contentWidth'] : '',
                'columnrow_select_background' => isset($flexForm['selectBackground']) ? $flexForm['selectBackground'] : '',
                'columnrow_row_background' => isset($flexForm['rowBackground']) ? $flexForm['rowBackground'] : '',
                'columnrow_row_user_background' => isset($flexForm['rowUserBackground']) ? $flexForm['rowUserBackground'] : '',
                'columnrow_row_background_image' => isset($flexForm['rowBackgroundImage']) ? $flexForm['rowBackgroundImage'] : '',
                'columnrow_additional_row_class' => isset($flexForm['additionalRowClass']) ? $flexForm['additionalRowClass'] : '',
                'columnrow_columns' => isset($flexForm['columns']) ? count($flexForm['columns']) : 0,
            ];

            $connectionPool = GeneralUtility::makeInstance(ConnectionPool::class);
            $connectionPool->getConnectionForTable('tt_content')
                ->update(
                    'tt_content',
                    $contentRow,
                    ['uid' => $contentElement['uid']]
                );

            // update sys_file_reference when image is set
            if(!empty($contentRow['columnrow_row_background_image']) && ((int) $contentRow['columnrow_row_background_image']) > 0) {                
                $connectionPool->getConnectionForTable('sys_file_reference')
                    ->update(
                        'sys_file_reference',
                        [                            
                            'fieldname' => 'columnrow_row_background_image'
                        ],
                        [
                            'uid_foreign' => $contentElement['uid']   
                        ]
                    );
            }

            if(isset($flexForm['columns']) && is_array($flexForm['columns']) && count($flexForm['columns'])) {
                $columns = IteratorUtility::pluck($flexForm['columns'], 'column');               
                
                $extended = isset($flexForm['extended']) ? ((int) $flexForm['extended']) : 0;
                foreach($columns as $index => $column) {  
                    // in V2 we merged the col-lg and col into one field
                    $classicCol = isset($column['col']) ? ((int) $column['col']) : null;
                    $classicColLg = isset($column['col-lg']) ? ((int) $column['col-lg']) : null;

                    $colLg = $extended ? ($classicColLg ?? $classicCol) : $classicCol;

                    if($colLg === null) {
                        throw new \Exception("Not matching main col size found at tt_content " . $contentElement['uid']);                        
                    }

                    $columnRow = [
                        'extended' => $extended,
                        'col_lg' => $colLg,
                        'col_md' => isset($column['col-md']) ? $column['col-md'] : null,
                        'col_sm' => isset($column['col-sm']) ? $column['col-sm'] : null,
                        'col_xs' => isset($column['col-xs']) ? $column['col-xs'] : null,
                        'order_lg' => isset($column['order-lg']) ? $column['order-lg'] : null,
                        'order_md' => isset($column['order-md']) ? $column['order-md'] : null,
                        'order_sm' => isset($column['order-sm']) ? $column['order-sm'] : null,
                        'order_xs' => isset($column['order-xs']) ? $column['order-xs'] : null,
                        'offset_lg' => isset($column['offset-lg']) ? $column['offset-lg'] : null,
                        'offset_md' => isset($column['offset-md']) ? $column['offset-md'] : null,
                        'offset_sm' => isset($column['offset-sm']) ? $column['offset-sm'] : null,
                        'offset_xs' => isset($column['offset-xs']) ? $column['offset-xs'] : null,
                        'additional_col_class' => isset($column['additionalColClass']) ? $column['additionalColClass'] : '',
                        'parent_column_row' => $contentElement['uid'],
                        'sorting' => $index + 1
                    ];

                    // write columns to database
                    $connectionPool->getConnectionForTable('tx_jarcolumnrow_columns')
                        ->insert(
                            'tx_jarcolumnrow_columns',
                            $columnRow
                        );
                    
                    // get the uid of the new column
                    $columnUid = $connectionPool->getConnectionForTable('tx_jarcolumnrow_columns')
                        ->lastInsertId('tx_jarcolumnrow_columns');
                    
                    // move content elements to the new colpos
                    $fluxBasedColPos = ($contentElement['uid'] * 100) + ($index + 1);
                    $ContainerBasedColPos = ColumnRowUtility::decodeColPos(['uid' => $columnUid]);

                    $connectionPool->getConnectionForTable('tt_content')
                    ->update(
                        'tt_content',
                        [
                            'colPos' => $ContainerBasedColPos,
                            'tx_container_parent' => $contentElement['uid']
                        ],
                        [
                            'colPos' => $fluxBasedColPos,
                            'pid' => $contentElement['pid']
                        ]
                    );
                }
            }
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
        $queryBuilder = $this->getQueryForFluxBasedColumnRowElements()
            ->Count('uid')
            ->execute()
            ->fetchAll();

        return !!reset(reset($queryBuilder));
    }

    protected function getQueryForFluxBasedColumnRowElements(): QueryBuilder
    {        
        $connectionPool = GeneralUtility::makeInstance(ConnectionPool::class);

        $containerBasedColumnRowUids = $connectionPool->getQueryBuilderForTable('tx_jarcolumnrow_columns')
            ->select('parent_column_row')
            ->from('tx_jarcolumnrow_columns')
            ->execute()
            ->fetchAll();

        $containerBasedColumnRowUids = array_unique(IteratorUtility::pluck($containerBasedColumnRowUids, 'parent_column_row'));

        $queryBuilder = $connectionPool->getQueryBuilderForTable('tt_content');
        $queryBuilder->getRestrictions()->removeAll();
        return $queryBuilder            
            ->from('tt_content')
            ->where(
                $queryBuilder->expr()->like('CType', $queryBuilder->createNamedParameter('jarcolumnrow_%'))
            )
            ->andWhere(
                $queryBuilder->expr()->neq('pi_flexform', $queryBuilder->createNamedParameter(''))
            )
            ->andWhere(
                $queryBuilder->expr()->notIn('UID', $containerBasedColumnRowUids)
            );
        
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
        // Column Row Ctype Update Wizard must be executed first
        return [
            \Jar\Columnrow\Update\ColumnRowCtypeUpdateWizard::class
        ];
    }

    
}
