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

namespace Comely\AppKernel\CLI;

use Comely\AppKernel;

/**
 * Class AbstractJob
 * @package Comely\AppKernel\CLI
 */
abstract class AbstractJob
{
    /** @var AppKernel */
    protected $app;
    /** @var AppKernel\CLI */
    protected $cli;

    /**
     * AbstractJob constructor.
     * @param AppKernel $app
     * @param AppKernel\CLI $cli
     */
    public function __construct(AppKernel $app, AppKernel\CLI $cli)
    {
        $this->app = $app;
        $this->cli = $cli;
    }

    abstract public function run(): void;
}