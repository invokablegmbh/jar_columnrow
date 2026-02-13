<?php

declare(strict_types=1);
namespace Jar\Columnrow\Update;

use Jar\Columnrow\Utilities\ColumnRowUtility;
use Jar\Utilities\Utilities\IteratorUtility;
use TYPO3\CMS\Install\Updates\UpgradeWizardInterface;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Service\FlexFormService;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/** @package Jar\Columnrow\Update */
class MigrateFluxToContainer implements UpgradeWizardInterface
{    
    public function __construct(private readonly ConnectionPool $connectionPool)
    {
    }
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
            ->executeQuery()
            ->fetchAllAssociative();

        foreach ($contentElements as $contentElement) {
            $flexForm = GeneralUtility::makeInstance(FlexFormService::class)->convertFlexFormContentToArray($contentElement['pi_flexform']);
            
            // if no columns are preset add one dummy column
            $dummyColumn = false;
            if (
                !isset($flexForm['columns']) || 
                (isset($flexForm['columns']) && is_array($flexForm['columns']) && !count($flexForm['columns'])) ||
                (isset($flexForm['columns']) && is_string($flexForm['columns']) && $flexForm['columns'] === '')
            ) {
                $flexForm['columns'] = [];
                $flexForm['columns'][] = [
                    'column' => [
                        'col' => ColumnRowUtility::getGridBase()
                    ]
                ];
                $dummyColumn = true;
            }

            $contentRow = [
                'columnrow_content_width' => $flexForm['contentWidth'] == 'fullwidth' ? 'container full-width' : 'container content',
                'columnrow_select_background' => $flexForm['selectBackground'] ?? '',
                'columnrow_row_background' => $flexForm['rowBackground'] ?? '',
                'columnrow_row_user_background' => $flexForm['rowUserBackground'] ?? '',
                'columnrow_row_background_image' => $flexForm['rowBackgroundImage'] ?? '',
                'columnrow_additional_row_class' => $flexForm['additionalRowClass'] ?? '',
                'columnrow_columns' => isset($flexForm['columns']) && is_array($flexForm['columns']) ? count($flexForm['columns']) : 0,
            ];

            $connectionPool = $this->connectionPool;
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
                
                foreach($columns as $index => $column) {  
                    // in V2 we merged the col-lg and col into one field
                    $classicCol = isset($column['col']) ? ((int) $column['col']) : null;
                    $classicColLg = isset($column['large']) ? ((int) $column['large']) : null;

                    $colLg = ($classicColLg ?? $classicCol);

                    if($colLg === null) {
                        throw new \Exception("Not matching main col size found at tt_content " . $contentElement['uid'], 1930954329);                        
                    }

                    $columnRow = [
                        'pid' => $contentElement['pid'],
                        'col_lg' => $colLg,
                        'col_md' => $column['medium'] ?? null,
                        'col_sm' => $column['small'] ?? null,
                        // 'col_xs' => isset($column['small']) ? $column['small'] : null,
                        'order_lg' => $column['order-large'] ?? null,
                        'order_md' => $column['order-medium'] ?? null,
                        'order_sm' => $column['order-small'] ?? null,
                        // 'order_xs' => isset($column['order-small']) ? $column['order-small'] : null,
                        'offset_lg' => $column['offset-large'] ?? null,
                        'offset_md' => $column['offset-medium'] ?? null,
                        'offset_sm' => $column['offset-small'] ?? null,
                        // 'offset_xs' => isset($column['offset-small']) ? $column['offset-small'] : null,
                        'additional_col_class' => $column['additionalColClass'] ?? '',
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
                    if($dummyColumn && count($columns) === 1) {
                        $fluxBasedColPos = ($contentElement['uid'] * 100);
                    }
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
            ->executeQuery()
            ->fetchAllAssociative();

        return (bool) reset(reset($queryBuilder));
    }

    protected function getQueryForFluxBasedColumnRowElements(): QueryBuilder
    {
        $connectionPool = $this->connectionPool;

        $containerBasedColumnRowUids = $connectionPool->getQueryBuilderForTable('tx_jarcolumnrow_columns')
            ->select('parent_column_row')
            ->from('tx_jarcolumnrow_columns')
            ->executeQuery()
            ->fetchAllAssociative();

        $containerBasedColumnRowUids = array_unique(IteratorUtility::pluck($containerBasedColumnRowUids, 'parent_column_row'));

        // Stelle sicher, dass die UIDs numerisch sind.
        $containerBasedColumnRowUids = array_map(intval(...), $containerBasedColumnRowUids);

        $queryBuilder = $connectionPool->getQueryBuilderForTable('tt_content');
        $queryBuilder->getRestrictions()->removeAll();
        $query = $queryBuilder            
            ->from('tt_content')
            ->where(
                $queryBuilder->expr()->like('CType', $queryBuilder->createNamedParameter('jarcolumnrow_%'))
            )
            ->andWhere(
                $queryBuilder->expr()->neq('pi_flexform', $queryBuilder->createNamedParameter(''))
            )->andWhere(
                $queryBuilder->expr()->eq('deleted', 0)
            );

        if (count($containerBasedColumnRowUids) > 0) {
            $query->andWhere(
                $queryBuilder->expr()->notIn('uid', $containerBasedColumnRowUids)
            );
        }

        return $query;
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
            ColumnRowCtypeUpdateWizard::class
        ];
    }

    
}
