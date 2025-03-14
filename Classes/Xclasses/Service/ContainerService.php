<?php

declare(strict_types=1);

namespace Jar\Columnrow\Xclasses\Service;

/*
 * This file is part of TYPO3 CMS-based extension "jar_columnrow" by invokable.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

use B13\Container\Domain\Model\Container;
use B13\Container\Domain\Service\ContainerService as ServiceContainerService;
use Jar\Columnrow\Utilities\ColumnRowUtility;

class ContainerService extends ServiceContainerService
{
    public function getAfterContainerRecord(Container $container): array {
        $containerRecord = $container->getContainerRecord();
        if(isset($containerRecord['CType']) && ColumnRowUtility::isOurContainerCType($containerRecord['CType'])) {
            return $containerRecord;
        }
        return parent::getAfterContainerRecord($container);
    }
}
