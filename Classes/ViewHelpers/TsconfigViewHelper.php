<?php

namespace Jar\Columnrow\ViewHelpers;

use Jar\Utilities\Utilities\BackendUtility;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;
use TYPO3Fluid\Fluid\Core\Parser\Source;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * 
 */
class TsconfigViewHelper extends AbstractViewHelper
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
     * @return void
     */
    
    public function initializeArguments()
    {
        $this->registerArgument(
            'key',
            'string',
            'Key from tsconfig'
        );
    }

    /**
     * @param array $arguments
     * @param \Closure $renderChildrenClosure
     * @param RenderingContextInterface $renderingContext
     * @return mixed|string
     */
    public static function renderStatic(
        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    ) {
        $options[] = ['LLL:EXT:jar_columnrow/Resources/Private/Language/locallang_be.xlf:transparent','default'];
        $pageTs = BackendUtility::getCurrentPageTS();
        if(isset($pageTs['jar']['bgcolors'])) {
            foreach($pageTs['jar']['bgcolors'] as $label => $value) {
                $options[] = [$label, $value];
            }
        }
        $options[] = ['LLL:EXT:jar_columnrow/Resources/Private/Language/locallang_be.xlf:user_customized','user'];
        
        return $options;
    }
}
