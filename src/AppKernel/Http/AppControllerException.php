<?php
/**
 * This file is part of Comely App Kernel package.
 * https://github.com/comelyio/app-kernel
 *
 * Copyright (c) 2018 Furqan A. Siddiqui <hello@furqansiddiqui.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code or visit following link:
 * https://github.com/comelyio/app-kernel/blob/master/LICENSE
 */

declare(strict_types=1);

namespace Comely\AppKernel\Http;

use Comely\AppKernel\Exception\AppKernelException;

/**
 * Class AppControllerException
 * @package Comely\AppKernel\Http
 */
class AppControllerException extends AppKernelException
{
    /** @var null|string */
    protected $param;

    /**
     * @param string $param
     * @return AppControllerException
     */
    public function setParam(string $param): self
    {
        $this->param = $param;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getParam(): ?string
    {
        return $this->param;
    }
}

