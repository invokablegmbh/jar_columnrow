<?php
namespace Jar\Columnrow\DataProcessing;

use B13\Container\DataProcessing\ContainerProcessor;
use Jar\Columnrow\Utilities\ColumnRowUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;
use TYPO3\CMS\Frontend\ContentObject\DataProcessorInterface;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use B13\Container\Domain\Factory\ContainerFactory;
use Jar\Columnrow\Services\GateService;
use Jar\Utilities\Utilities\IteratorUtility;

class ColumnRowDataProcessor implements DataProcessorInterface
{
    public function __construct(
        private readonly ContainerFactory $containerFactory
    ) {
    }

    /**
     * Process data of a record to resolve File objects to the view
     *
     * @param ContentObjectRenderer $cObj The data of the content element or page
     * @param array $contentObjectConfiguration The configuration of Content Object
     * @param array $processorConfiguration The configuration of this processor
     * @param array $processedData Key/value store of processed data (e.g. to be passed to a Fluid View)
     * @return array the processed data as key/value store
     */
    public function process(ContentObjectRenderer $cObj, array $contentObjectConfiguration, array $processorConfiguration, array $processedData)
    {  
        if(!isset($processedData['data'])) {
            return $processedData;
        }

        $row = $processedData['data'];

        if(!isset($row['sys_language_uid']) || !isset($row['l18n_parent'])){
            return $processedData;
        }

        $isTranslatedElementInConnectedMode = ColumnRowUtility::rowIsTranslatedInConnectionMode($row);

        $container = $this->containerFactory->buildContainer($isTranslatedElementInConnectedMode ? $row['_LOCALIZED_UID'] : $row['uid']);

        $toReflectedRow = $isTranslatedElementInConnectedMode ? $container->getContainerRecord() : $row;
        $reflectedRow = GeneralUtility::makeInstance(GateService::class)->getReflectedRow($toReflectedRow);
                


        // render column content via container processor
        $containerProcessor = GeneralUtility::makeInstance(ContainerProcessor::class);
        $containerProcessorResult = $containerProcessor->process($cObj, $contentObjectConfiguration, $processorConfiguration, $processedData);
        $colsWithChildren = [];
        foreach (array_keys($containerProcessorResult) as $key) {
            if (strpos($key, 'children_') === 0) {
                $colPos = explode('_', $key, 2)[1];
                $colsWithChildren[$colPos] = IteratorUtility::pluck($containerProcessorResult[$key], 'renderedContent');
            }
        }

        $processedData = $processedData + $reflectedRow + ColumnRowUtility::getFrontendAttributesByPopulatedRow($reflectedRow);

        if (!isset($processedData['columns'])) {
            return $processedData;
        }
      
        // add content to columns        
        foreach($processedData['columns'] as $key => $column) {           
            $colPos = ColumnRowUtility::decodeColPos([
                'uid' => $column['uid']
            ]);

            if(isset($colsWithChildren[$colPos])) {
                $processedData['columns'][$key]['content'] = $colsWithChildren[$colPos];
            }
        }

        // add column css classes to columns        
        // @todo: refactor this to a separate data processor
        
        foreach($processedData['columns'] as $k => $column) {
            $finalColumnClass = $finalOrderClass = $finalOffsetClass = '';
            if($column['extended']) {
                if($column['col_xs'] != '12' && $column['col_xs'] != '0' && ($column['col_xs'] != $column['col_sm'])) {
                    $finalColumnClass .= ' col-xs-' . $column['col_xs'];
                }
                if($column['col_sm'] != '12' && $column['col_sm'] != '0' && ($column['col_sm'] != $column['col_md'])) {
                    $finalColumnClass .= ' col-sm-' . $column['col_sm'];
                }
                if($column['col_md'] != '12' && $column['col_md'] != '0' && ($column['col_md'] != $column['col_lg'])) {
                    $finalColumnClass .= ' col-md-' . $column['col_md'];
                }

                if($column['order_xs'] != $column['order_sm']) {
                    $finalOrderClass .= ' order-xs-' . $column['order_xs'];
                }
                if($column['order_sm'] != $column['order_md']) {
                    $finalOrderClass .= ' order-sm-' . $column['order_sm'];
                }
                if($column['order_md'] != $column['order_lg']) {
                    $finalOrderClass .= ' order-md-' . $column['order_md'];
                }
                $finalOrderClass .= ' order-lg-' . $column['order_lg'];

                if($column['offset_xs'] != '12' && $column['offset_xs'] != '0' && ($column['offset_xs'] != $column['offset_sm'])) {
                    $finalOffsetClass .= ' offset-xs-' . $column['offset_xs'];
                }
                if($column['offset_sm'] != '12' && $column['offset_sm'] != '0' && ($column['offset_sm'] != $column['offset_md'])) {
                    $finalOffsetClass .= ' offset-sm-' . $column['offset_sm'];
                }
                if($column['offset_md'] != '12' && $column['offset_md'] != '0' && ($column['offset_md'] != $column['offset_lg'])) {
                    $finalOffsetClass .= ' offset-md-' . $column['offset_md'];
                }
                if($column['offset_lg'] != '12' && $column['offset_lg'] != '0') {
                    $finalOffsetClass .= ' offset-lg-' . $column['offset_lg'];
                }
            }
            if(!empty($column['col_lg'])) {
                $finalColumnClass .= ' col-lg-' . $column['col_lg'];
            }

            $processedData['columns'][$k]['cssClass'] = $finalColumnClass;
            $processedData['columns'][$k]['orderClass'] = $finalOrderClass;
            $processedData['columns'][$k]['offsetClass'] = $finalOffsetClass;
        }

        // @todo: also refactor this to a separate data processor
        $processedData['space_before_class'] = $row['space_before_class'] ? 'space-before-' . $row['space_before_class'] : '';
        $processedData['space_after_class'] = $row['space_after_class'] ? 'space-after-' . $row['space_after_class'] : '';

       
        return $processedData;
    }
}
