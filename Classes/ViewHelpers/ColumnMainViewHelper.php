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
class ColumnMainViewHelper extends AbstractViewHelper
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
            'options',
            'array',
            ''
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
        $options = $arguments['options'];
        DebuggerUtility::var_dump($arguments);

        $config = [];
        $backgroundRowStyle = '';
        if($options['selectBackground'] == 1 && $options['rowBackground'] != ''){
            if($rowBackground != 'default' && $rowBackground != 'user') {
                $backgroundRowStyle = 'background-color:' . $options['rowBackground'];
            } else {
                $backgroundRowStyle = 'background-color:' . $options['rowUserBackground'];
            }
        } else if($options['selectBackground'] == 2 && $options['rowBackgroundImage'] == 1) {
            #DebuggerUtility::var_dump($rowBackgroundImage);die;
            //{v:content.resources.fal(field: 'rowBackgroundImage') -> v:iterator.first()}
            $image['url'] = '';
            $backgroundRowStyle = 'background-image: url(/'. $image['url'] .')';
        }

        $config['backgroundRowStyle'] = $backgroundRowStyle;
        DebuggerUtility::var_dump($config);die;
        return $config;
    }
}
