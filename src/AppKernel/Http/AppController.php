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

declare(strict_types=1);

namespace Comely\AppKernel\Http;

use Comely\AppKernel;
use Comely\IO\HttpRouter\Controller;

/**
 * Class Controller
 * @package Comely\AppKernel\Http
 */
abstract class AppController extends Controller
{
    /** @var AppKernel */
    protected $app;

    /**
     * @throws AppKernel\Exception\AppKernelException
     * @throws \Comely\IO\HttpRouter\Exception\ControllerResponseException
     */
    public function callback(): void
    {
        // Set AppKernel instance
        $this->app = AppKernel::getInstance();
        $this->app->http()->client()->headers($this->request()->headers());
    }

    /**
     * @return Client
     */
    public function client(): Client
    {
        return $this->app->http()->client();
    }
}