<?php
namespace Jar\Columnrow\DataProcessing;

use B13\Container\DataProcessing\ContainerProcessor;
use Jar\Columnrow\Utilities\ColumnRowUtility;
use TYPO3\CMS\Frontend\ContentObject\DataProcessorInterface;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use B13\Container\Domain\Factory\ContainerFactory;
use Jar\Columnrow\Services\GateService;
use Jar\Utilities\Utilities\IteratorUtility;

class ColumnRowDataProcessor implements DataProcessorInterface
{
    public function __construct(
        private readonly ContainerFactory $containerFactory,
        private readonly GateService $gateService,
        private readonly ContainerProcessor $containerProcessor
    ) {}

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

        if(isset($row['_LOCALIZED_UID'])) {
            $row['uid'] = $row['_LOCALIZED_UID'];
        }

        $container = $this->containerFactory->buildContainer($row['uid']);

        $toReflectedRow = $isTranslatedElementInConnectedMode ? $container->getContainerRecord() : $row;
        $reflectedRow = $this->gateService->getReflectedRow($toReflectedRow);
                
        // add individual labels per language
        if($isTranslatedElementInConnectedMode) {
            $reflectedRow = ColumnRowUtility::addIndividualFieldsPerLanguage(
                $reflectedRow,
                $this->gateService->getReflectedRow($row)
            );
        }

        // render column content via container processor        
        $containerProcessorResult = $this->containerProcessor->process($cObj, $contentObjectConfiguration, $processorConfiguration, $processedData);
        
