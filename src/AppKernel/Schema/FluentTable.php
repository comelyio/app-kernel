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

namespace Comely\AppKernel\Schema;

use Comely\AppKernel;
use Comely\AppKernel\Exception\AppKernelException;
use Comely\Fluent\Database\Table;

/**
 * Class FluentTable
 * @package Comely\AppKernel\Schema
 */
abstract class FluentTable extends Table
{
    /** @var AppKernel */
    protected $app;
    /** @var AppKernel\Memory */
    protected $memory;

    /**
     * @throws AppKernelException
     */
    public function callback()
    {
        $this->app = AppKernel::getInstance();
        $this->memory = $this->app->memory();
    }
}