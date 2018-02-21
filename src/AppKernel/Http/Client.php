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

use Comely\IO\HttpRouter\Controller\Data\Headers;

/**
 * Class Client
 * @package Comely\AppKernel\Http
 */
class Client
{
    /** @var bool */
    public $https;
    /** @var null|string */
    public $ipAddress;
    /** @var null|string */
    public $origin;
    /** @var null|string */
    public $agent;
    /** @var null|int */
    public $port;

    /**
     * User constructor.
     * @param Headers $headers
     */
    public function __construct(Headers $headers)
    {
        $this->https = $headers->get("HTTPS") ? true : false;
        $this->ipAddress = $headers->get("HTTP_CF_CONNECTING_IP") ?? $headers->get("REMOTE_ADDR");
        $this->origin = $headers->get("HTTP_REFERER");
        $this->agent = $headers->get("HTTP_USER_AGENT");
        $this->port = $headers->get("SERVER_PORT");
        if ($this->port) {
            $this->port = intval($this->port);
        }
    }
}