        $colsWithChildren = [];
        foreach (array_keys($containerProcessorResult) as $key) {
            if (str_starts_with((string) $key, 'children_')) {
                $colPos = explode('_', (string) $key, 2)[1];
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
            
            if($column['col_xs'] != '12' && $column['col_xs'] != '0') {
                $finalColumnClass .= ' col-' . $column['col_xs'];
            }
            if($column['col_sm'] != '12' && $column['col_sm'] != '0') {
                $finalColumnClass .= ' col-sm-' . $column['col_sm'];
            }
            if($column['col_md'] != '12' && $column['col_md'] != '0') {
                $finalColumnClass .= ' col-md-' . $column['col_md'];
            }

            if($column['order_xs'] != '12' && $column['order_xs'] != '0') {
                $finalColumnClass .= ' order-' . $column['order_xs'];
            }
            if($column['order_sm'] != $column['order_md']) {
                $finalOrderClass .= ' order-sm-' . $column['order_sm'];
            }
            if($column['order_md'] != $column['order_lg']) {
                $finalOrderClass .= ' order-md-' . $column['order_md'];
            }
            if($column['order_lg'] != $column['order_lg']) {
                $finalOrderClass .= ' order-lg-' . $column['order_lg'];
            }

            if($column['offset_xs'] != '12' && $column['offset_xs'] != '0') {
                $finalColumnClass .= ' offset-' . $column['offset_xs'];
            }
            if($column['offset_sm'] != '12' && $column['offset_sm'] != '0') {
                $finalOffsetClass .= ' offset-sm-' . $column['offset_sm'];
            }
            if($column['offset_md'] != '12' && $column['offset_md'] != '0') {
                $finalOffsetClass .= ' offset-md-' . $column['offset_md'];
            }
            if($column['offset_lg'] != '12' && $column['offset_lg'] != '0') {
                $finalOffsetClass .= ' offset-lg-' . $column['offset_lg'];
            }

            if(!empty($column['col_lg'])) {
                $finalColumnClass .= ' col-lg-' . $column['col_lg'];
            }

            if(!empty($column['padding_class'])) {
                $paddingClass = 'cp-none';
                if($column['padding_class'] === '1') { $paddingClass = 'cp-small'; }
                if($column['padding_class'] === '2') { $paddingClass = 'cp-normal'; }
                if($column['padding_class'] === '3') { $paddingClass = 'cp-double'; }
                if($column['padding_class'] === '4') { $paddingClass = 'cp-x-double'; }
                $finalColumnClass .= ' ' . $paddingClass;
            }

            if(!empty($column['alignment_class'])) {
                $alignClass = 'align-self-top';
                if($column['alignment_class'] === '1') { $alignClass = 'align-self-middle'; }
                if($column['alignment_class'] === '2') { $alignClass = 'align-self-bottom'; }
                if($column['alignment_class'] === '3') { $alignClass = 'align-self-stretch'; }
                $finalColumnClass .= ' ' . $alignClass;
            }

            if ($column['bg_color_class'] == "transpa") {
                $column['bg_color_class'] = 'transparent';
            }
            
            $finalBackgroundColor = '';
            if(!empty($column['bg_color_class']) && $column['bg_color_class'] != 'transparent') {
                $finalBackgroundColor = 'background-color:' . $column['bg_color_class'] .';';
            }


            $processedData['columns'][$k]['cssClass'] = $finalColumnClass;
            $processedData['columns'][$k]['orderClass'] = $finalOrderClass;
            $processedData['columns'][$k]['offsetClass'] = $finalOffsetClass;
            $processedData['columns'][$k]['backgroundColor'] = $finalBackgroundColor;
        }

        if (!empty($row['space_before_class'])) {
            $processedData['space_before_class'] = 'space-before-' . $row['space_before_class'];
        } else {
            $processedData['space_before_class'] = 'pt-none';
        }

        if (!empty($row['space_after_class'])) {
            $processedData['space_after_class'] = 'space-after-' . $row['space_after_class'];
        } else {
            $processedData['space_after_class'] = 'pb-normal';
        }

        $convertCroppingAreaToCssVars = function ($croppingArea) {
            if (empty($croppingArea['x']) || empty($croppingArea['y'])) {
                return '';
            }
            $fpx = round($croppingArea['x'] + ($croppingArea['width'] / 2), 4) * 100;
            $fpy = round($croppingArea['y'] + ($croppingArea['height'] / 2), 4) * 100;
            return '--fpx:' . $fpx . '%;--fpy:' . $fpy . '%;';
        };

        $focusAreaCss = [];
        if (!empty($processedData['row_background_image'][0]['focusArea'])) {
            foreach ($processedData['row_background_image'][0]['focusArea'] as $variant => $area) {
                $focusAreaCss[$variant] = $convertCroppingAreaToCssVars($area);
            }
        }
        
        $processedData['focusAreaCss'] = $focusAreaCss;
        $processedData['focusAreaCssStyling'] = $this->buildFocusAreaCssStyling($focusAreaCss, $row['uid']);
        
        // background options
        $processedData['bg'] = '';
        if ($row['columnrow_select_background'] == '1') {
            $processedData['bg'] = 'background-color: ' . $row['columnrow_row_background'];
        }
        if ($row['columnrow_select_background'] == '2') {
            $processedData['bg'] = 'background-image: url(' .  $processedData['row_background_image'][0]['url'] . ');background-size:cover;';
        }
        
        return $processedData;
    }

    private function buildFocusAreaCssStyling(array $focusAreaCss, string $uid): string
    {
        $css = '';
        if (!empty($focusAreaCss['desktop'])) {
            $css .= "@media (min-width:1200px){#c{$uid}{{$focusAreaCss['desktop']}background-position:var(--fpx) var(--fpy);}}";
        }
        if (!empty($focusAreaCss['medium'])) {
            $css .= "@media (max-width:990px){#c{$uid}{{$focusAreaCss['medium']}background-position:var(--fpx) var(--fpy);}}";
        }
        if (!empty($focusAreaCss['tablet'])) {
            $css .= "@media (max-width:768px){#c{$uid}{{$focusAreaCss['tablet']}background-position:var(--fpx) var(--fpy);}}";
        }
        if (!empty($focusAreaCss['mobile'])) {
            $css .= "@media (max-width:450px){#c{$uid}{{$focusAreaCss['mobile']}background-position:var(--fpx) var(--fpy);}}";
        }
        return $css !== '' && $css !== '0' ? "<style>{$css}</style>" : '';
    }
}
