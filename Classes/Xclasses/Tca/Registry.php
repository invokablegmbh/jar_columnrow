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
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;

class Registry extends \B13\Container\Tca\Registry implements SingletonInterface
{
    private string $containerCTypePrefix = 'jarcolumnrow_';

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
        // check the beginning of ctype for "jarcolumnrow_"
        if(substr($cType, 0, strlen($this->containerCTypePrefix)) === $this->containerCTypePrefix) {
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
        return parent::getGrid($cType);
    }

    public function getGridTemplate(string $cType): ?string
    {
        return parent::getGridTemplate($cType);
    }

    public function getGridPartialPaths(string $cType): array
    {
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
