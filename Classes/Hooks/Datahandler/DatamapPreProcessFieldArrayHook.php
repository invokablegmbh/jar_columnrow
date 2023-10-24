<?php

declare(strict_types=1);
namespace Jar\Columnrow\Hooks\Datahandler;

/*
 * This file is part of TYPO3 CMS-based extension "jar_columnrow" by invokable.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

use B13\Container\Domain\Factory\ContainerFactory;
use B13\Container\Domain\Service\ContainerService;
use B13\Container\Domain\Factory\Exception;
use B13\Container\Hooks\Datahandler\Database;
use B13\Container\Tca\Registry;
use Jar\Columnrow\Hooks\Datahandler\Database as ColumnDatabase;
use Jar\Columnrow\Utilities\ColumnRowUtility;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Utility\MathUtility;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;

class DatamapPreProcessFieldArrayHook
{
    public function __construct(
        private readonly ContainerFactory $containerFactory,
        private readonly Database $database,
        private readonly ColumnDatabase $columnDatabase,
        private readonly Registry $tcaRegistry,
        private readonly ContainerService $containerService
    ) {
    }

    protected function copyToLanguageElementInContainer(array $incomingFieldArray): array
    {        
        if (!isset($incomingFieldArray['tx_container_parent']) || (int)$incomingFieldArray['tx_container_parent'] === 0) {
            return $incomingFieldArray;
        }
        if (!isset($incomingFieldArray['l10n_source']) || (int)$incomingFieldArray['l10n_source'] === 0) {
            return $incomingFieldArray;
        }
        if (!isset($incomingFieldArray['l18n_parent']) || (int)$incomingFieldArray['l18n_parent'] > 0) {
            return $incomingFieldArray;
        }
        if (!isset($incomingFieldArray['sys_language_uid']) || (int)$incomingFieldArray['sys_language_uid'] === 0) {
            return $incomingFieldArray;
        }        
        $translatedContainerRecord = $this->database->fetchOneRecord((int)$incomingFieldArray['tx_container_parent']);  
   
        //$incomingFieldArray['colPos'] = 773144;
       
        if ($translatedContainerRecord === null) {
            return $incomingFieldArray;
        }
        try {            
            $container = $this->containerFactory->buildContainer((int)$translatedContainerRecord['uid']);

            if (!$container->isConnectedMode()) {                
                $sourceColumnUid = ColumnRowUtility::encodeColPos((int)$incomingFieldArray['colPos']);
                if($sourceColumnUid !== (int)$incomingFieldArray['colPos']) {
                    
                }
                DebuggerUtility::var_dump($translatedContainerRecord);
                DebuggerUtility::var_dump($sourceColumnUid);
                die();
         
            }
        } catch (Exception $e) {
            // not a container
        }
        return $incomingFieldArray;
    }

    public function processDatamap_preProcessFieldArray(array &$incomingFieldArray, string $table, $id, DataHandler $dataHandler): void
    {        
        if ($table !== 'tt_content') {
            return;
        }
        if (MathUtility::canBeInterpretedAsInteger($id)) {
            return;
        }
        if (!isset($incomingFieldArray['pid']) || (int)$incomingFieldArray['pid'] >= 0) {
            return;
        }      
        $incomingFieldArray = $this->copyToLanguageElementInContainer($incomingFieldArray);
    }
}
