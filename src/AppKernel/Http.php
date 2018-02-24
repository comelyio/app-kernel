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

namespace Comely\AppKernel;

use Comely\AppKernel;
use Comely\AppKernel\Http\Client;
use Comely\AppKernel\Http\Security;

/**
 * Class Http
 * @package Comely\AppKernel
 */
class Http
{
    /** @var null|Client */
    private $client;
    /** @var null|Security */
    private $security;

    /**
     * @return Client
     */
    public function client(): Client
    {
        if (!$this->client) {
            $this->client = new Client();
        }

        return $this->client;
    }

    /**
     * @return Security
     * @throws Exception\AppKernelException
     * @throws Exception\BootstrapException
     * @throws \Comely\IO\Session\Exception\SessionException
     */
    public function security(): Security
    {
        if (!$this->security) {
            $this->security = new Security(AppKernel::getSession());
        }

        return $this->security;
    }
}