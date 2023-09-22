<?php

declare(strict_types=1);

namespace Jar\Columnrow\Xclasses\Tca;

/*
 * This file is part of TYPO3 CMS-based extension "jar_columnrow" by invokable.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */


use TYPO3\CMS\Core\SingletonInterface;
use B13\Container\Tca\ContainerConfiguration;
use Jar\Columnrow\Services\GateService;
use Jar\Columnrow\Utilities\ColumnRowUtility;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;

class Registry extends \B13\Container\Tca\Registry implements SingletonInterface
{
    

    /**
     * @param ContainerConfiguration $containerConfiguration
     */
    public function configureContainer(ContainerConfiguration $containerConfiguration): void
    {
        parent::configureContainer($containerConfiguration);
    }

    public function getAllAvailableColumnsColPos(string $cType): array
    {
        return parent::getAllAvailableColumnsColPos($cType);
    }    

    public function isContainerElement(string $cType): bool
    {        
        if(ColumnRowUtility::isOurContainerCType($cType)) {
            return true;
        }        
        return parent::isContainerElement($cType);
    }

    public function getRegisteredCTypes(): array
    {
        $result = parent::getRegisteredCTypes();
        return $result;
    }

    public function getGrid(string $cType): array
    {        
        if(ColumnRowUtility::isOurContainerCType($cType)) {
            return GeneralUtility::makeInstance(GateService::class)->getContainerGridFromLastUsedRow();
        }
        return parent::getGrid($cType);
    }

    public function getGridTemplate(string $cType): ?string
    {
        if(ColumnRowUtility::isOurContainerCType($cType)) {
            return 'EXT:container/Resources/Private/Templates/Grid.html';
        }
        return parent::getGridTemplate($cType);
    }

    public function getGridPartialPaths(string $cType): array
    {
        // thus we skip the container bootloading of containers via tca (and the corsponding creation of new content elements)
        // we have to add the partials manualy
        if (ColumnRowUtility::isOurContainerCType($cType)) {
            return [
                'EXT:backend/Resources/Private/Partials/',
                ((GeneralUtility::makeInstance(Typo3Version::class))->getMajorVersion() === 12) ? 'EXT:container/Resources/Private/Partials12/' : 'EXT:container/Resources/Private/Partials/',
                'EXT:jar_columnrow/Resources/Private/Partials/'
            ];
        }
        return parent::getGridPartialPaths($cType);
    }

    public function getGridLayoutPaths(string $cType): array
    {
        return parent::getGridLayoutPaths($cType);
    }

    public function getColPosName(string $cType, int $colPos): ?string
    {
        return parent::getColPosName($cType, $colPos);
    }

    public function getAvailableColumns(string $cType): array
    {
        return parent::getAvailableColumns($cType);
    }

    public function getAllAvailableColumns(): array
    {
        return parent::getAllAvailableColumns();
    }

    public function getPageTsString(): string
    {
        return parent::getPageTsString();
    }
}
