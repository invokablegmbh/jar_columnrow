<?php

namespace Jar\Columnrow\ViewHelpers;

use Jar\Utilities\Utilities\BackendUtility;
use Jar\Utilities\Utilities\FileUtility;
use TYPO3\CMS\Backend\Utility\BackendUtility as UtilityBackendUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
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
        $this->registerArgument(
            'preview',
            'int',
            '',
            false,
            0
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
        $config = [];
        
        $backgroundRowStyle = $backgroundColor = $backgroundColorLabel = '';
        
        if(isset($options['selectBackground'])) {
            if($options['selectBackground'] == 1 && isset($options['rowBackground']) && $options['rowBackground'] != ''){
                if($options['rowBackground'] != 'default' && $options['rowBackground'] != 'user') {
                    $backgroundRowStyle = 'background:' . $options['rowBackground'];
                    $backgroundColor = $options['rowBackground'];
                } else if(isset($options['rowUserBackground'])) {
                    $backgroundRowStyle = 'background:' . $options['rowUserBackground'];
                    $backgroundColor = $options['rowUserBackground'];
                }
                if($arguments['preview'] == 1) {
                    $pageTs = BackendUtility::getCurrentPageTS();
                    if(isset($pageTs['jar']['bgcolors'])) {
                        foreach($pageTs['jar']['bgcolors'] as $label => $value) {
                            if($backgroundColor == $value) {
                                $backgroundColorLabel = $label;
                                break;
                            }
                        }
                    }
                }
            } else if($options['selectBackground'] == 2 && $options['rowBackgroundImage'] == 1) {
                if($url = static::getImage($options['record']['uid'])) {
                    $backgroundRowStyle = 'background-image: url('. $url .')';
                }
            }
        }
        if(!empty($options['columns'])) {
            foreach($options['columns'] as $key => $column) {
                $colClasses = $colBackground = '';
                if($options['extended'] == 0) {
                    if(isset($column['column']['col']) && $column['column']['col'] != 12){
                        $colClasses = 'col-12 col-md-' . $column['column']['col'];
                    } else {
                        $colClasses = 'col-' . $column['column']['col'];
                    }
                    if(array_key_exists('col-background-color', $column['column'])) {
                        if($column['column']['col-background-color'] != 'default' && $column['column']['col-background-color'] != 'user') {
                            $colBackground = 'background-color:' . $column['column']['col-background-color'];
                        } 
                        else if($column['column']['col-background-color'] == 'user') {
                            if($column['column']['col-user-background-color']) {
                                $colBackground = 'background-color:' . $column['column']['col-user-background-color'];
                            }
                        }
                    }
                } else {
                    $colClasses = static::getColClasses($column);

                    if(array_key_exists('col-background-choose', $column['column']) && $column['column']['col-background-choose'] == 1) {
                        if($column['column']['col-background-color'] != 'default' && $column['column']['col-background-color'] != 'user') {
                            $colBackground = 'background-color:' . $column['column']['col-background-color'];
                        } 
                        else if(array_key_exists('col-background-color', $column['column']) && $column['column']['col-background-color'] == 'user') {
                            if($column['column']['col-user-background-color']) {
                                $colBackground = 'background-color:' . $column['column']['col-user-background-color'];
                            }
                        }
                    } else if(array_key_exists('col-background-choose', $column['column']) && $column['column']['col-background-choose'] == 2) {
                        $colBackground = 'background-image:url(/'. $column['column']['col-background-image'] .')';
                    }
                }

                $config['columns'][$key]['colClasses'] = $colClasses;
                $config['columns'][$key]['colBackground'] = $colBackground;
            }
        }

        $config['backgroundRowStyle'] = $backgroundRowStyle;
        $config['backgroundColorLabel'] = $backgroundColorLabel;

        return $config;
    }

    protected static function getImage($ceUid) {
        $url = false;
        
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('sys_file_reference')->createQueryBuilder();
        $res = $queryBuilder
        ->select('uid')
        ->from('sys_file_reference')
        ->where($queryBuilder->expr()->eq('tablenames', $queryBuilder->createNamedParameter('tt_content')))
        ->andWhere($queryBuilder->expr()->eq('uid_foreign', $queryBuilder->createNamedParameter($ceUid, \PDO::PARAM_INT)))
        ->execute();
        if($fileRefUid = $res->fetchColumn()) {
            $fileRef = FileUtility::getFileReferenceByUid($fileRefUid);
            $file = FileUtility::buildFileArrayBySysFileReference($fileRef);
            $url = $file['url'];
        }
        
        return $url;
    }
    
    protected static function getColClasses($column) {
        $colClasses = '';
        
        if(array_key_exists('col-xs', $column['column'])){
            $colClasses = 'col-' . $column['column']['col-xs'];
        }
        if(array_key_exists('col-sm', $column['column'])){
            $colClasses .= ' col-sm-' . $column['column']['col-sm'];
        }
        if(array_key_exists('col-md', $column['column'])){
            $colClasses .= ' col-md-' . $column['column']['col-md'];
        }
        if(array_key_exists('col-lg', $column['column'])){
            $colClasses .= ' col-lg-' . $column['column']['col-lg'];
        }

        if(array_key_exists('order-xs', $column['column'])){
            $colClasses .= ' order-' . $column['column']['order-xs'];
        }
        if(array_key_exists('order-sm', $column['column'])){
            $colClasses .= ' order-sm-' . $column['column']['order-sm'];
        }
        if(array_key_exists('order-md', $column['column'])){
            $colClasses .= ' order-md-' . $column['column']['order-md'];
        }
        if(array_key_exists('order-lg', $column['column'])){
            $colClasses .= ' order-lg-' . $column['column']['order-lg'];
        }

        if(array_key_exists('offset-xs', $column['column'])){
            $colClasses .= ' offset-' . $column['column']['offset-xs'];
        }
        if(array_key_exists('offset-sm', $column['column'])){
            $colClasses .= ' offset-sm-' . $column['column']['offset-sm'];
        }
        if(array_key_exists('offset-md', $column['column'])){
            $colClasses .= ' offset-md-' . $column['column']['offset-md'];
        }
        if(array_key_exists('offset-lg', $column['column'])){
            $colClasses .= ' offset-lg-' . $column['column']['offset-lg'];
        }
        
        return $colClasses;
    }
}
