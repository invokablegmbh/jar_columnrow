<?php

declare(strict_types=1);

namespace Jar\Columnrow\Xclasses\Backend;

use B13\Container\Backend\Grid\ContainerGridColumn as OriginalContainerGridColumn;
use B13\Container\Domain\Model\Container;

/*
 * This file is part of TYPO3 CMS-based extension "jar_columnrow" by invokable.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */


class ContainerGridColumn extends OriginalContainerGridColumn
{
    public function getContainer(): Container
    {
        return $this->container;
    }
}
