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
        $backgroundRowStyle = '';
        if($options['selectBackground'] == 1 && $options['rowBackground'] != ''){
            if($options['rowBackground'] != 'default' && $options['rowBackground'] != 'user') {
                $backgroundRowStyle = 'background-color:' . $options['rowBackground'];
            } else {
                $backgroundRowStyle = 'background-color:' . $options['rowUserBackground'];
            }
        } else if($options['selectBackground'] == 2 && $options['rowBackgroundImage'] == 1) {
            if($url = static::getImage($options['record']['uid'])) {
                $backgroundRowStyle = 'background-image: url(/'. $url .')';
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
                    if($column['column']['col-background-color'] != 'default' && $column['column']['col-background-color'] != 'user') {
                        $colBackground = 'background-color:' . $column['column']['col-background-color'];
                    } 
                    else if($column['column']['col-background-color'] == 'user') {
                        $colBackground = 'background-color:' . $column['column']['col-user-background-color'];
                    }
                } else {
                    $colClasses = static::getColClasses($column);

                    if($column['column']['col-background-choose'] == 1) {
                        if($column['column']['col-background-color'] != 'default' && $column['column']['col-background-color'] != 'user') {
                            $colBackground = 'background-color:' . $column['column']['col-background-color'];
                        } 
                        else if($column['column']['col-background-color'] == 'user') {
                            $colBackground = 'background-color:' . $column['column']['col-user-background-color'];
                        }
                    } else if($column['column']['col-background-choose'] == 2) {
                        $colBackground = 'background-image:url(/'. $column['column']['col-background-image'] .')';
                    }
                }

                $config['columns'][$key]['colClasses'] = $colClasses;
                $config['columns'][$key]['colBackground'] = $colBackground;
            }
        }

        $config['backgroundRowStyle'] = $backgroundRowStyle;

        #DebuggerUtility::var_dump(json_encode($config));die;
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
        if($column['column']['col-xs']){
            $colClasses = 'col-' . $column['column']['col-xs'];
        }
        if($column['column']['col-sm']){
            $colClasses .= ' col-sm-' . $column['column']['col-sm'];
        }
        if($column['column']['col-md']){
            $colClasses .= ' col-md-' . $column['column']['col-md'];
        }
        if($column['column']['col-lg']){
            $colClasses .= ' col-lg-' . $column['column']['col-lg'];
        }

        if($column['column']['order-xs']){
            $colClasses .= ' order-' . $column['column']['order-xs'];
        }
        if($column['column']['order-sm']){
            $colClasses .= ' order-sm-' . $column['column']['order-sm'];
        }
        if($column['column']['order-md']){
            $colClasses .= ' order-md-' . $column['column']['order-md'];
        }
        if($column['column']['order-lg']){
            $colClasses .= ' order-lg-' . $column['column']['order-lg'];
        }

        if($column['column']['offset-xs']){
            $colClasses .= ' offset-' . $column['column']['offset-xs'];
        }
        if($column['column']['offset-sm']){
            $colClasses .= ' offset-sm-' . $column['column']['offset-sm'];
        }
        if($column['column']['offset-md']){
            $colClasses .= ' offset-md-' . $column['column']['offset-md'];
        }
        if($column['column']['offset-lg']){
            $colClasses .= ' offset-lg-' . $column['column']['offset-lg'];
        }
        
        return $colClasses;
    }
}
