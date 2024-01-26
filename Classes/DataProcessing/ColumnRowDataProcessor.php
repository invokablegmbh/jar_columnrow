<?php
namespace Jar\Columnrow\DataProcessing;

use B13\Container\DataProcessing\ContainerProcessor;
use Jar\Columnrow\Utilities\ColumnRowUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;
use TYPO3\CMS\Frontend\ContentObject\DataProcessorInterface;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

class ColumnRowDataProcessor implements DataProcessorInterface
{
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
        $processedData = ColumnRowUtility::getFrontendAttributesByPopulatedRow($processedData) + $processedData;
        $containerProcessor = GeneralUtility::makeInstance(ContainerProcessor::class);
        $processedData = $containerProcessor->process($cObj, $contentObjectConfiguration, $processorConfiguration, $processedData);
        $processedData = $this->mapContentInColumns($processedData);
        return $processedData;
    }

    protected function mapContentInColumns($processedData) {
        $columns = $processedData['columns'];
        foreach($processedData as $key => $value) {
            $split = explode('_', $key);
            if(str_contains($key, 'children_')) {
                foreach($columns as $colKey => $col) {
                    $strCount = strlen($col['uid']);
                    if(substr($key, -$strCount) == $col['uid']) {
                        $processedData['columns'][$colKey]['colPos'] = $split[1];
                    }
                }
            }
        }

        return $processedData;
    }
}
