<?php

namespace Jar\Columnrow\ViewHelpers;

use Jar\Columnrow\Services\GateService;
use Jar\Utilities\Services\ReflectionService;
use TYPO3\CMS\Backend\View\BackendLayout\Grid\Grid;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Exception;

/**
 * 
 */
class GridSettingsViewHelper extends AbstractViewHelper
{
    /**
     * @var boolean
     */
    protected $escapeChildren = false;

    /**
     * @var boolean
     */
    protected $escapeOutput = false;

    /**
     * @param ReflectionService $reflectionService 
     * @return void 
     */
    public function __construct(
        private readonly ReflectionService $reflectionService
    ) {
        $reflectionService
            ->setTableColumnWhitelist([
                'tt_content' => ['columnrow_*']
            ])
            ->setTableColumnRemoveablePrefixes([
                'tt_content' => ['columnrow_']
            ]);
    }  
    
    /**
     * @return void 
     * @throws Exception 
     */
    public function initializeArguments()
    {
        $this->registerArgument(
            'grid',
            Grid::class,
            'Grid object'
        );
    }

    /**
     * @param array $arguments
     * @param \Closure $renderChildrenClosure
     * @param RenderingContextInterface $renderingContext
     * @return array
     */
    public static function renderStatic(
        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    ): ?array {
        $grid = $arguments['grid'];
        $rows = $grid->getRows();

        $result = [];

        // skip first hidden adjustment row
        if(is_array($rows) && count($rows) > 1) {
            $firstUseableColumn = reset($rows[1]->getColumns());
            $containerRecord = $firstUseableColumn->getContainer()->getContainerRecord();
            $columnRowSettings = GeneralUtility::makeInstance(GateService::class)->getReflectedRow($containerRecord);
            $result = $columnRowSettings;
        }

        
        
        return $result;
    }
}
