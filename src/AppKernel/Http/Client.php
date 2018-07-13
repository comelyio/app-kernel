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
     * Client constructor.
     */
    public function __construct()
    {
        $this->https = isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] ? true : false;
        $this->ipAddress = $_SERVER["HTTP_CF_CONNECTING_IP"] ?? $_SERVER["REMOTE_ADDR"] ?? null;
        $this->port = $_SERVER["SERVER_PORT"] ? intval($_SERVER["SERVER_PORT"]) : null;
    }

    /**
     * @param Headers $headers
     * @return Client
     */
    public function headers(Headers $headers): self
    {
        $this->origin = $headers->get("referer");
        $this->agent = $headers->get("user-agent");
        return $this;
    }
}