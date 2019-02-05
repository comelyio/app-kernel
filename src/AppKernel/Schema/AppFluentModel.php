<?php
/**
 * This file is part of Comely App Kernel package.
 * https://github.com/comelyio/app-kernel
 *
 * Copyright (c) 2019 Furqan A. Siddiqui <hello@furqansiddiqui.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code or visit following link:
 * https://github.com/comelyio/app-kernel/blob/master/LICENSE
 */

namespace Comely\AppKernel\Schema;

use Comely\AppKernel;
use Comely\AppKernel\Exception\AppKernelException;
use Comely\Fluent\ORM\Model;

/**
 * Class AppFluentModel
 * @package Comely\AppKernel\Schema
 */
abstract class AppFluentModel extends Model
{
    /** @var null|AppKernel */
    protected $app;

    /**
     * Set $app prop with AppKernel instance
     * @throws AppKernelException
     */
    public function onLoad()
    {
        $this->app = AppKernel::getInstance();
    }

    /**
     * Clear $app property
     */
    public function onSleep()
    {
        $this->app = null;
    }

    /**
     * Set $app prop with AppKernel instance
     * @throws AppKernelException
     */
    public function onWakeup()
    {
        $this->app = AppKernel::getInstance();
    }
}